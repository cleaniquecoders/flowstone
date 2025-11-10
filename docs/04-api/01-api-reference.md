# API Reference

Complete API reference for all Flowstone classes, methods, and interfaces.

## Core Classes

### Workflow Model

The main model for managing workflow configurations.

```php
CleaniqueCoders\Flowstone\Models\Workflow
```

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `name` | string | Workflow name/identifier |
| `description` | string | Human-readable description |
| `type` | string | 'state_machine' or 'workflow' |
| `initial_marking` | string | Default starting state |
| `marking` | string | Current workflow state |
| `config` | array | Cached workflow configuration |
| `is_enabled` | boolean | Whether workflow is active |
| `meta` | array | Additional metadata |

#### Methods

**getSymfonyConfig(): array**

Generate Symfony Workflow configuration from database structure.

```php
$workflow = Workflow::find(1);
$config = $workflow->getSymfonyConfig();
```

**scopeIsEnabled($query)**

Query scope to filter enabled workflows.

```php
$activeWorkflows = Workflow::isEnabled()->get();
```

**places(): HasMany**

Relationship to workflow places.

```php
$places = $workflow->places()->orderBy('sort_order')->get();
```

**transitions(): HasMany**

Relationship to workflow transitions.

```php
$transitions = $workflow->transitions()->orderBy('sort_order')->get();
```

### InteractsWithWorkflow Trait

The main trait that adds workflow functionality to your models.

```php
CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow
```

> **💡 Recommended Approach**: Use this trait instead of Symfony's `supports` configuration for better Laravel developer experience. You get full IDE autocomplete, type safety, and dynamic workflow selection. See [Configuration Guide](../02-configuration/01-configuration.md#model-integration-trait-vs-supports-configuration) for detailed comparison.

#### Required Methods

Models using this trait must implement:

**workflowType(): Attribute**

Returns the workflow type identifier.

```php
public function workflowType(): Attribute
{
    return Attribute::make(get: fn () => 'my-workflow');
}
```

**workflowTypeField(): Attribute**

Returns the field name that stores the workflow type.

```php
public function workflowTypeField(): Attribute
{
    return Attribute::make(get: fn () => 'workflow_type');
}
```

#### Available Methods

**setWorkflow(): self**

Initialize workflow configuration for the model.

```php
$model->setWorkflow();
```

**getWorkflow(): Workflow**

Get the Symfony Workflow instance.

```php
$workflow = $model->getWorkflow();
if ($workflow->can($model, 'submit')) {
    $workflow->apply($model, 'submit');
}
```

**getWorkflowKey(): string**

Generate cache key for workflow configuration.

```php
$cacheKey = $model->getWorkflowKey();
```

**getMarking(): string**

Get current workflow state.

```php
$currentState = $model->getMarking(); // 'draft'
```

**getEnabledTransitions(): array**

Get all available Symfony transition objects.

```php
$transitions = $model->getEnabledTransitions();
foreach ($transitions as $transition) {
    echo $transition->getName();
}
```

**getEnabledToTransitions(): array**

Get available transitions as key-value pairs.

```php
$transitions = $model->getEnabledToTransitions();
// Returns: ['approved' => 'Approved', 'rejected' => 'Rejected']
```

**hasEnabledToTransitions(): bool**

Check if any transitions are available.

```php
if ($model->hasEnabledToTransitions()) {
    // Show transition buttons
}
```

**getRolesFromTransition(?string $marking = null, string $type = 'to'): array**

Get required roles for transitions.

```php
// Roles that can transition TO current state
$roles = $model->getRolesFromTransition();

// Roles that can transition FROM specific state
$roles = $model->getRolesFromTransition('under_review', 'from');
```

**getAllEnabledTransitionRoles(): array**

Get roles for all available transitions.

```php
$allRoles = $model->getAllEnabledTransitionRoles();
// Returns: ['approved' => ['manager'], 'rejected' => ['admin']]
```

### Status Enum

Predefined workflow states.

```php
CleaniqueCoders\Flowstone\Enums\Status
```

#### Available Status Values

