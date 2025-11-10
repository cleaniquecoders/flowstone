# E-commerce Order Processing Workflow

A comprehensive order processing workflow for e-commerce applications demonstrating order lifecycle management from cart to delivery.

> **💡 Note**: This example uses Flowstone's trait-based approach (`InteractsWithWorkflow`) for better IDE support and type safety. See [Configuration Guide](../../docs/02-configuration/01-configuration.md#model-integration-trait-vs-supports-configuration) for why this is better than Symfony's `supports` config.

## Workflow Overview

**Cart → Pending → Confirmed → Processing → Shipped → Delivered**

With branches to **Cancelled** and **Refunded** states.

## Order States

- **Cart**: Items in shopping cart (not yet ordered)
- **Pending**: Order placed, awaiting payment confirmation
- **Confirmed**: Payment confirmed, order accepted
- **Processing**: Items being prepared/picked
- **Shipped**: Order shipped to customer
- **Delivered**: Order successfully delivered
- **Cancelled**: Order cancelled (before shipping)
- **Refunded**: Order refunded (after payment)

## Implementation

### Order Model

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'total_amount',
        'payment_method',
        'shipping_address',
        'tracking_number',
        'notes',
        'processed_by',
        'shipped_at',
        'delivered_at',
        'cancelled_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'order-processing');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    public function getMarking(): string
    {
        return $this->status ?? 'cart';
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;

        // Auto-update timestamps
        match ($marking) {
            'shipped' => $this->shipped_at = now(),
            'delivered' => $this->delivered_at = now(),
            default => null,
        };
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Helper methods
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['delivered', 'shipped']);
    }

    public function isComplete(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled', 'refunded']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled', 'refunded']);
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
```

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->string('status')->default('cart');
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->json('shipping_address')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
```

### Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product']);

        if (auth()->user()->hasRole('customer')) {
            $query->forCustomer(auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $availableTransitions = $this->getAvailableTransitions($order);

        return view('orders.show', compact('order', 'availableTransitions'));
    }

    public function transition(Request $request, Order $order)
    {
        $request->validate([
            'transition' => 'required|string',
            'tracking_number' => 'nullable|string|required_if:transition,ship',
            'cancelled_reason' => 'nullable|string|required_if:transition,cancel',
        ]);

        $transition = $request->transition;
        $workflow = $order->getWorkflow();

        if (!$workflow->can($order, $transition)) {
            return back()->withErrors(['transition' => 'Invalid transition']);
        }

        // Handle transition-specific logic
        $this->handleTransitionLogic($order, $transition, $request);

        // Apply workflow transition
        $workflow->apply($order, $transition);
        $order->save();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order status updated successfully');
    }

    private function getAvailableTransitions(Order $order): array
    {
        $user = auth()->user();
        $allTransitions = $order->getEnabledToTransitions();
        $availableTransitions = [];

        foreach ($allTransitions as $transition => $label) {
            if ($this->canUserPerformTransition($user, $order, $transition)) {
                $availableTransitions[$transition] = $label;
            }
        }

        return $availableTransitions;
    }

    private function canUserPerformTransition($user, Order $order, string $transition): bool
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        return match ($transition) {
            'place_order' => $order->customer_id === $user->id,
            'confirm_payment' => $user->hasRole('payment_processor'),
            'start_processing' => $user->hasRole(['warehouse_staff', 'processor']),
            'ship' => $user->hasRole(['shipping_staff', 'processor']),
            'confirm_delivery' => $user->hasRole(['delivery_staff', 'processor']),
            'cancel' => $order->customer_id === $user->id || $user->hasRole('processor'),
            'request_refund' => $order->customer_id === $user->id,
            'approve_refund' => $user->hasRole(['refund_processor', 'manager']),
            default => false,
        };
    }

    private function handleTransitionLogic(Order $order, string $transition, Request $request): void
    {
        switch ($transition) {
            case 'place_order':
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
                break;

            case 'start_processing':
                $order->processed_by = auth()->id();
                break;

            case 'ship':
                $order->tracking_number = $request->tracking_number;
                break;

            case 'cancel':
                $order->cancelled_reason = $request->cancelled_reason;
                break;
        }
    }
}
```

### Workflow Configuration Seeder

```php
<?php

