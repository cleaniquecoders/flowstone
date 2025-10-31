# Quick Start

Get up and running with Flowstone in minutes! This guide shows you how to implement a basic workflow in your Laravel application.

## Step 1: Create a Model with Workflow

Let's create a document approval workflow as an example.

### Generate the Model

```bash
php artisan make:model Document -m
```

### Implement the Workflow Contract

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'title',
        'content',
        'status',
        'workflow_type',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Define which workflow configuration to use
    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->workflow_type ?? 'document-approval'
        );
    }

    // Define the field that stores workflow type
    public function workflowTypeField(): Attribute
    {
        return Attribute::make(
            get: fn () => 'workflow_type'
        );
    }

    // Get the current workflow state
    public function getMarking(): string
    {
        return $this->status ?? Status::DRAFT->value;
    }

    // Set the workflow state
    public function setMarking(string $marking): void
    {
        $this->status = $marking;
    }
}
```

### Update the Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('status')->default('draft');
            $table->string('workflow_type')->default('document-approval');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

## Step 2: Create a Workflow Configuration

### Option A: Database Configuration (Recommended)

Create a workflow using the database:

```php
// In tinker or a seeder
use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

// Create the main workflow
$workflow = Workflow::create([
    'name' => 'document-approval',
    'description' => 'Document approval workflow',
    'type' => 'state_machine',
    'initial_marking' => 'draft',
    'is_enabled' => true,
]);

// Create places (states)
$places = [
    ['name' => 'draft', 'sort_order' => 1],
    ['name' => 'submitted', 'sort_order' => 2],
    ['name' => 'under_review', 'sort_order' => 3],
    ['name' => 'approved', 'sort_order' => 4],
    ['name' => 'rejected', 'sort_order' => 5],
];

foreach ($places as $place) {
    WorkflowPlace::create([
        'workflow_id' => $workflow->id,
        'name' => $place['name'],
        'sort_order' => $place['sort_order'],
    ]);
}

// Create transitions
$transitions = [
    [
        'name' => 'submit',
        'from_place' => 'draft',
        'to_place' => 'submitted',
        'sort_order' => 1,
        'meta' => ['roles' => ['author', 'editor']],
    ],
    [
        'name' => 'start_review',
        'from_place' => 'submitted',
        'to_place' => 'under_review',
        'sort_order' => 2,
        'meta' => ['roles' => ['reviewer']],
    ],
    [
        'name' => 'approve',
        'from_place' => 'under_review',
        'to_place' => 'approved',
        'sort_order' => 3,
        'meta' => ['roles' => ['manager', 'admin']],
    ],
    [
        'name' => 'reject',
        'from_place' => 'under_review',
        'to_place' => 'rejected',
        'sort_order' => 4,
        'meta' => ['roles' => ['manager', 'admin']],
    ],
    [
        'name' => 'revise',
        'from_place' => 'rejected',
        'to_place' => 'draft',
        'sort_order' => 5,
        'meta' => ['roles' => ['author']],
    ],
];

foreach ($transitions as $transition) {
    WorkflowTransition::create([
        'workflow_id' => $workflow->id,
        'name' => $transition['name'],
        'from_place' => $transition['from_place'],
        'to_place' => $transition['to_place'],
        'sort_order' => $transition['sort_order'],
        'meta' => $transition['meta'] ?? null,
    ]);
}
```

### Option B: Configuration File

Alternatively, define in `config/flowstone.php`:

```php
'custom' => [
    'document-approval' => [
        'type' => 'state_machine',
        'supports' => [App\Models\Document::class],
        'marking_store' => [
            'type' => 'method',
            'property' => 'status',
        ],
        'initial_marking' => 'draft',
        'places' => [
            'draft' => null,
            'submitted' => null,
            'under_review' => null,
            'approved' => null,
            'rejected' => null,
        ],
        'transitions' => [
            'submit' => [
                'from' => ['draft'],
                'to' => 'submitted',
                'metadata' => ['roles' => ['author', 'editor']],
            ],
            'start_review' => [
                'from' => ['submitted'],
                'to' => 'under_review',
                'metadata' => ['roles' => ['reviewer']],
            ],
            'approve' => [
                'from' => ['under_review'],
                'to' => 'approved',
                'metadata' => ['roles' => ['manager', 'admin']],
            ],
            'reject' => [
                'from' => ['under_review'],
                'to' => 'rejected',
                'metadata' => ['roles' => ['manager', 'admin']],
            ],
            'revise' => [
                'from' => ['rejected'],
                'to' => 'draft',
                'metadata' => ['roles' => ['author']],
            ],
        ],
    ],
],
```

## Step 3: Use the Workflow

Now you can use the workflow in your application:

### Create and Manage Documents

```php
use App\Models\Document;
use CleaniqueCoders\Flowstone\Enums\Status;

// Create a new document
$document = Document::create([
    'title' => 'My Important Document',
    'content' => 'Document content here...',
    'status' => Status::DRAFT->value,
    'workflow_type' => 'document-approval',
]);

// Check current status
echo $document->getMarking(); // 'draft'

