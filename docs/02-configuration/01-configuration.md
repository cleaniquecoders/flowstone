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

### Supported Models (Symfony Compatibility)

> **⚠️ Important**: While Flowstone supports Symfony's `supports` configuration for compatibility, we **strongly recommend using the trait-based approach** instead. See the section below for details.

The `supports` configuration defines which model classes can use a specific workflow:

```php
'supports' => [
    App\Models\Document::class,
    App\Models\Order::class,
    App\Models\Task::class,
],
```

**However, this is NOT the recommended approach for Laravel applications!** Continue reading to understand why.

## Model Integration: Trait vs Supports Configuration

Flowstone provides two ways to integrate workflows with your models. Understanding the difference is crucial for a better developer experience.

### ❌ Symfony's Way: `supports` Configuration

In Symfony, you configure which classes can use a workflow in the config file:

```php
// config/flowstone.php
'custom' => [
    'document_approval' => [
        'type' => 'state_machine',
        'supports' => [
            App\Models\Document::class,
        ],
        'places' => [...],
        'transitions' => [...],
    ],
],
```

**Limitations of this approach:**

- ❌ Models must be hardcoded in config files
- ❌ No IDE autocomplete for workflow methods
- ❌ No type safety or type hints
- ❌ Less flexible - can't dynamically switch workflows
- ❌ Requires config cache clear when adding new models
- ❌ No method discovery in your IDE
- ❌ More verbose and scattered configuration

### ✅ Laravel's Way: `InteractsWithWorkflow` Trait (Recommended)

Flowstone uses a trait-based approach that's more idiomatic for Laravel:

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = ['title', 'content', 'status', 'workflow_type'];

    // Define which workflow this model uses
    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->workflow_type ?? 'document-approval'
        );
    }

    // Define the field name that stores the workflow type
    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    // Get current workflow state
    public function getMarking(): string
    {
        return $this->status ?? 'draft';
    }

    // Set workflow state
    public function setMarking(string $marking): void
    {
        $this->status = $marking;
    }
}
```

**Advantages of the trait approach:**

- ✅ **Full IDE support** - Autocomplete, type hints, and method discovery
- ✅ **Type safety** - Enforced by the `WorkflowContract` interface
- ✅ **More flexible** - Models can dynamically choose workflows at runtime
- ✅ **Laravel-native** - Uses traits like Laravel's native features
- ✅ **Better DX** - All workflow methods available directly on model
- ✅ **Self-documenting** - Just look at the model to see it has workflows
- ✅ **No config management** - No need to maintain class lists
- ✅ **Easier testing** - Mock workflow behavior directly on model
- ✅ **Runtime flexibility** - Switch workflows based on conditions

### Comparison Example

**Symfony's `supports` approach:**

```php
// ❌ Config file - no autocomplete, no type safety
'supports' => [App\Models\Document::class],

// In your code - no IDE help
$document = new Document();
// How do I know what methods are available? 🤔
// IDE shows no workflow methods!
```

**Flowstone's trait approach:**

```php
// ✅ In your model
class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;
}

// In your code - full IDE support
$document = new Document();
$document->getWorkflow();           // ✅ Autocomplete works!
$document->getEnabledTransitions(); // ✅ Type hints work!
$document->canApplyTransition('approve'); // ✅ Parameter hints work!
// IDE shows all 50+ workflow methods! 🎉
```

### Dynamic Workflow Selection

The trait approach allows powerful runtime workflow selection:

```php
class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Choose workflow based on document type
                return match ($this->type) {
                    'invoice' => 'invoice-approval',
                    'contract' => 'contract-review',
                    'report' => 'report-publishing',
                    default => 'document-approval',
                };
            }
        );
    }
}

// Same model, different workflows!
$invoice = Document::create(['type' => 'invoice']);
$invoice->getWorkflow(); // Uses 'invoice-approval' workflow

$contract = Document::create(['type' => 'contract']);
$contract->getWorkflow(); // Uses 'contract-review' workflow
```

This flexibility is impossible with the `supports` configuration approach!

### When to Use `supports` Configuration

The `supports` configuration is primarily for:

1. **Symfony compatibility** - If migrating from Symfony
2. **Legacy code** - If you have existing Symfony workflows
3. **Third-party packages** - That expect Symfony's configuration

**For new Laravel projects, always use the trait approach!**

### Migration from `supports` to Trait

If you have existing code using `supports`, here's how to migrate:

**Before (Symfony style):**

```php
// config/flowstone.php
'document_approval' => [
    'supports' => [App\Models\Document::class],
    // ... rest of config
],

// Model
class Document extends Model
{
    // No workflow integration
}
```

**After (Laravel style):**

```php
// config/flowstone.php
'document_approval' => [
    // Remove 'supports' - not needed!
    // ... rest of config
],

