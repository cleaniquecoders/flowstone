# Advanced Workflow Features

Flowstone provides advanced workflow capabilities that go beyond basic state transitions, including support for multiple simultaneous states, transition context, and metadata management.

## Table of Contents

- [Multiple State Support](#multiple-state-support)
- [Context Support](#context-support)
- [Metadata Management](#metadata-management)
- [Use Cases](#use-cases)
- [Best Practices](#best-practices)

---

## Multiple State Support

Flowstone supports two types of workflows with different state management strategies:

### State Machines vs Workflows

**State Machine** (`type: 'state_machine'`):

- Can only be in **one state at a time**
- Uses single_state marking store
- Common for linear processes (draft → review → published)

**Workflow** (`type: 'workflow'`):

- Can be in **multiple states simultaneously**
- Uses multiple_state marking store
- Common for parallel processes (testing + reviewing + editing)

### Checking Workflow Type

```php
// Check if workflow supports multiple states
if ($document->supportsMultipleStates()) {
    // This is a workflow type (can be in multiple states)
} else {
    // This is a state machine (single state only)
}
```

### Working with Multiple States

#### Get All Marked Places

```php
// Get array of all current places
$places = $document->getMarkedPlaces();
// Returns: ['editing', 'reviewing', 'testing']
```

#### Check if in Specific Place

```php
// Check if document is in a specific place
if ($document->isInPlace('reviewing')) {
    // Document is currently being reviewed
}
```

#### Check Multiple Places

```php
// Check if in ALL specified places
if ($document->isInAllPlaces(['editing', 'reviewing'])) {
    // Document is in both editing AND reviewing states
}

// Check if in ANY of the specified places
if ($document->isInAnyPlace(['published', 'archived'])) {
    // Document is in either published OR archived state
}
```

### Validating Marking Store Type

Flowstone can validate that your marking store configuration matches your workflow type:

```php
try {
    $document->validateMarkingStoreType();
} catch (\LogicException $e) {
    // Configuration mismatch detected
    echo $e->getMessage();
}
```

This validation ensures:

- State machines use `single_state` marking store
- Workflows can use either `single_state` or `multiple_state`

### Configuration Example

**State Machine Configuration:**

```php
'type' => 'state_machine',
'marking_store' => [
    'type' => 'single_state',
    'arguments' => ['marking'],
],
'places' => ['draft', 'review', 'published'],
'transitions' => [
    'submit' => ['from' => 'draft', 'to' => 'review'],
    'publish' => ['from' => 'review', 'to' => 'published'],
],
```

**Workflow Configuration (Multiple States):**

```php
'type' => 'workflow',
'marking_store' => [
    'type' => 'multiple_state',
    'arguments' => ['marking'],
],
'places' => ['editing', 'reviewing', 'testing', 'published'],
'transitions' => [
    'start_review' => ['from' => 'editing', 'to' => 'reviewing'],
    'start_testing' => ['from' => 'reviewing', 'to' => 'testing'],
    'publish' => ['from' => ['reviewing', 'testing'], 'to' => 'published'],
],
```

---

## Context Support

Context allows you to pass additional data through transitions and retrieve it later. This is useful for tracking reasons, approver information, notes, and other metadata about state changes.

### Passing Context in Transitions

#### Basic Context Usage

```php
// Apply transition with context
$context = [
    'reason' => 'All requirements met',
    'approver' => 'John Doe',
    'priority' => 'high',
    'notes' => 'Ready for production',
];

$document->applyTransition('approve', $context);
```

#### Getting Marking and Context Together

```php
$result = $document->applyTransitionWithContext('approve', $context);

// Returns: ['marking' => Marking, 'context' => array]
$marking = $result['marking'];
$contextData = $result['context'];
```

### Retrieving Context from History

#### Get Last Transition Context

```php
// Get context from the most recent transition
$lastContext = $document->getLastTransitionContext();

if ($lastContext) {
    $reason = $lastContext['reason'] ?? 'No reason provided';
    $approver = $lastContext['approver'] ?? 'Unknown';
}
```

#### Get Context for Specific Transition

```php
// Get context from a specific transition by name
$publishContext = $document->getTransitionContext('publish');

if ($publishContext) {
    echo "Published by: " . $publishContext['approver'];
    echo "Reason: " . $publishContext['reason'];
}
```

### Context in Guards

Guards can access transition context for conditional logic:

#### Define Context-Aware Guard Method

```php
class Document extends Model
{
    use InteractsWithWorkflow;

    /**
     * Guard method that checks context
     */
    public function canBeApprovedWithContext(array $context = []): bool
    {
        // Check if high priority items can be approved
        if (isset($context['priority']) && $context['priority'] === 'high') {
            return true;
        }

        // Check if approver is authorized
        $authorizedApprovers = ['John Doe', 'Jane Smith'];
        if (isset($context['approver']) && in_array($context['approver'], $authorizedApprovers)) {
            return true;
        }

        return false;
    }
}
```

#### Configure Guard in Transition Metadata

```php
'transitions' => [
    'approve' => [
        'from' => ['review'],
        'to' => 'approved',
        'metadata' => [
            'guard' => [
                'type' => 'method',
                'value' => 'canBeApprovedWithContext',
            ],
        ],
    ],
],
```

#### Apply Transition with Context

```php
$context = [
    'approver' => 'John Doe',
    'priority' => 'high',
];

if ($document->canApplyTransition('approve')) {
    $document->applyTransition('approve', $context);
}
```

### Context Storage and Audit Trail

When audit trail is enabled, context is automatically stored:

```php
// Enable audit trail for workflow
$workflow->update(['audit_trail_enabled' => true]);

// Apply transition with context
$document->applyTransition('approve', [
    'approver' => 'John Doe',
    'comments' => 'All checks passed',
    'priority' => 'high',
]);

// Context is stored in workflow_audit_logs table
$lastLog = $document->auditLogs()->latest()->first();
echo $lastLog->context['approver']; // "John Doe"
echo $lastLog->context['comments']; // "All checks passed"
```

---

## Metadata Management

Metadata allows you to attach additional information to workflows, places, and transitions. This is useful for UI customization, documentation, and business logic.

### Understanding Metadata Levels

Flowstone supports metadata at three levels:

1. **Workflow Metadata** - Applies to the entire workflow
2. **Place Metadata** - Applies to specific states/places
3. **Transition Metadata** - Applies to specific transitions

### Getting Workflow Metadata

```php
// Get all workflow metadata
$metadata = $document->getWorkflowMetadata();

// Get specific workflow metadata by key
$department = $document->getWorkflowMetadata('department');
$priority = $document->getWorkflowMetadata('priority');
```

### Getting Place Metadata

```php
// Get all metadata for a specific place
$draftMeta = $document->getPlaceMetadata('draft');

// Get specific place metadata by key
$color = $document->getPlaceMetadata('draft', 'color');
$icon = $document->getPlaceMetadata('draft', 'icon');
$description = $document->getPlaceMetadata('draft', 'description');
```

### Getting Transition Metadata

```php
// Get all metadata for a specific transition
$approveMeta = $document->getTransitionMetadata('approve');

// Get specific transition metadata by key
$requiresApproval = $document->getTransitionMetadata('approve', 'requires_approval');
$notification = $document->getTransitionMetadata('approve', 'notification');
$icon = $document->getTransitionMetadata('approve', 'icon');
```

### Getting All Metadata at Once

```php
// Get all places with their metadata
$placesWithMeta = $document->getPlacesWithMetadata();
/*
Returns:
[
    'draft' => ['color' => 'gray', 'icon' => 'draft-icon'],
    'review' => ['color' => 'yellow', 'icon' => 'eye-icon'],
    'published' => ['color' => 'green', 'icon' => 'check-icon'],
]
*/

// Get all transitions with their metadata
$transitionsWithMeta = $document->getTransitionsWithMetadata();
/*
Returns:
[
    'submit' => ['requires_approval' => false],
    'approve' => ['requires_approval' => true, 'notification' => 'email'],
]
*/
```

### Generic Metadata Access

```php
// Generic method with type and name parameters
$workflowMeta = $document->getMetadata(null, 'workflow');
$placeMeta = $document->getMetadata(null, 'place', 'draft');
$transitionMeta = $document->getMetadata(null, 'transition', 'approve');

// Get specific key
$color = $document->getMetadata('color', 'place', 'draft');
```

### Configuring Metadata

Metadata is configured in the workflow definition:

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

$workflow = Workflow::create([
    'name' => 'document-workflow',
    'type' => 'state_machine',
    'meta' => [
        'department' => 'editorial',
        'priority' => 'high',
        'sla_hours' => 24,
    ],
]);

// Configure place with metadata
$workflow->places()->create([
    'name' => 'draft',
    'meta' => [
        'color' => 'gray',
        'icon' => 'draft-icon',
        'description' => 'Initial draft state',
        'allow_editing' => true,
    ],
]);

// Configure transition with metadata
$workflow->transitions()->create([
    'name' => 'approve',
    'from' => ['review'],
    'to' => 'approved',
    'meta' => [
        'requires_approval' => true,
        'notification' => 'email',
        'icon' => 'check-circle',
        'button_color' => 'green',
        'roles' => ['ROLE_APPROVER', 'ROLE_ADMIN'],
    ],
]);
```

---

## Use Cases

### Use Case 1: Document Approval with Context

```php
class ApprovalController extends Controller
{
    public function approve(Document $document, Request $request)
    {
        $validated = $request->validate([
            'comments' => 'required|string',
            'urgency' => 'required|in:low,medium,high',
        ]);

        $context = [
            'approver' => auth()->user()->name,
            'approver_id' => auth()->id(),
            'comments' => $validated['comments'],
            'urgency' => $validated['urgency'],
            'ip_address' => request()->ip(),
            'timestamp' => now()->toIso8601String(),
        ];

        $document->applyTransition('approve', $context);

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Document approved successfully');
    }

    public function showApprovalHistory(Document $document)
    {
        // Get all approval contexts
        $approvals = $document->auditLogs()
            ->where('transition', 'approve')
            ->get()
            ->map(function ($log) {
                return [
                    'approver' => $log->context['approver'] ?? 'Unknown',
                    'comments' => $log->context['comments'] ?? '',
                    'urgency' => $log->context['urgency'] ?? 'normal',
                    'date' => $log->created_at,
                ];
            });

        return view('documents.approval-history', compact('document', 'approvals'));
    }
}
```

### Use Case 2: Parallel Workflow with Multiple States

```php
class Article extends Model
{
    use InteractsWithWorkflow;

    public function canBePublished(): bool
    {
        // Article must pass both review and testing before publishing
        return $this->isInAllPlaces(['reviewed', 'tested']);
    }

    public function getWorkflowStatus(): string
    {
        if ($this->isInPlace('published')) {
            return 'Published';
        }

        $places = $this->getMarkedPlaces();

        if ($this->supportsMultipleStates() && count($places) > 1) {
            return 'In Progress: ' . implode(', ', array_map('ucfirst', $places));
        }

        return ucfirst($this->getMarking());
    }
}

// Usage
$article = Article::find(1);

// Start review and testing in parallel
$article->applyTransition('start_review');
$article->applyTransition('start_testing');

// Check status
echo $article->getWorkflowStatus(); // "In Progress: Reviewing, Testing"

// Check if both are complete
if ($article->isInAllPlaces(['reviewed', 'tested'])) {
    $article->applyTransition('publish');
}
```

### Use Case 3: Dynamic UI Based on Metadata

```blade
@php
    $currentPlace = $document->getMarking();
    $placeColor = $document->getPlaceMetadata($currentPlace, 'color') ?? 'blue';
    $placeIcon = $document->getPlaceMetadata($currentPlace, 'icon');
    $placeDescription = $document->getPlaceMetadata($currentPlace, 'description');
@endphp

<div class="status-badge bg-{{ $placeColor }}-100 text-{{ $placeColor }}-800">
    @if($placeIcon)
        <i class="{{ $placeIcon }}"></i>
    @endif
    {{ ucfirst($currentPlace) }}
</div>

@if($placeDescription)
    <p class="text-sm text-gray-600">{{ $placeDescription }}</p>
@endif

<!-- Render transitions with metadata -->
@foreach($document->getTransitionsWithMetadata() as $transitionName => $metadata)
    @if(workflow_can($document, $transitionName))
        <button
            class="btn btn-{{ $metadata['button_color'] ?? 'primary' }}"
            wire:click="applyTransition('{{ $transitionName }}')"
        >
            @if(isset($metadata['icon']))
                <i class="{{ $metadata['icon'] }}"></i>
            @endif
            {{ ucwords(str_replace('_', ' ', $transitionName)) }}
        </button>
    @endif
@endforeach
```

### Use Case 4: Context-Aware Business Logic

```php
class OrderWorkflow
{
    public function canExpediteOrder(array $context = []): bool
    {
        // Only allow expedite for high priority orders
        if (!isset($context['priority'])) {
            return false;
        }

        if ($context['priority'] === 'urgent' && auth()->user()->hasRole('manager')) {
            return true;
        }

        return false;
    }

    public function processOrder(Order $order, string $transition, Request $request)
    {
        $context = [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'priority' => $request->input('priority', 'normal'),
            'notes' => $request->input('notes'),
            'department' => auth()->user()->department,
        ];

        // Apply transition with context
        $result = $order->applyTransitionWithContext($transition, $context);

        // Use context for notifications
        $this->sendNotification($order, $transition, $context);

        // Log business metrics
        $this->logMetrics($order, $transition, $context);

        return $result;
    }

    protected function sendNotification(Order $order, string $transition, array $context)
    {
        if ($context['priority'] === 'urgent') {
            // Send SMS for urgent orders
            SMS::send($order->customer->phone, "Order #{$order->id} is being {$transition}");
        } else {
            // Send email for normal orders
            Mail::to($order->customer)->send(new OrderStatusUpdate($order, $transition));
        }
    }
}
```

---

## Best Practices

### 1. Use State Machines for Linear Workflows

For sequential, single-state workflows (most common use case):

```php
'type' => 'state_machine',
'places' => ['draft', 'review', 'approved', 'published'],
```

### 2. Use Workflows for Parallel Processes

For processes that can be in multiple states simultaneously:

```php
'type' => 'workflow',
'places' => ['editing', 'reviewing', 'testing', 'translating'],
```

### 3. Always Validate Workflow Configuration

Add validation in your service provider or during workflow creation:

```php
try {
    $model->validateMarkingStoreType();
} catch (\LogicException $e) {
    logger()->error('Workflow configuration error', [
        'model' => get_class($model),
        'error' => $e->getMessage(),
    ]);
}
```

### 4. Use Meaningful Context Keys

Establish conventions for context data:

```php
// Good ✅
$context = [
    'user_id' => auth()->id(),
    'reason' => 'Quality check passed',
    'priority' => 'high',
    'department' => 'operations',
];

// Avoid ❌
$context = [
    'uid' => 1,
    'r' => 'OK',
    'p' => 'H',
];
```

### 5. Store Important Context Data

Use context for audit trail and compliance:

```php
$context = [
    'user_id' => auth()->id(),
    'user_name' => auth()->user()->name,
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'reason' => $request->input('reason'),
    'timestamp' => now()->toIso8601String(),
];

$document->applyTransition('approve', $context, true); // Enable audit logging
```

### 6. Use Metadata for UI Customization

Define metadata for consistent UI rendering:

```php
$place->update([
    'meta' => [
        'color' => 'blue',          // For badges
        'icon' => 'clock',          // For icons
        'description' => '...',     // For tooltips
        'allow_editing' => true,    // For permissions
    ],
]);
```

### 7. Leverage Context in Guards

Make guards context-aware for flexible validation:

```php
public function canApprove(array $context = []): bool
{
    // Check user role
    if (!auth()->user()->hasRole('approver')) {
        return false;
    }

    // Check context conditions
    if (isset($context['expedite']) && $context['expedite'] === true) {
        return auth()->user()->hasRole('manager');
    }

    return true;
}
```

### 8. Document Your Workflow Metadata

Keep documentation of your metadata schema:

```php
/**
 * Workflow Metadata Schema:
 * - department: string (editorial, operations, etc.)
 * - sla_hours: int (SLA in hours)
 * - notification_channels: array (email, sms, slack)
 *
 * Place Metadata Schema:
 * - color: string (gray, blue, green, red)
 * - icon: string (icon class name)
 * - description: string
 * - allow_editing: bool
 *
 * Transition Metadata Schema:
 * - requires_approval: bool
 * - notification: string (email, sms, none)
 * - button_color: string (primary, success, danger)
 * - roles: array (required roles)
 */
```

---

## See Also

- [Workflows Usage Guide](01-workflows.md)
- [Guards and Blockers](05-guards-and-blockers.md)
- [Audit Trail](04-audit-trail.md)
- [Blade Helpers](06-blade-helpers.md)
- [API Reference](../04-api/01-api-reference.md)