// Get available transitions
$transitions = $document->getEnabledToTransitions();
// Returns: ['submitted' => 'Submitted']

// Check if transitions are available
if ($document->hasEnabledToTransitions()) {
    echo "Document can be transitioned!";
}

// Get the Symfony workflow instance
$workflow = $document->getWorkflow();

// Check if a specific transition is allowed
if ($workflow->can($document, 'submit')) {
    // Apply the transition
    $workflow->apply($document, 'submit');

    // Save the updated state
    $document->save();

    echo "Document submitted for review!";
}
```

### In a Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function show(Document $document)
    {
        $availableTransitions = $document->getEnabledToTransitions();

        return view('documents.show', compact('document', 'availableTransitions'));
    }

    public function transition(Request $request, Document $document)
    {
        $transitionName = $request->input('transition');

        // Get the workflow
        $workflow = $document->getWorkflow();

        // Check if transition is allowed
        if (!$workflow->can($document, $transitionName)) {
            return back()->withErrors(['transition' => 'Invalid transition']);
        }

        // Apply the transition
        $workflow->apply($document, $transitionName);

        // Save the document
        $document->save();

        return back()->with('success', 'Document status updated successfully!');
    }
}
```

### In a Blade View

```blade
{{-- resources/views/documents/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $document->title }}</h1>

    <div class="alert alert-info">
        Current Status: <strong>{{ str($document->getMarking())->title() }}</strong>
    </div>

    <div class="card">
        <div class="card-body">
            <p>{{ $document->content }}</p>
        </div>
    </div>

    @if($availableTransitions)
        <div class="mt-3">
            <h5>Available Actions:</h5>
            <form method="POST" action="{{ route('documents.transition', $document) }}" class="d-inline">
                @csrf
                <div class="btn-group" role="group">
                    @foreach($availableTransitions as $transition => $label)
                        <button type="submit" name="transition" value="{{ $transition }}"
                                class="btn btn-outline-primary">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
```

## Step 4: Add Routes

```php
// routes/web.php
use App\Http\Controllers\DocumentController;

Route::resource('documents', DocumentController::class);
Route::post('documents/{document}/transition', [DocumentController::class, 'transition'])
    ->name('documents.transition');
```

## Advanced Usage

### Role-Based Access Control

Check user permissions before allowing transitions:

```php
public function transition(Request $request, Document $document)
{
    $transitionName = $request->input('transition');

    // Get required roles for this transition
    $requiredRoles = $document->getRolesFromTransition($document->getMarking());

    // Check if user has required roles (implement your own logic)
    if (!$this->userHasRoles(auth()->user(), $requiredRoles)) {
        abort(403, 'Insufficient permissions for this transition');
    }

    // Apply transition...
}
```

### Event Listeners

Listen for workflow events:

```php
// In EventServiceProvider
use Symfony\Component\Workflow\Event\TransitionEvent;

protected $listen = [
    TransitionEvent::class => [
        DocumentTransitionListener::class,
    ],
];
```

```php
// DocumentTransitionListener
class DocumentTransitionListener
{
    public function handle(TransitionEvent $event)
    {
        $document = $event->getSubject();
        $transition = $event->getTransition();

        // Send notifications, log changes, etc.
        Log::info("Document {$document->id} transitioned via {$transition->getName()}");
    }
}
```

### Custom Validation

Add custom validation before transitions:

```php
public function transition(Request $request, Document $document)
{
    $transitionName = $request->input('transition');

    // Custom validation
    if ($transitionName === 'approve' && !$document->hasReviewer()) {
        return back()->withErrors(['transition' => 'Document must have a reviewer']);
    }

    // Apply transition...
}
```

## Testing

Test your workflow implementation:

```php
// tests/Feature/DocumentWorkflowTest.php
use Tests\TestCase;
use App\Models\Document;

class DocumentWorkflowTest extends TestCase
{
    public function test_document_can_be_submitted()
    {
        $document = Document::factory()->create(['status' => 'draft']);

        $workflow = $document->getWorkflow();

        $this->assertTrue($workflow->can($document, 'submit'));

        $workflow->apply($document, 'submit');

        $this->assertEquals('submitted', $document->getMarking());
    }

    public function test_draft_document_cannot_be_approved()
    {
        $document = Document::factory()->create(['status' => 'draft']);

        $workflow = $document->getWorkflow();

        $this->assertFalse($workflow->can($document, 'approve'));
    }
}
```

## Next Steps

Congratulations! You've implemented a basic workflow. Now you can:

1. **Explore [Database Workflows](database-workflows.md)** for dynamic configuration
2. **Learn about [Advanced Usage](advanced-usage.md)** for complex scenarios
3. **Check [Examples](examples.md)** for more workflow patterns
4. **Review [API Reference](../04-api/01-api-reference.md)** for detailed documentation

## Troubleshooting

### Common Issues

**Workflow not found**: Ensure your workflow configuration exists in database or config file.

**Transition not allowed**: Check if the current state allows the transition you're trying to apply.

**Permission denied**: Verify user roles match the transition requirements.

**Caching issues**: Clear cache after configuration changes:

```bash
php artisan cache:clear
```