| Status | Value | Description |
|--------|-------|-------------|
| `DRAFT` | 'draft' | Initial preparation state |
| `PENDING` | 'pending' | Waiting to be started |
| `IN_PROGRESS` | 'in-progress' | Currently being executed |
| `UNDER_REVIEW` | 'under-review' | Being reviewed/evaluated |
| `APPROVED` | 'approved' | Approved to proceed |
| `REJECTED` | 'rejected' | Rejected and blocked |
| `ON_HOLD` | 'on-hold' | Temporarily suspended |
| `CANCELLED` | 'cancelled' | Permanently cancelled |
| `COMPLETED` | 'completed' | Successfully finished |
| `FAILED` | 'failed' | Failed execution |
| `PAUSED` | 'paused' | Temporarily paused |
| `ARCHIVED` | 'archived' | Archived/historical |

#### Methods

**label(): string**

Get human-readable label.

```php
echo Status::DRAFT->label(); // 'Draft'
echo Status::IN_PROGRESS->label(); // 'In Progress'
```

**description(): string**

Get detailed description.

```php
echo Status::DRAFT->description();
// 'Initial state where the workflow is being prepared or configured.'
```

**options(): array**

Get all status options (from InteractsWithEnum trait).

```php
$allStatuses = Status::options();
```

**values(): array**

Get all status values (from InteractsWithEnum trait).

```php
$allValues = Status::values();
// ['draft', 'pending', 'in-progress', ...]
```

## Helper Functions

### create_workflow()

Create a Symfony Workflow instance from configuration.

```php
function create_workflow(array $configuration, ?Registry $registry = null): SymfonyWorkflow
```

**Parameters:**

- `$configuration` - Workflow configuration array
- `$registry` - Optional Symfony Workflow Registry instance

**Example:**

```php
$config = [
    'type' => 'state_machine',
    'places' => ['draft', 'published'],
    'transitions' => [
        'publish' => [
            'from' => ['draft'],
            'to' => 'published'
        ]
    ]
];

$workflow = create_workflow($config);
```

### get_workflow_config()

Retrieve workflow configuration from database or default.

```php
function get_workflow_config(string $name, string $field = 'name'): array
```

**Parameters:**

- `$name` - Workflow name/identifier
- `$field` - Field to search by (default: 'name')

**Example:**

```php
$config = get_workflow_config('document-approval');
$configByType = get_workflow_config('approval', 'type');
```

### get_roles_from_transition()

Extract roles from workflow transition metadata.

```php
function get_roles_from_transition(array $workflow, string $marking, string $type = 'to'): array
```

**Parameters:**

- `$workflow` - Workflow configuration array
- `$marking` - Current or target state
- `$type` - Direction: 'to' or 'from'

**Example:**

```php
$workflow = ['transitions' => [
    'approve' => [
        'from' => ['review'],
        'to' => 'approved',
        'metadata' => ['roles' => ['manager', 'admin']]
    ]
]];

$roles = get_roles_from_transition($workflow, 'approved', 'to');
// Returns: ['manager', 'admin']
```

## Contracts

### Workflow Contract

Interface that workflow-enabled models must implement.

```php
CleaniqueCoders\Flowstone\Contracts\Workflow
```

> **💡 Design Pattern**: This contract ensures type safety and IDE support. When combined with `InteractsWithWorkflow` trait, you get the best developer experience - much better than Symfony's `supports` array configuration.

#### Required Methods

**workflowType(): Attribute**

Must return the workflow type identifier.

**workflowTypeField(): Attribute**

Must return the field name storing workflow type.

#### Implementation Example

```php
class MyModel extends Model implements Workflow
{
    use InteractsWithWorkflow;

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => $this->type);
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'type');
    }
}
```

## Service Provider

### FlowstoneServiceProvider

Main service provider for package registration.

```php
CleaniqueCoders\Flowstone\FlowstoneServiceProvider
```

#### Registered Services

**Symfony Workflow Registry**

Registered as singleton in the container:

```php
app(Registry::class); // Get registry instance
```

#### Published Resources

- **Migrations**: `--tag="flowstone-migrations"`
- **Configuration**: `--tag="flowstone-config"`
- **Views**: `--tag="flowstone-views"`

## Artisan Commands

### FlowstoneCommand

Base command for Flowstone operations.

```bash
php artisan flowstone
```

### CreateWorkflowCommand

Create new workflow configurations.

```bash
php artisan workflow:create
```

## Processors

### Workflow Processor

Internal class for handling workflow operations.

