# Guard Events & Transition Blocking

Guards are powerful validation mechanisms that control whether a workflow transition can be applied. They act as gatekeepers, ensuring that transitions only occur when specific conditions are met.

## Overview

Guards allow you to:

- **Control access** based on user roles and permissions
- **Validate business logic** before applying transitions
- **Provide clear feedback** when transitions are blocked
- **Implement complex conditions** using expressions
- **Combine multiple checks** for robust validation

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

// Multiple permissions (all must pass)
'publish' => [
    'from' => 'approved',
    'to' => 'published',
    'metadata' => [
        'permissions' => ['edit-documents', 'publish-documents'],
    ],
]
```

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

### Single Guard

```php
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'guard' => "is_granted('approve-documents')",
    ],
]
```

### Multiple Guards (All Must Pass)

```php
'approve' => [
    'from' => 'review',
    'to' => 'approved',
    'metadata' => [
        'guards' => [
            ['type' => 'role', 'value' => ['ROLE_APPROVER']],
            ['type' => 'permission', 'value' => 'approve-documents'],
            ['type' => 'method', 'value' => 'hasMinimumReviews'],
        ],
    ],
]
```

### Combined Configuration

```php
'publish' => [
    'from' => 'approved',
    'to' => 'published',
    'metadata' => [
        // Role check
        'roles' => ['ROLE_PUBLISHER', 'ROLE_ADMIN'],

        // Permission check
        'permission' => 'publish-content',

        // Custom method check
        'guard' => [
            'type' => 'method',
            'value' => 'isReadyForPublication',
        ],
    ],
]
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

```php
// You can update guards at runtime
$transition = WorkflowTransition::where('name', 'approve')->first();

$transition->update([
    'meta' => [
        'roles' => ['ROLE_SENIOR_MANAGER'], // More restrictive
        'permission' => 'approve-high-value-documents',
    ],
]);

// Refresh workflow cache
$document->getWorkflow(); // Will pick up new guards
```

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

   ```php
   $transitions = $model->getEnabledTransitions();
   // Make sure your transition is in the list
   ```

2. **Verify guard configuration**:

   ```php
   $guards = $model->getTransitionGuards('approve');
   dd($guards); // Check what guards are configured
   ```

3. **Check authentication**:

   ```php
   dd(auth()->check()); // User must be authenticated for role/permission guards
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

### Model Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `canApplyTransition(string $name)` | `bool` | Check if transition can be applied |
| `getTransitionBlockers(string $name)` | `array<TransitionBlocker>` | Get all blockers |
| `getTransitionBlockerMessages(string $name)` | `array<string>` | Get blocker messages |
| `getTransitionGuards(string $name)` | `array` | Get guard configurations |

### TransitionBlocker Class

| Method | Returns | Description |
|--------|---------|-------------|
| `getMessage()` | `string` | Get blocker message |
| `getCode()` | `string` | Get blocker code constant |
| `getParameters()` | `array` | Get additional parameters |
| `toArray()` | `array` | Convert to array |
| `__toString()` | `string` | Convert to string (message) |

## See Also

- [Workflows Guide](01-workflows.md)
- [Audit Trail](04-audit-trail.md)
- [API Reference](../04-api/01-api-reference.md)
- [Symfony Workflow Guards](https://symfony.com/doc/current/workflow.html#using-events)