namespace Database\Seeders;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;
use Illuminate\Database\Seeder;

class OrderWorkflowSeeder extends Seeder
{
    public function run()
    {
        $workflow = Workflow::create([
            'name' => 'order-processing',
            'description' => 'E-commerce order processing workflow',
            'type' => 'state_machine',
            'initial_marking' => 'cart',
            'is_enabled' => true,
        ]);

        $places = [
            'cart', 'pending', 'confirmed', 'processing',
            'shipped', 'delivered', 'cancelled', 'refunded'
        ];

        foreach ($places as $index => $place) {
            WorkflowPlace::create([
                'workflow_id' => $workflow->id,
                'name' => $place,
                'sort_order' => $index + 1,
            ]);
        }

        $transitions = [
            [
                'name' => 'place_order',
                'from_place' => 'cart',
                'to_place' => 'pending',
                'meta' => ['roles' => ['customer']],
            ],
            [
                'name' => 'confirm_payment',
                'from_place' => 'pending',
                'to_place' => 'confirmed',
                'meta' => ['roles' => ['payment_processor', 'admin']],
            ],
            [
                'name' => 'start_processing',
                'from_place' => 'confirmed',
                'to_place' => 'processing',
                'meta' => ['roles' => ['warehouse_staff', 'processor', 'admin']],
            ],
            [
                'name' => 'ship',
                'from_place' => 'processing',
                'to_place' => 'shipped',
                'meta' => ['roles' => ['shipping_staff', 'processor', 'admin']],
            ],
            [
                'name' => 'confirm_delivery',
                'from_place' => 'shipped',
                'to_place' => 'delivered',
                'meta' => ['roles' => ['delivery_staff', 'processor', 'admin']],
            ],
            [
                'name' => 'cancel',
                'from_place' => 'pending',
                'to_place' => 'cancelled',
                'meta' => ['roles' => ['customer', 'processor', 'admin']],
            ],
            [
                'name' => 'cancel',
                'from_place' => 'confirmed',
                'to_place' => 'cancelled',
                'meta' => ['roles' => ['processor', 'admin']],
            ],
            [
                'name' => 'cancel',
                'from_place' => 'processing',
                'to_place' => 'cancelled',
                'meta' => ['roles' => ['processor', 'admin']],
            ],
            [
                'name' => 'request_refund',
                'from_place' => 'delivered',
                'to_place' => 'refunded',
                'meta' => ['roles' => ['customer']],
            ],
        ];

        foreach ($transitions as $index => $transition) {
            WorkflowTransition::create([
                'workflow_id' => $workflow->id,
                'name' => $transition['name'],
                'from_place' => $transition['from_place'],
                'to_place' => $transition['to_place'],
                'sort_order' => $index + 1,
                'meta' => $transition['meta'],
            ]);
        }
    }
}
```

## Key Features

- **Order lifecycle management** from cart to delivery
- **Role-based permissions** for different staff types
- **Automatic timestamping** for key workflow events
- **Tracking number** integration for shipped orders
- **Cancellation and refund** handling
- **Customer self-service** for order placement and tracking

## Usage Examples

### Place an Order

```php
$order = Order::create([
    'customer_id' => auth()->id(),
    'total_amount' => 99.99,
    'status' => 'cart',
]);

// Add items to order
$order->items()->create([
    'product_id' => 1,
    'quantity' => 2,
    'price' => 49.99,
    'total' => 99.98,
]);

// Place the order
$workflow = $order->getWorkflow();
$workflow->apply($order, 'place_order');
$order->save();
```

### Process Order (Staff)

```php
// Confirm payment
$workflow->apply($order, 'confirm_payment');

// Start processing
$workflow->apply($order, 'start_processing');

// Ship order
$order->tracking_number = 'TRACK123456';
$workflow->apply($order, 'ship');
$order->save();
```

### Customer Actions

```php
// Check available actions for customer
$transitions = $order->getEnabledToTransitions();

// Cancel order (if allowed)
if ($order->canBeCancelled()) {
    $workflow->apply($order, 'cancel');
}

// Request refund (if delivered)
if ($order->canBeRefunded()) {
    $workflow->apply($order, 'request_refund');
}
```

This example demonstrates a complete e-commerce order workflow with proper role separation and business logic integration.
