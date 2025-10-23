# Bug Tracking System

Issue management workflow for development teams with priority handling and resolution tracking.

## Workflow: New → Assigned → In Progress → Testing → Resolved/Closed

### Bug Model

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Bug extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'title', 'description', 'status', 'priority',
        'reporter_id', 'assignee_id', 'resolver_id',
        'steps_to_reproduce', 'expected_behavior', 'actual_behavior',
        'browser', 'os', 'version', 'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'steps_to_reproduce' => 'array',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'bug-tracking');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    public function getMarking(): string
    {
        return $this->status ?? 'new';
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;

        if ($marking === 'resolved') {
            $this->resolved_at = now();
            $this->resolver_id = auth()->id();
        }
    }

    // Relationships
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    // Scopes
    public function scopeOpenBugs($query)
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assignee_id', $userId);
    }
}
```

### Key Features

- **Priority-based assignment** (Critical/High/Medium/Low)
- **Developer assignment** and tracking
- **Testing phase** before resolution
- **Reporter feedback** integration
- **Resolution tracking** with timestamps
- **Environment details** (browser, OS, version)

### Usage

```php
// Report a bug
$bug = Bug::create([
    'title' => 'Login button not working',
    'description' => 'Users cannot log in...',
    'priority' => 'high',
    'reporter_id' => auth()->id(),
]);

// Assign to developer
$bug->assignee_id = $developerId;
$workflow = $bug->getWorkflow();
$workflow->apply($bug, 'assign');

// Progress through workflow
$workflow->apply($bug, 'start_work');     // new → in_progress
$workflow->apply($bug, 'submit_fix');     // in_progress → testing
$workflow->apply($bug, 'verify_fix');     // testing → resolved
```

### Workflow States

- **New**: Bug reported, awaiting triage
- **Assigned**: Bug assigned to developer
- **In Progress**: Developer working on fix
- **Testing**: Fix implemented, needs verification
- **Resolved**: Fix verified and working
- **Closed**: Bug permanently closed
- **Reopened**: Previously resolved bug reopened
