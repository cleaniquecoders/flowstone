# Blade Template Helpers

Flowstone provides a comprehensive set of Blade template helpers that make it easy to work with workflows in your views. These helpers include custom directives, reusable components, and helper functions.

## Table of Contents

- [Blade Directives](#blade-directives)
- [Blade Components](#blade-components)
- [Helper Functions](#helper-functions)
- [Usage Examples](#usage-examples)
- [Best Practices](#best-practices)

---

## Blade Directives

Flowstone registers several custom Blade directives that simplify workflow checks in your views.

### @canTransition

Check if a transition can be applied to a model:

```blade
@canTransition($document, 'approve')
    <button wire:click="approve">Approve Document</button>
@endCanTransition
```

### @cannotTransition

Inverse of `@canTransition` - executes when transition cannot be applied:

```blade
@cannotTransition($document, 'approve')
    <p class="text-red-600">You cannot approve this document at this time.</p>
@endCannotTransition
```

### @workflowMarkedPlaces

Display the current marked places (states) for a workflow model:

```blade
<p>Current status: @workflowMarkedPlaces($document)</p>
<!-- Output: Current status: draft, pending -->
```

### @workflowHasMarkedPlace

Check if a model is in a specific place:

```blade
@workflowHasMarkedPlace($document, 'draft')
    <span class="badge badge-gray">Draft</span>
@endWorkflowHasMarkedPlace

@workflowHasMarkedPlace($document, 'published')
    <span class="badge badge-green">Published</span>
@endWorkflowHasMarkedPlace
```

---

## Blade Components

Flowstone provides four ready-to-use Blade components for common workflow UI patterns.

### x-flowstone::workflow-status

Display the current workflow status with styled badges:

```blade
<x-flowstone::workflow-status :model="$document" />

<!-- With custom options -->
<x-flowstone::workflow-status
    :model="$document"
    :show-label="true"
    badge-class="px-4 py-2 text-lg font-bold rounded-lg"
/>
```

**Props:**
- `model` (required) - The workflow model instance
- `show-label` (optional, default: `true`) - Show "Status:" label
- `badge-class` (optional) - Custom CSS classes for badges

**Output:**
```html
<div class="workflow-status inline-flex items-center gap-2">
    <span class="text-sm font-medium text-gray-700">Status:</span>
    <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
        Draft
    </span>
</div>
```

**Color Mapping:**
The component automatically applies colors based on common workflow states:
- `draft` - Gray
- `pending` - Yellow
- `in_progress` - Blue
- `under_review` - Purple
- `approved` - Green
- `rejected` - Red
- `completed` - Emerald
- And more...

### x-flowstone::workflow-transitions

Display available transitions with buttons:

```blade
<x-flowstone::workflow-transitions :model="$document" />

<!-- With custom styling -->
<x-flowstone::workflow-transitions
    :model="$document"
    :show-blockers="true"
    button-class="btn btn-primary"
    disabled-class="btn btn-disabled"
/>
```

**Props:**
- `model` (required) - The workflow model instance
- `show-blockers` (optional, default: `true`) - Show blocker messages for disabled transitions
- `button-class` (optional) - CSS classes for enabled transition buttons
- `disabled-class` (optional) - CSS classes for disabled transition buttons

**Example Output:**

```html
<div class="workflow-transitions space-y-2">
    <div class="flex flex-wrap gap-2">
        <!-- Enabled transition -->
        <button wire:click="applyTransition('submit')"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Submit
        </button>

        <!-- Disabled transition with blockers -->
        <button disabled
                class="px-4 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed"
                title="The marking does not enable the transition.">
            Approve
        </button>

        <!-- Blocker message -->
        <div class="w-full ml-2 text-sm text-red-600">
            <strong>Approve blocked:</strong>
            <ul class="list-disc list-inside">
                <li>The marking does not enable the transition.</li>
            </ul>
        </div>
    </div>
</div>
```

### x-flowstone::workflow-blockers

Display blocker messages for a specific transition:

```blade
<x-flowstone::workflow-blockers
    :model="$document"
    transition="approve"
/>

<!-- Without icon -->
<x-flowstone::workflow-blockers
    :model="$document"
    transition="approve"
    :show-icon="false"
/>
```

**Props:**
- `model` (required) - The workflow model instance
- `transition` (required) - The transition name to check
- `show-icon` (optional, default: `true`) - Show warning icon

**Example Output:**

```html
<div class="workflow-blockers">
    <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex items-start">
            <div class="shrink-0">
                <svg class="h-5 w-5 text-red-400">...</svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-red-800">
                    Cannot apply "Approve" transition
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>User does not have required role: ROLE_APPROVER</li>
                        <li>Missing permission: approve-documents</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
```

### x-flowstone::workflow-timeline

Display workflow history as a timeline:

```blade
<x-flowstone::workflow-timeline :model="$document" />

<!-- With custom options -->
<x-flowstone::workflow-timeline
    :model="$document"
    :limit="10"
    :show-user="true"
    :show-context="true"
/>
```

**Props:**
- `model` (required) - The workflow model instance
- `limit` (optional, default: `null`) - Maximum number of logs to display
- `show-user` (optional, default: `true`) - Show user information
- `show-context` (optional, default: `false`) - Show transition context data

**Example Output:**

```html
<div class="workflow-timeline">
    <div class="flow-root">
        <ul role="list" class="-mb-8">
            <li>
                <div class="relative pb-8">
                    <!-- Connection line -->
                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"></span>

                    <div class="relative flex space-x-3">
                        <!-- Status icon -->
                        <div>
                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                <svg class="h-5 w-5 text-white">...</svg>
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">Approve</span> transition
                                    from <span class="font-medium">Review</span>
                                    to <span class="font-medium">Published</span>
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    by John Doe
                                </p>
                            </div>
                            <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                <time>2 hours ago</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
```

---

## Helper Functions

Flowstone provides several global helper functions for working with workflows in PHP code and Blade templates.

### workflow_can()

Check if a transition can be applied:

```php
workflow_can($document, 'submit'); // Returns: bool
```

```blade
@if(workflow_can($document, 'approve'))
    <button>Approve</button>
@endif
```

### workflow_transitions()

Get all enabled transitions:

```php
$transitions = workflow_transitions($document); // Returns: array
```

```blade
@foreach(workflow_transitions($document) as $transition)
    <button wire:click="apply('{{ $transition->getName() }}')">
        {{ ucwords(str_replace('_', ' ', $transition->getName())) }}
    </button>
@endforeach
```

### workflow_transition()

Get a specific transition by name:

```php
$transition = workflow_transition($document, 'approve'); // Returns: ?object
```

```blade
@php
    $approveTransition = workflow_transition($document, 'approve');
@endphp

@if($approveTransition)
    <!-- Transition exists -->
@endif
```

### workflow_marked_places()

Get current marked places (states):

```php
$places = workflow_marked_places($document); // Returns: array
// Example: ['draft' => 1, 'pending' => 1]
```

```blade
@foreach(workflow_marked_places($document) as $place => $value)
    <span class="badge">{{ ucwords(str_replace('_', ' ', $place)) }}</span>
@endforeach
```

### workflow_has_marked_place()

Check if model has a specific place:

```php
workflow_has_marked_place($document, 'draft'); // Returns: bool
```

```blade
@if(workflow_has_marked_place($document, 'published'))
    <span class="text-green-600">This document is published!</span>
@endif
```

### workflow_transition_blockers()

Get blockers for a transition:

```php
$blockers = workflow_transition_blockers($document, 'approve'); // Returns: array<TransitionBlocker>
```

```blade
@php
    $blockers = workflow_transition_blockers($document, 'approve');
@endphp

@if(count($blockers) > 0)
    <div class="alert alert-danger">
        <strong>Cannot approve:</strong>
        <ul>
            @foreach($blockers as $blocker)
                <li>{{ $blocker->getMessage() }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### workflow_metadata()

Get metadata for workflow, place, or transition:

```php
// Get workflow metadata
$priority = workflow_metadata($document, 'priority', 'workflow');

// Get place metadata
$color = workflow_metadata($document, 'color', 'place', 'draft');

// Get transition metadata
$icon = workflow_metadata($document, 'icon', 'transition', 'approve');
```

```blade
@php
    $requiredRole = workflow_metadata($document, 'required_role', 'transition', 'approve');
@endphp

@if($requiredRole)
    <p>Required role: {{ $requiredRole }}</p>
@endif
```

---

## Usage Examples

### Complete Document Workflow View

```blade
<div class="document-view">
    <div class="flex items-center justify-between mb-4">
        <h1>{{ $document->title }}</h1>
        <x-flowstone::workflow-status :model="$document" />
    </div>

    <!-- Show current state information -->
    <div class="bg-gray-50 p-4 rounded-lg mb-4">
        <p class="text-sm text-gray-600">
            Current status: @workflowMarkedPlaces($document)
        </p>
    </div>

    <!-- Show available actions -->
    <div class="mb-4">
        <h2 class="text-lg font-semibold mb-2">Available Actions</h2>
        <x-flowstone::workflow-transitions :model="$document" />
    </div>

    <!-- Show timeline -->
    <div class="mt-6">
        <h2 class="text-lg font-semibold mb-2">History</h2>
        <x-flowstone::workflow-timeline
            :model="$document"
            :limit="10"
            :show-user="true"
        />
    </div>
</div>
```

### Conditional Button Display

```blade
<div class="document-actions">
    @canTransition($document, 'submit')
        <button wire:click="submit" class="btn btn-primary">
            Submit for Review
        </button>
    @endCanTransition

    @canTransition($document, 'approve')
        <button wire:click="approve" class="btn btn-success">
            Approve
        </button>
    @endCanTransition

    @canTransition($document, 'reject')
        <button wire:click="reject" class="btn btn-danger">
            Reject
        </button>
    @endCanTransition

    @cannotTransition($document, 'approve')
        <x-flowstone::workflow-blockers
            :model="$document"
            transition="approve"
        />
    @endCannotTransition
</div>
```

### Dynamic Transition Buttons

```blade
<div class="transition-buttons flex flex-wrap gap-2">
    @foreach(workflow_transitions($document) as $transition)
        @php
            $name = $transition->getName();
            $canApply = workflow_can($document, $name);
            $label = ucwords(str_replace('_', ' ', $name));
        @endphp

        @if($canApply)
            <button
                wire:click="applyTransition('{{ $name }}')"
                class="btn btn-primary"
            >
                {{ $label }}
            </button>
        @else
            <button
                disabled
                class="btn btn-disabled"
                title="Cannot apply {{ $label }}"
            >
                {{ $label }}
            </button>
        @endif
    @endforeach
</div>
```

### Status Badge in List View

```blade
<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($documents as $document)
            <tr>
                <td>{{ $document->title }}</td>
                <td>
                    <x-flowstone::workflow-status
                        :model="$document"
                        :show-label="false"
                    />
                </td>
                <td>
                    @canTransition($document, 'approve')
                        <a href="{{ route('documents.approve', $document) }}">
                            Approve
                        </a>
                    @endCanTransition
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

### Custom Transition Form

```blade
<form wire:submit.prevent="submitForReview">
    <div class="space-y-4">
        <div>
            <label>Comments</label>
            <textarea wire:model="comments" rows="4"></textarea>
        </div>

        @canTransition($document, 'submit')
            <button type="submit" class="btn btn-primary">
                Submit for Review
            </button>
        @endCanTransition

        @cannotTransition($document, 'submit')
            <div class="alert alert-warning">
                <p>Cannot submit this document:</p>
                <x-flowstone::workflow-blockers
                    :model="$document"
                    transition="submit"
                    :show-icon="false"
                />
            </div>
        @endCannotTransition
    </div>
</form>
```

---

## Best Practices

### 1. Use Components for Consistency

Prefer using Blade components over manually building UI:

```blade
<!-- Good ✅ -->
<x-flowstone::workflow-status :model="$document" />

<!-- Avoid ❌ -->
<span class="badge">{{ $document->marking }}</span>
```

### 2. Check Transitions Before Showing Actions

Always check if a transition is available before displaying action buttons:

```blade
<!-- Good ✅ -->
@canTransition($document, 'approve')
    <button wire:click="approve">Approve</button>
@endCanTransition

<!-- Avoid ❌ -->
<button wire:click="approve">Approve</button>
```

### 3. Show Blocker Messages for Better UX

When transitions are blocked, inform users why:

```blade
@cannotTransition($document, 'approve')
    <x-flowstone::workflow-blockers
        :model="$document"
        transition="approve"
    />
@endCannotTransition
```

### 4. Customize Component Styling

Override default styles to match your application's design:

```blade
<x-flowstone::workflow-status
    :model="$document"
    badge-class="px-4 py-2 text-base font-bold uppercase rounded"
/>
```

### 5. Use Helper Functions in Controllers

Helper functions work in controllers too, not just views:

```php
public function approve(Document $document)
{
    if (!workflow_can($document, 'approve')) {
        $blockers = workflow_transition_blockers($document, 'approve');
        return back()->withErrors([
            'workflow' => array_map(fn($b) => $b->getMessage(), $blockers)
        ]);
    }

    $document->applyTransition('approve');

    return redirect()->route('documents.show', $document);
}
```

### 6. Leverage Metadata for Dynamic UI

Use workflow metadata to drive your UI:

```blade
@php
    $buttonColor = workflow_metadata($document, 'button_color', 'transition', 'approve') ?? 'blue';
    $icon = workflow_metadata($document, 'icon', 'transition', 'approve');
@endphp

<button class="bg-{{ $buttonColor }}-600 text-white px-4 py-2 rounded">
    @if($icon)
        <i class="{{ $icon }}"></i>
    @endif
    Approve
</button>
```

### 7. Combine Multiple Helpers

Combine helpers for powerful conditional logic:

```blade
@if(workflow_has_marked_place($document, 'published') && workflow_can($document, 'archive'))
    <button wire:click="archive" class="btn btn-secondary">
        Archive Published Document
    </button>
@endif
```

---

## See Also

- [Workflows Usage Guide](01-workflows.md)
- [Guards and Blockers](05-guards-and-blockers.md)
- [Audit Trail](04-audit-trail.md)
- [API Reference](../04-api/01-api-reference.md)
