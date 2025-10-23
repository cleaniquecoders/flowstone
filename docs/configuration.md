# Configuration

This guide covers all configuration options available in Flowstone to customize workflows for your application needs.

## Configuration File

The main configuration file is located at `config/flowstone.php`. If you haven't published it yet:

```bash
php artisan vendor:publish --tag="flowstone-config"
```

## Default Configuration

### Basic Structure

```php
return [
    'default' => [
        'type' => 'state_machine',
        'supports' => [
            Workflow::class,
        ],
        'marking_store' => [
            'type' => 'method',
            'property' => 'status',
        ],
        'initial_marking' => Status::DRAFT->value,
        'places' => null,
        'transitions' => null,
    ],
    'custom' => [
        // Custom workflow configurations
    ],
    'auto_discovery' => [
        'enabled' => false,
        'paths' => [app_path('Models')],
        'trait' => 'CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow',
    ],
];
```

## Configuration Options

### Workflow Type

Choose between two workflow types:

```php
'type' => 'state_machine', // or 'workflow'
```

**State Machine**:

- Single marking at a time
- Exclusive states
- Most common use case
- Example: Document approval (draft → review → approved)

**Workflow**:

- Multiple markings simultaneously
- Parallel processes
- Complex scenarios
- Example: Multi-step form (step1 + step2 + step3)

### Supported Models

Define which models can use workflows:

```php
'supports' => [
    App\Models\Document::class,
    App\Models\Order::class,
    App\Models\Task::class,
],
```

### Marking Store Configuration

Configure how workflow state is stored:

```php
'marking_store' => [
    'type' => 'method',        // or 'property'
    'property' => 'status',    // database column name
],
```

**Method Type** (Recommended):

- Uses getter/setter methods
- More flexible
- Better validation

**Property Type**:

- Direct property access
- Simpler implementation
- Less overhead

### Initial Status

Set the default status for new workflow instances:

```php
'initial_marking' => Status::DRAFT->value,
```

Available status options:

- `DRAFT` - Initial preparation
- `PENDING` - Waiting to start
- `IN_PROGRESS` - Currently executing
- `UNDER_REVIEW` - Being reviewed
- `APPROVED` - Approved to proceed
- `REJECTED` - Rejected/blocked
- `ON_HOLD` - Temporarily suspended
- `CANCELLED` - Permanently cancelled
- `COMPLETED` - Successfully finished
- `FAILED` - Failed execution
- `PAUSED` - Temporarily paused
- `ARCHIVED` - Archived/historical

### Places Configuration

Define workflow places (states):

```php
// Auto-generate from Status enum
'places' => null,

// Or define custom places
'places' => [
    'draft' => ['label' => 'Draft'],
    'review' => ['label' => 'Under Review'],
    'approved' => ['label' => 'Approved'],
    'rejected' => ['label' => 'Rejected'],
],
```

### Transitions Configuration

Configure allowed transitions between states:

```php
// Use default transitions
'transitions' => null,

// Or define custom transitions
'transitions' => [
    'submit' => [
        'from' => ['draft'],
        'to' => 'review',
        'metadata' => [
            'label' => 'Submit for Review',
            'roles' => ['author', 'editor'],
        ],
    ],
    'approve' => [
        'from' => ['review'],
        'to' => 'approved',
        'metadata' => [
            'label' => 'Approve',
            'roles' => ['manager', 'admin'],
        ],
    ],
    'reject' => [
        'from' => ['review'],
        'to' => 'rejected',
        'metadata' => [
            'label' => 'Reject',
            'roles' => ['manager', 'admin'],
        ],
    ],
],
```

## Custom Workflows

Define multiple workflow configurations for different use cases:

### Example: Document Approval

```php
'custom' => [
    'document_approval' => [
        'type' => 'state_machine',
        'supports' => [App\Models\Document::class],
        'marking_store' => [
            'type' => 'method',
            'property' => 'approval_status',
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
            ],
            'start_review' => [
                'from' => ['submitted'],
                'to' => 'under_review',
            ],
            'approve' => [
                'from' => ['under_review'],
                'to' => 'approved',
            ],
            'reject' => [
                'from' => ['under_review'],
                'to' => 'rejected',
            ],
        ],
    ],
],
```

