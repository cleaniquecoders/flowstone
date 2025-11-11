# Guard Events & Transition Blocking

Guards are powerful validation mechanisms that control whether a workflow transition can be applied. They act as gatekeepers, ensuring that transitions only occur when specific conditions are met.

## Overview

Guards allow you to:

- **Control access** based on user roles and permissions
- **Validate business logic** before applying transitions
- **Provide clear feedback** when transitions are blocked
- **Implement complex conditions** using expressions
- **Combine multiple checks** for robust validation

### Key Features

✅ **Multiple Guards Supported** - Configure multiple guards per transition
✅ **All Must Pass** - Every guard must return `true` for transition to be allowed
✅ **Flexible Configuration** - Single guard or array of guards
✅ **Auto-Detection** - Automatically detects `roles`, `permission`, `permissions` keys
✅ **Detailed Feedback** - Get specific blockers for each failed guard

## Quick Answer

**Q: Does Flowstone support single or multiple guards?**

✅ **Both!** You can configure:

- **Single guard**: `'guard' => ...`
- **Multiple guards**: `'guards' => [...]`
- **Auto-detected**: `'roles'`, `'permission'`, `'permissions'`
- **Mixed**: Combine any of the above

**ALL configured guards must pass** for the transition to be allowed.

## Table of Contents

