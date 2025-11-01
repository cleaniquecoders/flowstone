# Workflow Organization

Flowstone provides flexible organization features to help you categorize and manage your workflows effectively through **groups**, **categories**, and **tags**.

## Overview

- **Group**: Organize workflows by department, team, or business unit (e.g., "finance", "hr", "operations")
- **Category**: Classify workflows by function or domain (e.g., "document-management", "e-commerce", "approval")
- **Tags**: Add multiple flexible labels for cross-cutting concerns (e.g., "Critical", "Automated", "SLA")

> **Note**: Groups and categories are defined by your application, not pre-configured in Flowstone. This gives you complete flexibility to structure workflows according to your business needs.

## Database Schema

The workflow organization fields are stored in the `workflows` table:

```php
Schema::table('workflows', function (Blueprint $table) {
    $table->string('group')->nullable()->index();
    $table->string('category')->nullable()->index();
    $table->json('tags')->nullable();
});
```

## Creating Workflows with Organization

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

$workflow = Workflow::create([
    'name' => 'Invoice Approval',
    'description' => 'Multi-level invoice approval process',
    'group' => 'finance',
    'category' => 'document-management',
    'tags' => ['Critical', 'Approval Required', 'SLA'],
    'type' => 'state_machine',
    'is_enabled' => true,
]);
```

## Tag Management

### Adding Tags

```php
// Add a single tag
$workflow->addTag('Automated');

// Sync tags (replaces all existing tags)
$workflow->syncTags(['Critical', 'Manual Review', 'Time-sensitive']);
```

### Removing Tags

```php
$workflow->removeTag('Automated');
```

### Checking Tags

```php
if ($workflow->hasTag('Critical')) {
    // Handle critical workflow
}
```

## Querying Workflows

### By Group

```php
$financeWorkflows = Workflow::byGroup('finance')->get();
```

### By Category

```php
$documentWorkflows = Workflow::byCategory('document-management')->get();
```

### By Tag

```php
// Workflows with a specific tag
$criticalWorkflows = Workflow::byTag('Critical')->get();

// Workflows with ALL specified tags
$strictWorkflows = Workflow::byTags(['Critical', 'Compliance', 'SLA'])->get();

// Workflows with ANY of the specified tags
$anyWorkflows = Workflow::byAnyTag(['Automated', 'Manual Review'])->get();
```

### Complex Queries

Combine multiple filters:

```php
$workflows = Workflow::byCategory('e-commerce')
    ->byGroup('sales')
    ->byAnyTag(['Automated', 'Customer-facing'])
    ->isEnabled()
    ->get();
```

### Search

Search across name, description, group, category, and tags:

```php
$results = Workflow::search('invoice')->get();
```

## Getting Organization Data

### All Groups

```php
$groups = Workflow::getAllGroups();
// Returns: ['finance', 'hr', 'operations', 'sales']
```

### All Categories

```php
$categories = Workflow::getAllCategories();
// Returns: ['document-management', 'e-commerce', 'approval']
```

### All Tags

```php
$tags = Workflow::getAllTags();
// Returns: ['Critical', 'Automated', 'SLA', 'Compliance', ...]
```

## Workflow Organization Service

Flowstone provides a `WorkflowOrganizationService` for advanced organization operations.

### Get Counts

```php
use CleaniqueCoders\Flowstone\Services\WorkflowOrganizationService;

$service = new WorkflowOrganizationService();

// Groups with workflow counts
$groups = $service->getGroupsWithCounts();
// Returns: ['finance' => 12, 'hr' => 8, 'operations' => 15]

// Categories with workflow counts
$categories = $service->getCategoriesWithCounts();
// Returns: ['document-management' => 8, 'e-commerce' => 12, ...]