### Example: Order Processing

```php
'order_processing' => [
    'type' => 'state_machine',
    'supports' => [App\Models\Order::class],
    'marking_store' => [
        'type' => 'method',
        'property' => 'order_status',
    ],
    'initial_marking' => 'pending',
    'places' => [
        'pending' => null,
        'confirmed' => null,
        'processing' => null,
        'shipped' => null,
        'delivered' => null,
        'cancelled' => null,
    ],
    'transitions' => [
        'confirm' => [
            'from' => ['pending'],
            'to' => 'confirmed',
        ],
        'start_processing' => [
            'from' => ['confirmed'],
            'to' => 'processing',
        ],
        'ship' => [
            'from' => ['processing'],
            'to' => 'shipped',
        ],
        'deliver' => [
            'from' => ['shipped'],
            'to' => 'delivered',
        ],
        'cancel' => [
            'from' => ['pending', 'confirmed'],
            'to' => 'cancelled',
        ],
    ],
],
```

## Role-Based Permissions

Configure role-based access control for transitions:

```php
'transitions' => [
    'approve_manager' => [
        'from' => ['review'],
        'to' => 'approved',
        'metadata' => [
            'roles' => ['manager'],
            'permissions' => ['approve_documents'],
        ],
    ],
    'approve_admin' => [
        'from' => ['review'],
        'to' => 'approved',
        'metadata' => [
            'roles' => ['admin'],
            'permissions' => ['approve_any_document'],
        ],
    ],
],
```

## Auto-Discovery Settings

Automatically discover models with workflow capabilities:

```php
'auto_discovery' => [
    'enabled' => true,
    'paths' => [
        app_path('Models'),
        app_path('Domain/Models'),
    ],
    'trait' => 'CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow',
],
```

## Environment-Based Configuration

Use environment variables for different configurations:

```php
// config/flowstone.php
'default' => [
    'type' => env('FLOWSTONE_DEFAULT_TYPE', 'state_machine'),
    'initial_marking' => env('FLOWSTONE_INITIAL_STATUS', 'draft'),
],
```

```bash
# .env
FLOWSTONE_DEFAULT_TYPE=state_machine
FLOWSTONE_INITIAL_STATUS=draft
```

## Database vs Code Configuration

### Code Configuration (config/flowstone.php)

**Pros**:

- Version controlled
- Type-safe
- IDE support
- Faster (no database queries)

**Cons**:

- Requires deployment for changes
- Less flexible
- No runtime updates

### Database Configuration

**Pros**:

- Runtime configuration changes
- Non-technical users can modify
- Dynamic workflows
- Multiple workflow versions

**Cons**:

- Requires database queries
- Less type safety
- More complex setup

## Performance Considerations

### Caching

Flowstone automatically caches workflow configurations:

```php
// Cache duration (in minutes)
'cache' => [
    'duration' => env('FLOWSTONE_CACHE_DURATION', 60),
    'prefix' => 'flowstone',
],
```

### Database Indexing

Ensure proper indexing for workflow queries:

```php
// Migration
Schema::table('your_table', function (Blueprint $table) {
    $table->index(['status', 'created_at']);
    $table->index(['workflow_type', 'status']);
});
```

## Best Practices

### 1. Naming Conventions

- Use snake_case for transition names: `submit_for_review`
- Use lowercase for place names: `under_review`
- Use descriptive names: `approve_with_conditions` vs `approve2`

### 2. Metadata Usage

Store additional information in metadata:

```php
'metadata' => [
    'label' => 'Human readable label',
    'description' => 'Detailed description',
    'roles' => ['required', 'roles'],
    'permissions' => ['required.permissions'],
    'conditions' => ['custom_condition_check'],
    'notifications' => ['email', 'slack'],
]
```

### 3. Validation

Validate workflow configurations:

```php
public function boot()
{
    Workflow::saving(function ($workflow) {
        // Validate configuration
        $this->validateWorkflowConfig($workflow->config);
    });
}
```

## Next Steps

- Learn about [Database Workflows](database-workflows.md) for dynamic configurations
- Explore [Usage Guide](usage-guide.md) for implementation details
- Check [Examples](examples.md) for real-world patterns
- Review [API Reference](api-reference.md) for method documentation