- [Basic Usage](#basic-usage)
- [Guard Types](#guard-types)
- [Checking Transitions](#checking-transitions)
- [Handling Blockers](#handling-blockers)
- [Guard Configuration](#guard-configuration)
- [Advanced Usage](#advanced-usage)
- [Best Practices](#best-practices)

## Basic Usage

### Checking if a Transition Can Be Applied

```php
use App\Models\Document;

$document = Document::find(1);

// Check if the transition is allowed
if ($document->canApplyTransition('approve')) {
    $document->applyTransition('approve');
    echo "Document approved!";
} else {
    echo "Cannot approve document.";
}
```

### Getting Blocker Messages

```php
if (!$document->canApplyTransition('approve')) {
    // Get detailed blocker messages
    $messages = $document->getTransitionBlockerMessages('approve');

    foreach ($messages as $message) {
        echo "❌ " . $message . "\n";
    }
}

// Output:
// ❌ You need one of these roles: ROLE_APPROVER.
// ❌ You do not have the required permission: approve-documents.
```

## Guard Types

Flowstone supports multiple types of guards to cover different validation scenarios.

### 1. Role-Based Guards

Restrict transitions to users with specific roles.

**Configuration:**

```php
// In transition metadata
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'roles' => ['ROLE_APPROVER', 'ROLE_ADMIN'],
    ],
]
```

**How it works:**

- Checks if the authenticated user has any of the specified roles
- Uses the `hasRole()` method on the user model
- Blocks if user is not authenticated

**Example:**

```php
$transition->meta = [
    'roles' => ['ROLE_MANAGER', 'ROLE_ADMIN'],
];

// Only users with ROLE_MANAGER or ROLE_ADMIN can apply this transition
```

### 2. Permission-Based Guards

Restrict transitions based on Laravel permissions/abilities.

**Configuration:**

```php
// Single permission
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'permission' => 'approve-documents',
    ],
]

// Multiple permissions (user needs at least one)
'publish' => [
    'from' => 'approved',
    'to' => 'published',
    'metadata' => [
        'permissions' => ['edit-documents', 'publish-documents'],
    ],
]
```

**Important: `permission` vs `permissions`**

```php
// Single permission - user needs THIS permission
'permission' => 'approve-documents'

// Single permission with array - user needs ANY of these (OR logic)
'permission' => ['edit-documents', 'approve-documents']  // edit OR approve

// Multiple permissions - user needs ALL of these (AND logic)
'permissions' => ['view-documents', 'approve-documents']  // view AND approve
```

The `permissions` (plural) key creates **separate guards for each permission**, so ALL must pass.

**How it works:**

- First tries `$user->hasPermissionTo($permission)` (Spatie Laravel Permission)
- Falls back to `$user->can($permission)`
- Finally tries Laravel's `Gate::allows($permission, $subject)`
- Compatible with most permission packages

### 3. Method-Based Guards

Call a custom method on your model for business logic validation.

**Configuration:**

```php
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'guard' => [
            'type' => 'method',
            'value' => 'canBeApproved',
        ],
    ],
]
```

**Model Implementation:**

```php
class Document extends Model
{
    use InteractsWithWorkflow;

    public function canBeApproved(): bool
    {
        // Custom business logic
        return $this->reviews()->count() >= 2
            && $this->hasAllRequiredFields()
            && !$this->hasBlockingIssues();
    }
}
```

### 4. Expression-Based Guards

Use simple expressions for inline validation.

**Configuration:**

```php
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'guard' => "is_granted('approve-documents')",
    ],
]

// Method call on subject
'publish' => [
    'from' => 'approved',
    'to' => 'published',
    'metadata' => [
        'guard' => 'subject.isReadyToPublish()',
    ],
]
```

**Supported Patterns:**

- `is_granted('permission')` - Check permission
- `subject.methodName()` - Call method on model
- Method name as string - Calls method on model

> **Note:** Full Symfony Expression Language support (with `and`, `or`, complex expressions) will be added in a future update.

## Checking Transitions

### Basic Check

```php
// Returns true/false
if ($model->canApplyTransition('approve')) {
    // Safe to apply
}
```

### Get Detailed Blockers

```php
use CleaniqueCoders\Flowstone\Guards\TransitionBlocker;

$blockers = $model->getTransitionBlockers('approve');

foreach ($blockers as $blocker) {
    echo "Code: " . $blocker->getCode() . "\n";
    echo "Message: " . $blocker->getMessage() . "\n";
    echo "Parameters: " . json_encode($blocker->getParameters()) . "\n";
}
```

### Blocker Codes

| Code | Description |
|------|-------------|
| `BLOCKED_BY_MARKING` | Transition not available from current state |
| `BLOCKED_BY_ROLE` | User lacks required roles |
| `BLOCKED_BY_PERMISSION` | User lacks required permission |
| `BLOCKED_BY_EXPRESSION_GUARD` | Expression guard returned false |
| `BLOCKED_BY_CUSTOM_GUARD` | Custom guard method failed |
| `UNKNOWN` | Unknown reason |

## Handling Blockers

### In Controllers

```php
public function approve(Request $request, Document $document)
{
    if (!$document->canApplyTransition('approve')) {
        $messages = $document->getTransitionBlockerMessages('approve');

        return back()->withErrors([
            'transition' => $messages,
        ]);
    }

    $document->applyTransition('approve');

    return redirect()
        ->route('documents.show', $document)
        ->with('success', 'Document approved successfully!');
}
```

### In Blade Views

```blade
@if($document->canApplyTransition('approve'))
    <button type="submit" class="btn btn-success">
        Approve Document
    </button>
@else
    <button type="button" class="btn btn-secondary" disabled title="Cannot approve">
        Approve Document
    </button>

    <div class="alert alert-warning">
        <strong>Cannot approve because:</strong>
        <ul>
            @foreach($document->getTransitionBlockerMessages('approve') as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### In Livewire Components

```php
class DocumentApproval extends Component
{
    public Document $document;
    public array $blockers = [];

    public function mount()
    {
        if (!$this->document->canApplyTransition('approve')) {
            $this->blockers = $this->document->getTransitionBlockerMessages('approve');
        }
    }

    public function approve()
    {
        if (!$this->document->canApplyTransition('approve')) {
            $this->addError('transition', 'Cannot approve document at this time.');
            return;
        }

        $this->document->applyTransition('approve');
        $this->dispatch('document-approved');
    }
}
```

## Guard Configuration

### Single vs Multiple Guards

Flowstone supports **both single and multiple guards** per transition:

| Configuration | Syntax | Behavior |
|---------------|--------|----------|
| **Single Guard** | `'guard' => ...` | One guard condition |
| **Multiple Guards** | `'guards' => [...]` | Array of guards - **ALL must pass** |
| **Mixed** | Both `guard` and `guards` | All conditions must pass |
| **Auto-Detected** | `'roles'`, `'permission'`, `'permissions'` | Automatically converted to guards |

**Important:** When multiple guards are configured, **ALL guards must pass** for the transition to be allowed. If any guard fails, the transition is blocked.

### Configuration Methods

Guards can be configured either in code (when defining workflows) or in the database (when using the Workflow model).

### Configuration via Code

When defining workflows in configuration files:

```php
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'guard' => "is_granted('approve-documents')",
    ],
]
```

### Configuration via Database

When using the `Workflow` and `WorkflowTransition` models:

#### Example 1: Single Guard

```php
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

// Single role guard
WorkflowTransition::create([
    'workflow_id' => $workflow->id,
    'name' => 'approve',
    'from_place' => 'review',
    'to_place' => 'approved',
    'meta' => [
        'guard' => "is_granted('approve-documents')",
    ],
]);
```

#### Example 2: Multiple Guards (ALL Must Pass)

```php
// Multiple guards using 'guards' array
WorkflowTransition::create([
    'workflow_id' => $workflow->id,
    'name' => 'approve',
    'from_place' => 'review',
    'to_place' => 'approved',
    'meta' => [
        'guards' => [
            ['type' => 'role', 'value' => ['ROLE_APPROVER']],        // Must have role
            ['type' => 'permission', 'value' => 'approve-documents'], // AND permission
            ['type' => 'method', 'value' => 'hasMinimumReviews'],    // AND pass method
        ],
    ],
]);

// Result: User needs ROLE_APPROVER AND approve-documents AND hasMinimumReviews() === true
```

#### Example 3: Auto-Detected Multiple Guards

```php
// These keys are automatically detected and converted to guards
WorkflowTransition::create([
    'workflow_id' => $workflow->id,
    'name' => 'publish',
    'from_place' => 'approved',
    'to_place' => 'published',
    'meta' => [
        'roles' => ['ROLE_PUBLISHER', 'ROLE_ADMIN'],  // User needs ONE of these roles
        'permission' => 'publish-content',             // AND this permission
        'permissions' => ['edit-articles', 'edit-documents'], // AND ONE of these
    ],
]);

// All three conditions must pass!
```

#### Example 4: Mixed Configuration

```php
// You can mix single 'guard' with auto-detected guards
WorkflowTransition::create([
    'workflow_id' => $workflow->id,
    'name' => 'approve',
    'from_place' => 'review',
    'to_place' => 'approved',
    'meta' => [
        'roles' => ['ROLE_MANAGER'],                          // Auto-detected
        'permission' => 'approve-documents',                   // Auto-detected
        'guard' => ['type' => 'method', 'value' => 'isReady'], // Explicit guard
        'guards' => [                                          // Multiple explicit guards
            ['type' => 'method', 'value' => 'hasMinimumReviews'],
        ],
    ],
]);

// All guards above will be combined and evaluated
// User needs: ROLE_MANAGER AND approve-documents AND isReady() AND hasMinimumReviews()
```

#### Understanding AND vs OR Logic

This is important to understand for proper guard configuration:

| Configuration | Number of Guards | Logic | Example Result |
|---------------|------------------|-------|----------------|
| `'roles' => ['A', 'B']` | 1 guard | User needs **A OR B** | ANY role |
| `'permission' => 'x'` | 1 guard | User needs **x** | Specific permission |
| `'permission' => ['x', 'y']` | 1 guard | User needs **x OR y** | ANY permission |
| `'permissions' => ['x', 'y']` | 2 guards | User needs **x AND y** | ALL permissions |

**Key Points:**

- `permission` (singular) = 1 guard, array uses OR logic
- `permissions` (plural) = multiple guards, each must pass (AND logic)
- All guard types combined = AND logic
- Within `roles` or single `permission` array = OR logic

**Example 1: OR within, AND across**

```php
'meta' => [
    'roles' => ['MANAGER', 'ADMIN'],        // Guard 1: MANAGER OR ADMIN
    'permission' => ['edit', 'publish'],    // Guard 2: edit OR publish
]
// User needs: (MANAGER OR ADMIN) AND (edit OR publish)
```

**Example 2: Requiring ALL permissions**

```php
'meta' => [
    'permissions' => ['view-docs', 'approve-docs', 'publish-docs'],  // 3 separate guards
]
// User needs: view-docs AND approve-docs AND publish-docs (all 3)
```

**Example 3: Complex combination**

```php
'meta' => [
    'roles' => ['MANAGER', 'ADMIN'],           // Guard 1: MANAGER OR ADMIN (any)
    'permission' => 'approve-docs',             // Guard 2: approve-docs (must have)
    'permissions' => ['view-docs', 'edit-docs'], // Guard 3 & 4: view-docs AND edit-docs (both)
    'guard' => ['type' => 'method', 'value' => 'isReady'], // Guard 5: isReady() === true
]
// User needs ALL 5 guards to pass:
// 1. (MANAGER OR ADMIN) AND
// 2. approve-docs AND
// 3. view-docs AND
// 4. edit-docs AND
// 5. isReady() === true
```

## How Guards Work Internally

Understanding the guard evaluation process can help with debugging and advanced usage.

### Evaluation Flow

When you call `canApplyTransition()` or `getTransitionBlockers()`:

1. **Check Marking** - Verify the transition is available from the current state
   - If not available → Return `BLOCKED_BY_MARKING` blocker
   - If available → Continue to guard checks

2. **Extract Guards** - Parse transition metadata to extract guard configurations
   - Looks for `roles`, `permission`, `permissions`, `guard`, `guards` keys
   - Normalizes them into a uniform format

3. **Evaluate Each Guard** - Check each guard condition
   - **Role guards**: Calls `auth()->user()->hasRole($role)`
   - **Permission guards**: Tries `hasPermissionTo()`, `can()`, then `Gate::allows()`
   - **Method guards**: Calls the specified method on the model
   - **Expression guards**: Parses and evaluates the expression

4. **Collect Blockers** - If any guard fails, create a `TransitionBlocker`
   - Blocker includes the reason (code) and helpful message
   - All failed guards produce blockers

5. **Return Result**
   - `canApplyTransition()` returns `true` if no blockers, `false` otherwise
   - `getTransitionBlockers()` returns array of `TransitionBlocker` instances

### Guard Resolution Order

Guards are stored in the transition's `metadata` field and resolved in this order:

```php
// From transition metadata
[
    'roles' => ['ROLE_MANAGER'],           // Checked first
    'permission' => 'approve',              // Checked second
    'permissions' => ['approve', 'edit'],   // Each checked as separate guard
    'guard' => 'canApprove',                // Checked as expression/method
    'guards' => [                           // Each checked in order
        ['type' => 'role', 'value' => ['ROLE_ADMIN']],
        ['type' => 'method', 'value' => 'customCheck'],
    ],
]
```

**All guards must pass** for the transition to be allowed.

### Guard Type Detection

The system automatically detects guard types based on the metadata structure:

```php
// Detected as role guard
'roles' => ['ROLE_X']

// Detected as permission guard
'permission' => 'perm-name'
'permissions' => ['perm1', 'perm2']

// Detected by explicit type
'guard' => ['type' => 'method', 'value' => 'methodName']

// String guards are treated as expressions
'guard' => "is_granted('permission')"
```

## Advanced Usage

### Complex Business Logic

```php
class Order extends Model
{
    use InteractsWithWorkflow;

    public function canBeShipped(): bool
    {
        return $this->payment_status === 'paid'
            && $this->items()->count() > 0
            && $this->items()->every(fn($item) => $item->in_stock)
            && $this->shipping_address !== null;
    }

    public function canBeCancelled(): bool
    {
        return $this->status !== 'shipped'
            && $this->created_at->diffInHours(now()) < 24;
    }
}

// In transition configuration
'ship' => [
    'metadata' => [
        'guard' => [
            'type' => 'method',
            'value' => 'canBeShipped',
        ],
    ],
]
```

### Dynamic Guards

You can update guards at runtime and the changes will take effect after regenerating the workflow configuration:

```php
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

// Update guard configuration
$transition = WorkflowTransition::where('name', 'approve')->first();

$transition->update([
    'meta' => [
        'roles' => ['ROLE_SENIOR_MANAGER'], // More restrictive
        'permission' => 'approve-high-value-documents',
    ],
]);

// Regenerate the workflow configuration
$workflow = $transition->workflow;
$workflow->setWorkflow(true); // Force regeneration

// Clear cache to ensure changes are picked up
Cache::forget($workflow->getWorkflowKey());

// Now the new guards will be active
$document = Document::find(1);
$document->canApplyTransition('approve'); // Uses new guards
```

**Important Notes:**

- Changes to guards require regenerating the workflow config with `setWorkflow(true)`
- Cache must be cleared for immediate effect
- Consider the impact on in-flight workflows when changing guards

### Audit Trail with Guards

Guards work seamlessly with the audit trail:

```php
if (!$document->canApplyTransition('approve')) {
    // Log the blocked attempt (optional)
    \Log::warning('Transition blocked', [
        'user' => auth()->id(),
        'document' => $document->id,
        'transition' => 'approve',
        'blockers' => $document->getTransitionBlockerMessages('approve'),
    ]);
}
```

## Best Practices

### 1. Use the Right Guard Type

```php
// ✅ Good: Use roles for simple access control
'metadata' => [
    'roles' => ['ROLE_MANAGER'],
]

// ✅ Good: Use permissions for fine-grained control
'metadata' => [
    'permission' => 'approve-documents',
]

// ✅ Good: Use methods for complex business logic
'metadata' => [
    'guard' => [
        'type' => 'method',
        'value' => 'meetsApprovalCriteria',
    ],
]

// ❌ Avoid: Don't put complex logic in expressions (yet)
'metadata' => [
    'guard' => 'complicated expression here', // Limited support currently
]
```

### 2. Provide Clear Error Messages

```php
public function canBeApproved(): bool
{
    if ($this->reviews()->count() < 2) {
        // Guards return bool, but you can log reasons
        \Log::info('Document needs more reviews', ['document' => $this->id]);
        return false;
    }

    return true;
}
```

### 3. Combine Guards Logically

```php
// ✅ Good: Combine related checks
'metadata' => [
    'roles' => ['ROLE_MANAGER', 'ROLE_ADMIN'],
    'guard' => [
        'type' => 'method',
        'value' => 'meetsBusinessRules',
    ],
]

// ❌ Avoid: Don't over-complicate
'metadata' => [
    'guards' => [
        ['type' => 'role', 'value' => ['ROLE_A']],
        ['type' => 'role', 'value' => ['ROLE_B']],
        ['type' => 'role', 'value' => ['ROLE_C']],
        // Too many separate role checks
    ],
]
```

### 4. Test Your Guards

```php
test('manager can approve documents', function () {
    $user = User::factory()->create();
    $user->assignRole('ROLE_MANAGER');
    auth()->login($user);

    $document = Document::factory()->create(['marking' => 'review']);

    expect($document->canApplyTransition('approve'))->toBeTrue();
});

test('regular user cannot approve documents', function () {
    $user = User::factory()->create(); // No roles
    auth()->login($user);

    $document = Document::factory()->create(['marking' => 'review']);

    expect($document->canApplyTransition('approve'))->toBeFalse();

    $blockers = $document->getTransitionBlockers('approve');
    expect($blockers)->toHaveCount(1);
    expect($blockers[0]->getCode())->toBe(TransitionBlocker::BLOCKED_BY_ROLE);
});
```

### 5. Handle Guards in UI

```blade
{{-- Show why a button is disabled --}}
<button
    type="submit"
    @if(!$model->canApplyTransition('approve'))
        disabled
        title="{{ implode(', ', $model->getTransitionBlockerMessages('approve')) }}"
    @endif>
    Approve
</button>

{{-- Or show detailed feedback --}}
@if(!$model->canApplyTransition('approve'))
    <div class="alert alert-info">
        <strong>This action is currently unavailable:</strong>
        <ul>
            @foreach($model->getTransitionBlockerMessages('approve') as $reason)
                <li>{{ $reason }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

## Common Usage Patterns

### Pattern 1: Admin Override

Allow admins to bypass certain restrictions:

```php
class Document extends Model
{
    use InteractsWithWorkflow;

    public function canBePublished(): bool
    {
        // Admins can always publish
        if (auth()->user()?->hasRole('ROLE_ADMIN')) {
            return true;
        }

        // Others must meet criteria
        return $this->reviews()->approved()->count() >= 2
            && $this->word_count >= 500;
    }
}

// In transition config
'publish' => [
    'metadata' => [
        'guard' => ['type' => 'method', 'value' => 'canBePublished'],
    ],
]
```

### Pattern 2: Time-Based Guards

Restrict actions based on time conditions:

```php
class Order extends Model
{
    use InteractsWithWorkflow;

    public function canBeCancelled(): bool
    {
        // Can only cancel within 24 hours
        return $this->created_at->diffInHours(now()) < 24;
    }

    public function canBeRefunded(): bool
    {
        // Can only refund within 30 days after delivery
        return $this->delivered_at
            && $this->delivered_at->diffInDays(now()) < 30;
    }
}
```

### Pattern 3: Relationship-Based Guards

Check related models before allowing transitions:

```php
class Article extends Model
{
    use InteractsWithWorkflow;

    public function canBePublished(): bool
    {
        return $this->author !== null
            && $this->category !== null
            && $this->featuredImage !== null
            && $this->tags()->count() > 0;
    }

    public function hasMinimumReviews(): bool
    {
        return $this->reviews()
            ->where('status', 'approved')
            ->count() >= 2;
    }
}
```

### Pattern 4: Hierarchical Approval

Different approval levels based on value:

```php
class PurchaseOrder extends Model
{
    use InteractsWithWorkflow;

    public function canBeApprovedByManager(): bool
    {
        return $this->total_amount < 10000;
    }

    public function requiresDirectorApproval(): bool
    {
        return $this->total_amount >= 10000
            && $this->total_amount < 50000;
    }

    public function requiresCEOApproval(): bool
    {
        return $this->total_amount >= 50000;
    }
}

// Different transitions based on amount
'approve_manager' => [
    'metadata' => [
        'roles' => ['ROLE_MANAGER'],
        'guard' => ['type' => 'method', 'value' => 'canBeApprovedByManager'],
    ],
]

'approve_director' => [
    'metadata' => [
        'roles' => ['ROLE_DIRECTOR'],
        'guard' => ['type' => 'method', 'value' => 'requiresDirectorApproval'],
    ],
]
```

### Pattern 5: Combined Role and Business Logic

Mix authorization with business rules:

```php
class Invoice extends Model
{
    use InteractsWithWorkflow;

    public function canBeSentToCustomer(): bool
    {
        // Business logic checks
        return $this->line_items()->count() > 0
            && $this->total > 0
            && $this->customer_email !== null
            && !$this->hasErrors();
    }
}

// Transition requires both role AND business logic
'send' => [
    'metadata' => [
        'roles' => ['ROLE_ACCOUNTANT', 'ROLE_BILLING'],  // Must have role
        'guard' => [
            'type' => 'method',
            'value' => 'canBeSentToCustomer',  // AND pass business checks
        ],
    ],
]
```

### Pattern 6: Context-Aware Guards

Guards that consider additional context:

```php
class Document extends Model
{
    use InteractsWithWorkflow;

    public function canBeApproved(array $context = []): bool
    {
        // Check if this is a fast-track approval
        if ($context['fast_track'] ?? false) {
            // Fast-track requires admin role
            return auth()->user()?->hasRole('ROLE_ADMIN');
        }

        // Normal approval flow
        return $this->reviews()->approved()->count() >= 2;
    }
}

// Usage with context
if ($document->canApplyTransition('approve')) {
    $document->applyTransition('approve', ['fast_track' => true]);
}
```

## Integration with Spatie Laravel Permission

If you're using [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission), guards integrate seamlessly:

```php
// Assign roles
$user->assignRole('editor');
$user->assignRole('moderator');

// Grant permissions
$user->givePermissionTo('edit articles');
$user->givePermissionTo('publish articles');

// Configure guards
'publish' => [
    'metadata' => [
        'roles' => ['editor', 'moderator'],
        'permission' => 'publish articles',
    ],
]

// Guards automatically work with Spatie
if ($article->canApplyTransition('publish')) {
    // User has role AND permission
}
```

## Troubleshooting

### Guards Not Working

1. **Check transition is enabled first**:

   Guards are only evaluated if the transition is available from the current marking.

   ```php
   $transitions = $model->getEnabledTransitions();
   // Make sure your transition is in the list

   // Check current marking
   dd($model->marking, $model->getMarkedPlaces());
   ```

2. **Verify guard configuration**:

   For Workflow models, check the guard configuration:

   ```php
   // For Workflow model instances
   $guards = $workflow->getTransitionGuardConfig('approve');
   dd($guards); // ['roles' => [...], 'permission' => '...']

   // Check the full config
   dd($workflow->config['transitions']['approve']['metadata']);
   ```

3. **Check authentication**:

   Role and permission guards require an authenticated user:

   ```php
   dd(auth()->check()); // Must be true
   dd(auth()->user()); // Must have hasRole() or hasPermissionTo() methods
   ```

4. **Debug blockers**:

   Get detailed information about why a transition is blocked:

   ```php
   $blockers = $model->getTransitionBlockers('approve');

   foreach ($blockers as $blocker) {
       echo "Code: " . $blocker->getCode() . "\n";
       echo "Message: " . $blocker->getMessage() . "\n";
       echo "Params: " . json_encode($blocker->getParameters()) . "\n";
   }
   ```

### Custom Guard Method Not Called

```php
// ❌ Wrong: Method doesn't exist
'guard' => ['type' => 'method', 'value' => 'canApprove']

// ✅ Correct: Method must exist on model
class Document extends Model {
    public function canApprove(): bool {
        return true;
    }
}
```

### Permission Checks Failing

```php
// Make sure your User model has permission checking methods
class User extends Authenticatable
{
    use HasRoles; // Spatie Laravel Permission

    // Or implement your own
    public function hasPermissionTo(string $permission): bool
    {
        // Your logic
    }
}
```

## API Reference

### InteractsWithWorkflow Trait Methods

These methods are available on any model using the `InteractsWithWorkflow` trait:

| Method | Returns | Description |
|--------|---------|-------------|
| `canApplyTransition(string $name)` | `bool` | Check if transition can be applied (checks marking + guards) |
| `getTransitionBlockers(string $name)` | `array<TransitionBlocker>` | Get all blockers preventing the transition |
| `getTransitionBlockerMessages(string $name)` | `array<string>` | Get user-friendly blocker messages |
| `applyTransition(string $name, array $context = [])` | `Marking` | Apply a transition (throws exception if blocked) |
| `getEnabledTransitions()` | `array<Transition>` | Get all transitions available from current marking |

### Workflow Model Methods

Additional methods available on the `Workflow` model:

| Method | Returns | Description |
|--------|---------|-------------|
| `getTransitionGuardConfig(string $name)` | `array` | Get simplified guard configuration for display |

**Example:**

```php
$guards = $workflow->getTransitionGuardConfig('approve');
// Returns: ['roles' => ['ROLE_APPROVER'], 'permission' => 'approve-docs']
```

### TransitionBlocker Class

The `TransitionBlocker` class represents a reason why a transition is blocked:

| Method | Returns | Description |
|--------|---------|-------------|
| `getMessage()` | `string` | Get human-readable blocker message |
| `getCode()` | `string` | Get blocker code constant (e.g., `BLOCKED_BY_ROLE`) |
| `getParameters()` | `array` | Get additional parameters (e.g., required roles) |
| `toArray()` | `array` | Convert to array representation |
| `__toString()` | `string` | Convert to string (returns message) |

**Static Factory Methods:**

```php
TransitionBlocker::createBlockedByMarking(string $message = null)
TransitionBlocker::createBlockedByRole(array $roles)
TransitionBlocker::createBlockedByPermission(string|array $permission)
TransitionBlocker::createBlockedByExpressionGuard(string $expression)
TransitionBlocker::createBlockedByCustomGuard(string $message)
TransitionBlocker::createUnknown(string $message = null)
```

## See Also

- [Workflows Guide](01-workflows.md)
- [Audit Trail](04-audit-trail.md)
- [API Reference](../04-api/01-api-reference.md)
- [Symfony Workflow Guards](https://symfony.com/doc/current/workflow.html#using-events)