// Model
class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'document-approval');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    public function getMarking(): string
    {
        return $this->status ?? 'draft';
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;
    }
}
```

### Summary: Best Practices

| Aspect | Symfony's `supports` | Flowstone's Trait | Recommendation |
|--------|---------------------|-------------------|----------------|
| **IDE Support** | ❌ None | ✅ Full autocomplete | Use trait |
| **Type Safety** | ❌ Runtime only | ✅ Compile-time | Use trait |
| **Flexibility** | ❌ Static config | ✅ Dynamic runtime | Use trait |
| **DX** | ❌ Poor | ✅ Excellent | Use trait |
| **Laravel Style** | ❌ Foreign | ✅ Native | Use trait |
| **Testability** | ⚠️ Moderate | ✅ Easy | Use trait |
| **Documentation** | ❌ Separate | ✅ Self-documenting | Use trait |
| **Use Case** | Legacy/Symfony compat | New Laravel projects | Use trait |

**🎯 Bottom Line:** Use `InteractsWithWorkflow` trait for all new Laravel projects. Only use `supports` configuration if you absolutely need Symfony compatibility.

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

## UI Configuration

Flowstone includes an optional visual workflow designer and management interface, similar to Laravel Telescope.

### Enable/Disable UI

Control whether the UI is accessible:

```php
'ui' => [
    'enabled' => env('FLOWSTONE_UI_ENABLED', true),
],
```

Set in your `.env` file:

```env
FLOWSTONE_UI_ENABLED=true
```

### UI Path and Domain

Customize where the UI is accessible:

```php
'ui' => [
    'path' => env('FLOWSTONE_UI_PATH', 'flowstone'),
    'domain' => env('FLOWSTONE_UI_DOMAIN', null),
],
```

**Examples**:

- Default: `http://your-app.test/flowstone`
- Custom path: Set `FLOWSTONE_UI_PATH=admin/workflows`
- Custom domain: Set `FLOWSTONE_UI_DOMAIN=workflows.your-app.test`

### Middleware Configuration

Define middleware stack for UI routes:

```php
'ui' => [
    'middleware' => [
        'web',
        // 'auth',      // Require authentication
        // 'verified',  // Require email verification
        // 'throttle:60,1', // Rate limiting
    ],
],
```

### Authorization Gate

Configure who can access the UI:

```php
'ui' => [
    'gate' => env('FLOWSTONE_UI_GATE', 'viewFlowstone'),
],
```

Define the gate in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewFlowstone', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

### Asset Configuration

Configure how UI assets are served:

```php
'ui' => [
    'asset_url' => env('FLOWSTONE_UI_ASSET_URL', '/vendor/flowstone'),
    'inline_assets' => env('FLOWSTONE_INLINE_ASSETS', true),
],
```

- **`asset_url`**: Base URL for published UI assets
- **`inline_assets`**: Whether to inline assets in HTML (true) or serve as separate files (false)

**Publishing Assets**:

```bash
php artisan vendor:publish --tag="flowstone-ui-assets"
# or
php artisan flowstone:publish-assets
```

### Allow List

Specify allowed users when not in local environment:

```php
'ui' => [
    'allowed' => [
        'admin@example.com',
        'manager@example.com',
        // Or use user IDs:
        // 1, 2, 3,
    ],
],
```

### Complete UI Configuration Example

```php
'ui' => [
    'enabled' => env('FLOWSTONE_UI_ENABLED', true),
    'path' => env('FLOWSTONE_UI_PATH', 'flowstone'),
    'domain' => env('FLOWSTONE_UI_DOMAIN', null),
    'middleware' => [
        'web',
        'auth',
    ],
    'gate' => env('FLOWSTONE_UI_GATE', 'viewFlowstone'),
    'allowed' => [
        'admin@example.com',
    ],
    'asset_url' => env('FLOWSTONE_UI_ASSET_URL', '/vendor/flowstone'),
    'inline_assets' => env('FLOWSTONE_INLINE_ASSETS', true),
],
```

## Environment Variables Reference

All available environment variables for Flowstone:

```env
# UI Configuration
FLOWSTONE_UI_ENABLED=true
FLOWSTONE_UI_PATH=flowstone
FLOWSTONE_UI_DOMAIN=null
FLOWSTONE_UI_GATE=viewFlowstone
FLOWSTONE_UI_ASSET_URL=/vendor/flowstone
FLOWSTONE_INLINE_ASSETS=true

# Cache Configuration
FLOWSTONE_CACHE_DURATION=60

# Default Workflow Settings
FLOWSTONE_DEFAULT_TYPE=state_machine
```

## Next Steps

- Learn about [Database Workflows](database-workflows.md) for dynamic configurations
- Explore [Workflows](../03-usage/01-workflows.md) for implementation details
- Learn about [Workflow Details](../03-usage/02-workflow-details.md) for managing workflows
- Discover the [Workflow Designer](../03-usage/03-workflow-designer.md) for drag-and-drop workflow design
- Check [Examples](../../examples/) for real-world patterns
- Review [API Reference](../04-api/01-api-reference.md) for method documentation
