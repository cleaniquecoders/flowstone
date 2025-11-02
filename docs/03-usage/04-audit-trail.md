# Audit Trail

Flowstone includes a comprehensive audit trail system that tracks all workflow state transitions. This feature is essential for compliance, debugging, and understanding workflow behavior over time.

## Overview

The audit trail system automatically logs:
- Every workflow transition
- The user who performed the transition
- Source and destination states
- Transition name
- Timestamp
- Additional context and metadata
- IP address and user agent

## Table of Contents

- [Enabling Audit Trail](#enabling-audit-trail)
- [Viewing Audit Logs](#viewing-audit-logs)
- [Programmatic Access](#programmatic-access)
- [Filtering and Searching](#filtering-and-searching)
- [Database Schema](#database-schema)
- [Best Practices](#best-practices)

## Enabling Audit Trail

### Per Workflow

Enable audit trail for a specific workflow:

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

$workflow = Workflow::find(1);
$workflow->update(['audit_trail_enabled' => true]);
```

### During Workflow Creation

```php
$workflow = Workflow::create([
    'name' => 'document-approval',
    'type' => 'state_machine',
    'initial_marking' => 'draft',
    'audit_trail_enabled' => true, // Enable audit trail
]);
```

### Via Livewire UI

1. Navigate to the workflow details page
2. Toggle the "Enable Audit Trail" option
3. Save the workflow

## Using Audit Trail

### Applying Transitions with Logging

Use the `applyTransition()` method instead of directly calling Symfony's `apply()`:

```php
use App\Models\Document;

$document = Document::find(1);

// Automatically logs if audit trail is enabled for this workflow
$marking = $document->applyTransition('submit_for_review');

// Pass additional context
$marking = $document->applyTransition('approve', [
    'comments' => 'Looks good!',
    'priority' => 'high',
]);

// Override audit trail setting (force logging)
$marking = $document->applyTransition('reject', [], true);

// Override audit trail setting (disable logging)
$marking = $document->applyTransition('to_draft', [], false);
```

### Example in a Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function approve(Request $request, Document $document)
    {
        $request->validate([
            'comments' => 'required|string',
        ]);

        try {
            $document->applyTransition('approve', [
                'comments' => $request->comments,
                'approved_by' => auth()->user()->name,
            ]);

            return redirect()
                ->route('documents.show', $document)
                ->with('success', 'Document approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

## Viewing Audit Logs

### Livewire Component

Add the audit log viewer to any page:

```blade
<livewire:flowstone::audit-log-viewer />
```

#### Filter by Workflow

```blade
<livewire:flowstone::audit-log-viewer :workflowId="$workflow->id" />
```

#### Filter by Subject (Model Instance)

```blade
<livewire:flowstone::audit-log-viewer
    :subjectType="get_class($document)"
    :subjectId="$document->id"
/>
```

#### Filter by User

```blade
<livewire:flowstone::audit-log-viewer :userId="auth()->id()" />
```

### In Workflow Details Page

The audit log viewer is automatically included in the workflow details page when audit trail is enabled.

## Programmatic Access

### Get Audit Trail for a Model

```php
// Get all audit logs for a model
$auditLogs = $document->getAuditTrail();

// Get limited number of logs
$recentLogs = $document->getAuditTrail(10);

// Or use the helper method
$recentLogs = $document->recentAuditLogs(10);

// Check if model has audit logs
if ($document->hasAuditLogs()) {
    // Show audit trail...
}
```

### Relationship Access

```php
// Use Eloquent relationship
$logs = $document->auditLogs()
    ->where('transition', 'approve')
    ->get();

// Count transitions
$approvalCount = $document->auditLogs()
    ->where('transition', 'approve')
    ->count();
```

### Querying Audit Logs

```php
use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;

// Get all logs for a workflow
$logs = WorkflowAuditLog::forWorkflow($workflowId)->get();

// Get logs for a specific subject
$logs = WorkflowAuditLog::forSubject(Document::class, $documentId)->get();

// Get logs by user
$logs = WorkflowAuditLog::byUser($userId)->get();

// Get logs by transition
$logs = WorkflowAuditLog::byTransition('approve')->get();

// Get logs involving a specific place
$logs = WorkflowAuditLog::byPlace('under_review')->get();

// Get logs in date range
$logs = WorkflowAuditLog::inDateRange('2025-01-01', '2025-12-31')->get();

// Combine filters
$logs = WorkflowAuditLog::query()
    ->forWorkflow($workflowId)
    ->byUser($userId)
    ->byTransition('approve')
    ->latest()
    ->get();
```

## Filtering and Searching

The audit log viewer component supports real-time filtering:

- **Workflow ID**: Filter by specific workflow
- **User ID**: Filter by user who performed transitions
- **Transition**: Filter by transition name
- **Place**: Filter by source or destination place
- **Date Range**: Filter by start and end dates
- **Per Page**: Control pagination (10, 20, 50, 100)

### Sorting

Click on column headers to sort:
- Timestamp
- Workflow
- Transition
- User

## Database Schema

The `workflow_audit_logs` table structure:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `uuid` | string | Unique identifier |
| `workflow_id` | bigint | Reference to workflow (nullable) |
| `subject_type` | string | Model class name |
| `subject_id` | bigint | Model ID |
| `from_place` | string | Source state (nullable for initial) |
| `to_place` | string | Destination state |
| `transition` | string | Transition name |
| `user_id` | bigint | User who performed transition |
| `context` | json | Additional context data |
| `metadata` | json | System metadata (IP, user agent) |
| `created_at` | timestamp | When transition occurred |

### Indexes

Optimized indexes for common queries:
- `(subject_type, subject_id)` - Find logs for specific model
- `(workflow_id, created_at)` - Workflow timeline
- `(subject_type, subject_id, created_at)` - Model timeline
- `(user_id, created_at)` - User activity

## Best Practices

### 1. Enable Audit Trail for Critical Workflows

```php
// Enable for sensitive workflows
$approvalWorkflow->update(['audit_trail_enabled' => true]);
$financialWorkflow->update(['audit_trail_enabled' => true]);
```

### 2. Use Context for Business Information

```php
$document->applyTransition('approve', [
    'reviewer' => auth()->user()->name,
    'department' => 'Legal',
    'priority' => 'high',
    'notes' => 'All requirements met',
]);
```

### 3. Regular Cleanup

Create a scheduled job to archive old logs:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Archive logs older than 1 year
    $schedule->call(function () {
        WorkflowAuditLog::where('created_at', '<', now()->subYear())
            ->delete();
    })->monthly();
}
```

### 4. Export for Compliance

```php
use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;

// Export to CSV
$logs = WorkflowAuditLog::forWorkflow($workflowId)
    ->inDateRange($startDate, $endDate)
    ->get();

// Process and export...
```

### 5. Monitor Transition Patterns

```php
// Analyze workflow efficiency
$averageTimeInReview = WorkflowAuditLog::query()
    ->where('to_place', 'under_review')
    ->get()
    ->map(function ($log) {
        $nextLog = WorkflowAuditLog::query()
            ->where('subject_type', $log->subject_type)
            ->where('subject_id', $log->subject_id)
            ->where('from_place', 'under_review')
            ->where('created_at', '>', $log->created_at)
            ->first();

        return $nextLog
            ? $nextLog->created_at->diffInHours($log->created_at)
            : null;
    })
    ->filter()
    ->average();
```

## Advanced Usage

### Custom Metadata

You can extend the audit log with custom metadata in your model:

```php
protected function logWorkflowTransition(string $transitionName, string $fromPlace, array $toPlaces, array $context = []): void
{
    $toPlace = array_key_first($toPlaces) ?? null;

    if (! $toPlace) {
        return;
    }

    WorkflowAuditLog::create([
        'workflow_id' => $this->getWorkflowId(),
        'subject_type' => get_class($this),
        'subject_id' => $this->getKey(),
        'from_place' => $fromPlace,
        'to_place' => $toPlace,
        'transition' => $transitionName,
        'user_id' => auth()->id(),
        'context' => $context,
        'metadata' => array_merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $this->getCustomAuditMetadata()), // Add custom metadata
        'created_at' => now(),
    ]);
}

protected function getCustomAuditMetadata(): array
{
    return [
        'environment' => app()->environment(),
        'version' => config('app.version'),
        // Add any custom fields
    ];
}
```

### Event Listeners

Listen for audit log creation:

```php
use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;

WorkflowAuditLog::created(function ($log) {
    // Send notification
    // Update analytics
    // Trigger webhooks
});
```

## Troubleshooting

### Audit Logs Not Being Created

1. **Check if audit trail is enabled**:
   ```php
   $workflow->audit_trail_enabled; // Should be true
   ```

2. **Verify you're using `applyTransition()`**:
   ```php
   // ✅ This logs
   $model->applyTransition('approve');

   // ❌ This doesn't log
   $workflow->apply($model, 'approve');
   ```

3. **Check database migration**:
   ```bash
   php artisan migrate:status | grep workflow_audit
   ```

### Performance Issues

If you have millions of audit logs:

1. **Partition the table** by date
2. **Archive old logs** to separate storage
3. **Add composite indexes** for your specific queries
4. **Consider using a time-series database** for very high volume

## See Also

- [Workflows Guide](01-workflows.md)
- [Workflow Details](02-workflow-details.md)
- [API Reference](../04-api/01-api-reference.md)