```php
CleaniqueCoders\Flowstone\Processors\Workflow
```

#### Static Methods

**createWorkflow(array $config, Registry $registry): SymfonyWorkflow**

Create Symfony Workflow from configuration.

**getDefaultWorkflow(): array**

Get default workflow configuration.

## Database Models

### WorkflowPlace

Represents workflow states/places.

```php
CleaniqueCoders\Flowstone\Models\WorkflowPlace
```

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `workflow_id` | integer | Foreign key to workflows table |
| `name` | string | Place name (e.g., 'draft') |
| `sort_order` | integer | Display order |
| `meta` | array | Symfony metadata |

#### Relationships

**workflow(): BelongsTo**

```php
$place->workflow; // Get parent workflow
```

### WorkflowTransition

Represents workflow transitions.

```php
CleaniqueCoders\Flowstone\Models\WorkflowTransition
```

#### Properties

| Property | Type | Description |
|----------|------|-------------|
| `workflow_id` | integer | Foreign key to workflows table |
| `name` | string | Transition name (e.g., 'submit') |
| `from_place` | string | Source state |
| `to_place` | string | Target state |
| `sort_order` | integer | Display order |
| `meta` | array | Symfony metadata |

#### Relationships

**workflow(): BelongsTo**

```php
$transition->workflow; // Get parent workflow
```

## Facades

### Flowstone Facade

Main facade for package functionality.

```php
CleaniqueCoders\Flowstone\Facades\Flowstone
```

Currently a placeholder for future functionality.

## Exception Handling

### Common Exceptions

**WorkflowException**

Thrown when workflow operations fail:

```php
try {
    $workflow->apply($model, 'invalid_transition');
} catch (\Symfony\Component\Workflow\Exception\LogicException $e) {
    // Handle invalid transition
}
```

**Configuration Errors**

Check configuration validity:

```php
if (empty($model->config)) {
    throw new \Exception('Workflow configuration not found');
}
```

## Events

### Symfony Workflow Events

Flowstone leverages Symfony's workflow events:

**workflow.entered**

Fired when entering a new place:

```php
Event::listen('workflow.entered', function ($event) {
    $subject = $event->getSubject();
    $place = $event->getTransition()->getTos()[0];
    // Handle place entry
});
```

**workflow.transition**

Fired during transitions:

```php
Event::listen('workflow.transition', function ($event) {
    $subject = $event->getSubject();
    $transition = $event->getTransition();
    // Handle transition
});
```

## Integration Examples

### With Laravel Authorization

```php
// In a Policy
public function approve(User $user, Document $document): bool
{
    $workflow = $document->getWorkflow();

    if (!$workflow->can($document, 'approve')) {
        return false;
    }

    $requiredRoles = $document->getRolesFromTransition();
    return $user->hasAnyRole($requiredRoles);
}
```

### With Form Requests

```php
class TransitionDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');
        $transition = $this->input('transition');

        return $document->getWorkflow()->can($document, $transition);
    }
}
```

### With Jobs

```php
class ProcessDocumentWorkflow implements ShouldQueue
{
    public function handle(Document $document, string $transition)
    {
        $workflow = $document->getWorkflow();

        if ($workflow->can($document, $transition)) {
            $workflow->apply($document, $transition);
            $document->save();
        }
    }
}
```

## Performance Tips

### Caching Strategies

```php
// Cache workflow instances
Cache::remember("workflow.{$model->id}", 3600, function () use ($model) {
    return $model->getWorkflow();
});

// Batch load workflow data
$models = Model::with(['workflow.places', 'workflow.transitions'])->get();
```

### Database Optimization

```php
// Add indexes for common queries
Schema::table('your_models', function (Blueprint $table) {
    $table->index(['status', 'workflow_type']);
    $table->index(['status', 'created_at']);
});
```

## Migration Patterns

### Adding Workflow to Existing Models

```php
Schema::table('existing_models', function (Blueprint $table) {
    $table->string('status')->default('draft')->after('id');
    $table->string('workflow_type')->default('default')->after('status');
    $table->json('workflow_config')->nullable()->after('workflow_type');
});
```

### Versioning Workflow Configurations

```php
Schema::table('workflows', function (Blueprint $table) {
    $table->string('version')->default('1.0')->after('name');
    $table->boolean('is_current_version')->default(true)->after('version');
});
```
