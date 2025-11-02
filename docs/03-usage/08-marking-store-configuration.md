# Per-Workflow Marking Store Configuration

By default, Flowstone uses the global marking store configuration defined in `config/flowstone.php`. However, you can configure each workflow to use its own marking store settings, allowing for greater flexibility when working with different models or workflow types.

## Table of Contents

- [Understanding Marking Stores](#understanding-marking-stores)
- [Configuration](#configuration)
- [Marking Store Types](#marking-store-types)
- [Usage Examples](#usage-examples)
- [Best Practices](#best-practices)

---

## Understanding Marking Stores

A **marking store** determines how the workflow state (marking) is stored and retrieved from your model. Symfony Workflow supports different marking store strategies:

### Single State vs Multiple State

- **Single State** (`single_state`): The model can only be in one place at a time
  - Used with `state_machine` workflow type
  - Stores a single string value (e.g., `"draft"`, `"published"`)

- **Multiple State** (`multiple_state`): The model can be in multiple places simultaneously
  - Used with `workflow` type
  - Stores an array of place names (e.g., `["editing", "reviewing"]`)

### Storage Property

The property name where the workflow state is stored on your model. Common choices:

- `marking` (default)
- `status`
- `state`
- `current_state`
- `workflow_state`

---

## Configuration

### Database Schema

The `workflows` table includes two fields for marking store configuration:

```php
Schema::table('workflows', function (Blueprint $table) {
    $table->enum('marking_store_type', ['method', 'property', 'single_state', 'multiple_state'])
        ->default('method');

    $table->string('marking_store_property')
        ->default('marking');
});
```

### Setting Marking Store Configuration

You can set the marking store configuration when creating or updating a workflow:

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

$workflow = Workflow::create([
    'name' => 'document-approval',
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'approval_status',
    'initial_marking' => 'pending',
]);
```

### Updating Configuration

```php
$workflow->update([
    'marking_store_type' => 'multiple_state',
    'marking_store_property' => 'current_places',
]);
```

---

## Marking Store Types

### `method` (Default)

Uses getter/setter methods to access the marking property. This is the standard approach and works for single state workflows.

```php
$workflow = Workflow::create([
    'name' => 'order-processing',
    'type' => 'state_machine',
    'marking_store_type' => 'method',
    'marking_store_property' => 'status',
]);
```

**Model example:**

```php
class Order extends Model
{
    use InteractsWithWorkflow;

    protected $fillable = ['status'];

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}
```

### `single_state`

Explicitly configures the workflow for single state storage. Best for `state_machine` type workflows.

```php
$workflow = Workflow::create([
    'name' => 'invoice-workflow',
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'invoice_status',
]);
```

**Model column:**

```php
Schema::create('invoices', function (Blueprint $table) {
    $table->string('invoice_status')->default('draft');
});
```

### `multiple_state`

Configures the workflow to support multiple simultaneous states. Required for `workflow` type.

```php
$workflow = Workflow::create([
    'name' => 'article-workflow',
    'type' => 'workflow',
    'marking_store_type' => 'multiple_state',
    'marking_store_property' => 'workflow_places',
]);
```

**Model column:**

```php
Schema::create('articles', function (Blueprint $table) {
    $table->json('workflow_places')->nullable();
});

// Model
class Article extends Model
{
    protected $casts = [
        'workflow_places' => 'array',
    ];
}
```

---

## Usage Examples

### Example 1: Different Models, Different Properties

```php
// Invoice workflow using 'invoice_status' property
$invoiceWorkflow = Workflow::create([
    'name' => 'invoice-processing',
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'invoice_status',
    'initial_marking' => 'draft',
]);

// Order workflow using 'order_state' property
$orderWorkflow = Workflow::create([
    'name' => 'order-fulfillment',
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'order_state',
    'initial_marking' => 'pending',
]);

// Document workflow using 'status' property
$documentWorkflow = Workflow::create([
    'name' => 'document-approval',
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'status',
    'initial_marking' => 'draft',
]);
```

### Example 2: Parallel Workflow with Multiple States

```php
$articleWorkflow = Workflow::create([
    'name' => 'article-publishing',
    'type' => 'workflow',
    'marking_store_type' => 'multiple_state',
    'marking_store_property' => 'current_places',
    'initial_marking' => 'editing',
]);

// Add places
$articleWorkflow->places()->createMany([
    ['name' => 'editing', 'sort_order' => 1],
    ['name' => 'reviewing', 'sort_order' => 2],
    ['name' => 'testing', 'sort_order' => 3],
    ['name' => 'published', 'sort_order' => 4],
]);

// Add transitions (can move to multiple states)
$articleWorkflow->transitions()->createMany([
    [
        'name' => 'start_review',
        'from_place' => 'editing',
        'to_place' => 'reviewing',
        'sort_order' => 1,
    ],
    [
        'name' => 'start_testing',
        'from_place' => 'reviewing',
        'to_place' => 'testing',
        'sort_order' => 2,
    ],
]);
```

**Model:**

```php
class Article extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = ['title', 'content', 'current_places'];

    protected $casts = [
        'current_places' => 'array',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'article-publishing');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }
}

// Usage
$article = Article::create(['title' => 'My Article']);
$article->applyTransition('start_review');  // Now in 'reviewing'
$article->applyTransition('start_testing'); // Now in both 'reviewing' and 'testing'

$places = $article->getMarkedPlaces(); // ['reviewing', 'testing']
```

### Example 3: Legacy System Migration

When migrating from an existing system with different column names:

```php
$legacyWorkflow = Workflow::create([
    'name' => 'legacy-order-workflow',
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'legacy_status_field', // Your existing column
    'initial_marking' => 'new',
]);
```

### Example 4: Retrieving Configuration

```php
$workflow = Workflow::find(1);

// Get marking store type (falls back to config if not set)
$type = $workflow->getMarkingStoreType(); // 'single_state'

// Get marking store property (falls back to config if not set)
$property = $workflow->getMarkingStoreProperty(); // 'status'

// Get full Symfony config (includes marking store if set)
$config = $workflow->getSymfonyConfig();
/*
[
    'type' => 'state_machine',
    'places' => [...],
    'transitions' => [...],
    'marking_store' => [
        'type' => 'single_state',
        'property' => 'status',
    ],
    'metadata' => [...],
]
*/
```

---

## Best Practices

### 1. Match Workflow Type with Marking Store Type

For consistency and clarity:

```php
// State machine → single_state
Workflow::create([
    'type' => 'state_machine',
    'marking_store_type' => 'single_state',
]);

// Workflow → multiple_state
Workflow::create([
    'type' => 'workflow',
    'marking_store_type' => 'multiple_state',
]);
```

### 2. Use Descriptive Property Names

Choose property names that clearly indicate their purpose:

```php
// Good ✅
'approval_status'
'order_state'
'document_workflow_state'
'current_places'

// Avoid ❌
'status'  // Too generic if you have multiple statuses
'state'   // Unclear what kind of state
'data'    // Not descriptive
```

### 3. Document Your Configuration

Add comments or documentation when using custom configurations:

```php
$workflow = Workflow::create([
    'name' => 'contract-approval',
    'description' => 'Contract approval workflow using legacy status field',
    'marking_store_property' => 'contract_status', // Legacy field from old system
    'meta' => [
        'notes' => 'Uses existing contract_status column for backward compatibility',
    ],
]);
```

### 4. Use JSON Columns for Multiple States

When using `multiple_state`, ensure your database column is JSON or array-compatible:

```php
// Migration
Schema::table('articles', function (Blueprint $table) {
    $table->json('workflow_places')->nullable();
});

// Model
protected $casts = [
    'workflow_places' => 'array',
];
```

### 5. Set Defaults in Configuration

For most workflows, use the global defaults in `config/flowstone.php`:

```php
// config/flowstone.php
'default' => [
    'marking_store' => [
        'type' => 'method',
        'property' => 'marking',
    ],
],
```

Only override at the workflow level when needed for specific use cases.

### 6. Validate Configuration

When creating workflows programmatically, validate the configuration:

```php
$workflow = Workflow::create([
    'type' => 'workflow',
    'marking_store_type' => 'multiple_state',
    'marking_store_property' => 'places',
]);

// Validate that the model has the required property
if (!Schema::hasColumn('articles', 'places')) {
    throw new \RuntimeException('Missing required column: places');
}
```

### 7. Consider Performance

- Single state workflows are more performant (simple string comparison)
- Multiple state workflows require array operations
- Use single state unless you truly need parallel states

---

## Migration Path

### Migrating Existing Workflows

If you're adding marking store configuration to existing workflows:

```php
use Illuminate\Support\Facades\DB;

// Update all existing workflows to use explicit configuration
DB::table('workflows')->update([
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'marking',
]);

// Or update specific workflows
Workflow::where('type', 'workflow')->update([
    'marking_store_type' => 'multiple_state',
]);
```

### Running the Migration

```bash
php artisan migrate
```

The migration adds the new columns with sensible defaults, so existing workflows will continue to work without changes.

---

## Troubleshooting

### Issue: Workflow not finding state

**Problem:** Workflow can't read the current state from your model.

**Solution:** Ensure the `marking_store_property` matches your model's column name:

```php
// Check your database column
Schema::hasColumn('orders', 'status'); // true

// Update workflow configuration
$workflow->update([
    'marking_store_property' => 'status', // Must match DB column
]);
```

### Issue: Multiple state workflow behaving as single state

**Problem:** Workflow replaces states instead of adding them.

**Solution:** Ensure you're using the correct marking store type:

```php
$workflow->update([
    'type' => 'workflow', // Must be 'workflow', not 'state_machine'
    'marking_store_type' => 'multiple_state', // Must be 'multiple_state'
]);
```

### Issue: States not persisting

**Problem:** States reset after reloading the model.

**Solution:** Ensure your model casts the property correctly for multiple states:

```php
// For multiple states
protected $casts = [
    'workflow_places' => 'array', // or 'json'
];
```

---

## UI Management

Flowstone provides a user-friendly interface to manage marking store configuration through the workflow create and edit forms.

### Creating a Workflow with Marking Store Configuration

When creating a new workflow through the UI:

1. Click the **"Create Workflow"** button
2. Fill in the basic workflow information (name, description, etc.)
3. In the **"Marking Store Configuration"** section:
   - Select the **Storage Type** from the dropdown:
     - **Method** - Standard getter/setter approach (recommended)
     - **Single State** - Explicit single state for state machines
     - **Multiple State** - Multiple simultaneous states for workflows
   - Enter the **Property Name** (default: `marking`)
4. The storage type automatically adjusts based on the workflow type you select

### Editing Marking Store Configuration

To update the marking store configuration for an existing workflow:

1. Navigate to the workflow details page
2. Click the **"Edit"** button
3. Scroll to the **"Marking Store Configuration"** section
4. Update the storage type and/or property name
5. Click **"Update Workflow"** to save changes

**Note:** Changing the marking store property on an existing workflow requires updating your model's database column or getter/setter methods accordingly.

### Viewing Marking Store Configuration

On the workflow details page, you can view the current marking store configuration:

- **Marking Store** field displays: `<Storage Type> → <property_name>`
- Example: `Single State → approval_status`

This makes it easy to see at a glance how each workflow stores its state.

### Auto-Configuration

The create form intelligently suggests the marking store type based on your workflow type:

- When you select **"State Machine"**, it defaults to **"Single State"**
- When you select **"Workflow"**, it defaults to **"Multiple State"**

You can override these defaults if needed for your specific use case.

---

## See Also

- [Workflows Usage Guide](01-workflows.md)
- [Advanced Workflow Features](07-advanced-features.md)
- [Configuration Guide](../../02-configuration/01-configuration.md)
- [API Reference](../../04-api/01-api-reference.md)