// Tags with usage counts
$tags = $service->getTagsWithCounts();
// Returns: ['Critical' => 15, 'Automated' => 20, 'SLA' => 8, ...]
```

### Get Summary

```php
$summary = $service->getSummary();
// Returns:
// [
//     'total_workflows' => 45,
//     'enabled_workflows' => 40,
//     'groups' => ['finance' => 12, ...],
//     'categories' => ['accounting' => 8, ...],
//     'tags' => ['Critical' => 15, ...]
// ]
```

### Get Organized Workflows

```php
// Get workflows grouped by category and group
$organized = $service->getOrganizedWorkflows();
// Returns: Collection grouped first by category, then by group
```

### Rename and Delete Tags

```php
// Rename a tag across all workflows
$count = $service->renameTag('OldTag', 'NewTag');
// Returns: Number of workflows updated

// Delete a tag from all workflows
$count = $service->deleteTag('Obsolete Tag');
// Returns: Number of workflows updated
```

## Defining Organization in Your Application

Create a configuration file in your application to define your organizational structure:

```php
// config/workflows.php
return [
    'groups' => [
        'finance' => [
            'label' => 'Finance',
            'icon' => 'currency-dollar',
            'color' => '#10B981',
            'description' => 'Financial workflows and processes',
        ],
        'hr' => [
            'label' => 'Human Resources',
            'icon' => 'users',
            'color' => '#F59E0B',
            'description' => 'HR and recruitment workflows',
        ],
        'operations' => [
            'label' => 'Operations',
            'icon' => 'cog',
            'color' => '#3B82F6',
            'description' => 'Operational workflows',
        ],
        'sales' => [
            'label' => 'Sales',
            'icon' => 'chart-bar',
            'color' => '#8B5CF6',
            'description' => 'Sales and marketing workflows',
        ],
    ],

    'categories' => [
        'document-management' => [
            'label' => 'Document Management',
            'icon' => 'document-text',
            'description' => 'Document processing and approval workflows',
        ],
        'e-commerce' => [
            'label' => 'E-commerce',
            'icon' => 'shopping-cart',
            'description' => 'Order processing and fulfillment workflows',
        ],
        'approval' => [
            'label' => 'Approval',
            'icon' => 'check-circle',
            'description' => 'Approval-based workflows',
        ],
        'reporting' => [
            'label' => 'Reporting',
            'icon' => 'chart-bar',
            'description' => 'Report generation and distribution',
        ],
    ],

    'suggested_tags' => [
        'Critical',
        'Automated',
        'Manual Review',
        'Multi-step',
        'Compliance',
        'Customer-facing',
        'Internal',
        'SLA',
        'Time-sensitive',
        'Approval Required',
    ],
];
```

Then use this configuration in your UI:

```php
// In your controller or Livewire component
$groups = config('workflows.groups');
$categories = config('workflows.categories');
$suggestedTags = config('workflows.suggested_tags');
```

## Best Practices

1. **Consistent Naming**: Use kebab-case for groups and categories (e.g., `document-management`, not `Document Management`)
2. **Limited Groups**: Keep the number of groups manageable (5-10 is ideal)
3. **Specific Categories**: Make categories specific enough to be meaningful
4. **Tag Standards**: Define a standard set of tags in your application configuration
5. **Tag Cleanup**: Periodically review and consolidate tags using the organization service

## UI Integration

You can use the organization data to build dynamic UIs:

```php
// Get data for a workflow dashboard
$groups = Workflow::getAllGroups();
$workflowsByGroup = [];

foreach ($groups as $group) {
    $workflowsByGroup[$group] = Workflow::byGroup($group)->isEnabled()->get();
}

// Pass to view
return view('workflows.dashboard', [
    'workflowsByGroup' => $workflowsByGroup,
    'availableTags' => Workflow::getAllTags(),
]);
```

## Migration

To add these fields to your existing workflows table:

```bash
php artisan migrate
```

The migration stub is located at:

```text
database/migrations/add_organization_fields_to_workflows_table.php.stub
```
