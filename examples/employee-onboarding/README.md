# Employee Onboarding Process

HR workflow for new employee integration with multi-department coordination and task tracking.

## Workflow: Applied → Hired → Onboarding → Training → Active

### Employee Model

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'status',
        'department_id', 'position', 'start_date', 'hr_manager_id',
        'it_setup_completed', 'documentation_completed', 'training_completed',
        'probation_end_date', 'active_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'probation_end_date' => 'date',
        'active_date' => 'date',
        'it_setup_completed' => 'boolean',
        'documentation_completed' => 'boolean',
        'training_completed' => 'boolean',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'employee-onboarding');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    public function getMarking(): string
    {
        return $this->status ?? 'applied';
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;

        if ($marking === 'active' && !$this->active_date) {
            $this->active_date = now();
        }
    }

    // Check if ready for next phase
    public function readyForTraining(): bool
    {
        return $this->it_setup_completed &&
               $this->documentation_completed;
    }

    public function readyToActivate(): bool
    {
        return $this->training_completed &&
               $this->probation_end_date <= now();
    }

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function hrManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_manager_id');
    }
}
```

### Key Features

- **Multi-department coordination** (HR, IT, Training)
- **Checklist-based progression** with completion tracking
- **Probation period** management
- **Automated notifications** for task assignments
- **Department-specific workflows**
- **Compliance tracking** for required documentation

### Onboarding Tasks

```php
// IT Setup Tasks
$employee->it_setup_completed = $this->checkITTasks([
    'laptop_assigned',
    'accounts_created',
    'security_training_completed',
    'vpn_access_granted'
]);

// Documentation Tasks
$employee->documentation_completed = $this->checkDocumentationTasks([
    'contract_signed',
    'tax_forms_completed',
    'emergency_contacts_provided',
    'policy_acknowledgment_signed'
]);

// Training Tasks
$employee->training_completed = $this->checkTrainingTasks([
    'orientation_completed',
    'role_specific_training',
    'compliance_training',
    'mentor_assignment'
]);
```

### Usage

```php
// Hire new employee
$employee = Employee::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@company.com',
    'department_id' => 1,
    'position' => 'Software Developer',
    'start_date' => now()->addWeek(),
]);

// Progress through onboarding
$workflow = $employee->getWorkflow();

// HR processes hire
$workflow->apply($employee, 'process_hire');

// Begin onboarding on start date
if ($employee->start_date <= now()) {
    $workflow->apply($employee, 'start_onboarding');
}

// Complete onboarding phases
if ($employee->readyForTraining()) {
    $workflow->apply($employee, 'begin_training');
}

if ($employee->readyToActivate()) {
    $workflow->apply($employee, 'activate');
}
```

### Workflow States

- **Applied**: Application received and reviewed
- **Hired**: Offer accepted, awaiting start date
- **Onboarding**: IT setup and documentation in progress
- **Training**: Role-specific and compliance training
- **Active**: Fully onboarded and productive
- **Terminated**: Employment ended (during probation)

This workflow ensures consistent onboarding experiences and helps track completion of required tasks across departments.
