# Workflow Organization

Flowstone provides flexible organization features to help you categorize and manage your workflows effectively through **groups**, **categories**, and **tags**.

## Table of Contents

- [Overview](#overview)
- [Database Schema](#database-schema)
- [Creating Workflows with Organization](#creating-workflows-with-organization)
- [Tag Management](#tag-management)
- [Querying Workflows](#querying-workflows)
- [Practical Examples](#practical-examples)
- [Advanced Usage](#advanced-usage)
- [Best Practices](#best-practices)

## Overview

- **Group**: Organize workflows by department, team, or business unit (e.g., "finance", "hr", "operations")
- **Category**: Classify workflows by function or domain (e.g., "document-management", "e-commerce", "approval")
- **Tags**: Add multiple flexible labels for cross-cutting concerns (e.g., "Critical", "Automated", "SLA")

> **Note**: Groups and categories are defined by your application, not pre-configured in Flowstone. This gives you complete flexibility to structure workflows according to your business needs.

# Workflow Details

This page documents the Workflow Details screen where you can inspect places, transitions, and metadata of a workflow.

## Screenshot — Workflow Details

![Workflow Details — Document Approval](../../screenshots/workflow-details-document-approval.png)

### What you see

- Header with workflow name, type, and active status
- Quick Stats: number of places and transitions
- Places (States) listing with order
- Workflow Metadata card (editable)
- Configuration summary

## Managing Metadata

You can manage metadata at workflow and transition levels.

### Workflow Metadata Modal

![Manage Workflow Metadata Modal](../../screenshots/modal-workflow-metadata.png)

Use this to add key-value pairs like role, permission, author, version, etc.

### Transition Metadata Modal

![Manage Transition Metadata Modal](../../screenshots/modal-transition-metadata.png)

Typical keys:

- roles: ["manager", "reviewer"]
- label: "Verify Facts"
- guard / permission: "approve.documents"

## Tips

- Keep labels human-friendly; use snake_case for internal names
- Use metadata roles to drive authorization for transitions
- Store additional hints like SLA, priority, or notifications in metadata

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
    ->byAnyTag(['Automated', 'Time-sensitive'])
    ->isEnabled()
    ->get();
```

## Practical Examples

### Example 1: E-commerce Order Processing

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

// Create an e-commerce workflow
$orderWorkflow = Workflow::create([
    'name' => 'Order Processing',
    'description' => 'Complete order fulfillment workflow',
    'group' => 'sales',
    'category' => 'e-commerce',
    'tags' => ['Automated', 'Customer-facing', 'Time-sensitive', 'Payment'],
    'type' => 'state_machine',
    'initial_marking' => 'pending',
    'is_enabled' => true,
]);

// Query all e-commerce workflows in sales group
$salesEcommerceWorkflows = Workflow::byGroup('sales')
    ->byCategory('e-commerce')
    ->isEnabled()
    ->get();

// Find time-sensitive customer-facing workflows
$urgentWorkflows = Workflow::byAnyTag(['Time-sensitive', 'Critical'])
    ->byTag('Customer-facing')
    ->get();
```

### Example 2: HR Onboarding Process

```php
// Create HR onboarding workflow
$onboardingWorkflow = Workflow::create([
    'name' => 'Employee Onboarding',
    'description' => 'New employee onboarding process',
    'group' => 'hr',
    'category' => 'recruitment',
    'tags' => ['Multi-step', 'Internal', 'Compliance', 'Approval Required'],
    'type' => 'state_machine',
    'initial_marking' => 'new_hire',
    'is_enabled' => true,
]);

// Add additional tag during implementation
$onboardingWorkflow->addTag('Background-check');

// Find all HR workflows requiring compliance
$complianceWorkflows = Workflow::byGroup('hr')
    ->byTag('Compliance')
    ->isEnabled()
    ->get();
```

### Example 3: Document Approval System

```php
// Multiple document approval workflows
$workflows = [
    [
        'name' => 'Invoice Approval',
        'group' => 'finance',
        'category' => 'document-management',
        'tags' => ['Critical', 'Approval Required', 'SLA'],
    ],
    [
        'name' => 'Contract Approval',
        'group' => 'legal',
        'category' => 'document-management',
        'tags' => ['Critical', 'Approval Required', 'Multi-step'],
    ],
    [
        'name' => 'Report Approval',
        'group' => 'operations',
        'category' => 'document-management',
        'tags' => ['Approval Required'],
    ],
];

foreach ($workflows as $workflowData) {
    Workflow::create(array_merge($workflowData, [
        'type' => 'state_machine',
        'initial_marking' => 'draft',
        'is_enabled' => true,
    ]));
}

// Query all critical document approval workflows
$criticalDocWorkflows = Workflow::byCategory('document-management')
    ->byTags(['Critical', 'Approval Required'])
    ->isEnabled()
    ->get();
```

### Example 4: Marketing Campaign Workflow

```php
$campaignWorkflow = Workflow::create([
    'name' => 'Marketing Campaign Launch',
    'description' => 'Campaign approval and execution workflow',
    'group' => 'marketing',
    'category' => 'campaign',
    'tags' => ['Customer-facing', 'Time-sensitive', 'Multi-step', 'Approval Required'],
    'type' => 'state_machine',
    'initial_marking' => 'draft',
    'is_enabled' => true,
]);

// Find all customer-facing workflows across departments
$customerWorkflows = Workflow::byTag('Customer-facing')
    ->isEnabled()
    ->get()
    ->groupBy('group');
```

### Example 5: Workflow Dashboard Controller

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

class WorkflowDashboardController extends Controller
{
    public function index()
    {
        // Get statistics by group
        $groups = Workflow::getAllGroups();
        $workflowsByGroup = [];

        foreach ($groups as $group) {
            $workflowsByGroup[$group] = [
                'total' => Workflow::byGroup($group)->count(),
                'enabled' => Workflow::byGroup($group)->isEnabled()->count(),
                'workflows' => Workflow::byGroup($group)->isEnabled()->get(),
            ];
        }

        // Get critical workflows
        $criticalWorkflows = Workflow::byTag('Critical')
            ->isEnabled()
            ->get();

        // Get workflows requiring attention (specific tags)
        $attentionNeeded = Workflow::byAnyTag(['SLA', 'Time-sensitive'])
            ->isEnabled()
            ->get();

        return view('workflows.dashboard', [
            'workflowsByGroup' => $workflowsByGroup,
            'criticalWorkflows' => $criticalWorkflows,
            'attentionNeeded' => $attentionNeeded,
            'allTags' => Workflow::getAllTags(),
        ]);
    }
}
```

### Example 6: API Endpoints for Organization

```php
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Http\Request;

// Get all organization metadata
Route::get('/api/workflows/organization', function () {
    return response()->json([
        'groups' => Workflow::getAllGroups(),
        'categories' => Workflow::getAllCategories(),
        'tags' => Workflow::getAllTags(),
    ]);
});

// Get workflows with filters
Route::get('/api/workflows', function (Request $request) {
    $query = Workflow::query();

    if ($request->has('group')) {
        $query->byGroup($request->input('group'));
    }

    if ($request->has('category')) {
        $query->byCategory($request->input('category'));
    }

    if ($request->has('tags')) {
        $tags = explode(',', $request->input('tags'));
        $query->byAnyTag($tags);
    }

    return $query->isEnabled()->get();
});
```

## Advanced Usage

### Workflow Organization Service

Create a service to manage workflow organization:

```php
namespace App\Services;

use CleaniqueCoders\Flowstone\Models\Workflow;

class WorkflowOrganizationService
{
    public function getSummary(): array
    {
        $groups = Workflow::getAllGroups();
        $categories = Workflow::getAllCategories();
        $tags = Workflow::getAllTags();

        return [
            'total_workflows' => Workflow::count(),
            'enabled_workflows' => Workflow::isEnabled()->count(),
            'groups' => collect($groups)->map(function ($group) {
                return [
                    'name' => $group,
                    'count' => Workflow::byGroup($group)->count(),
                ];
            }),
            'categories' => collect($categories)->map(function ($category) {
                return [
                    'name' => $category,
                    'count' => Workflow::byCategory($category)->count(),
                ];
            }),
            'tags' => collect($tags)->map(function ($tag) {
                return [
                    'name' => $tag,
                    'count' => Workflow::byTag($tag)->count(),
                ];
            }),
        ];
    }

    public function suggestTags(Workflow $workflow): array
    {
        // Suggest tags based on group and category
        $suggestions = [];

        if ($workflow->group === 'finance') {
            $suggestions = ['Critical', 'Compliance', 'Approval Required'];
        } elseif ($workflow->group === 'hr') {
            $suggestions = ['Internal', 'Compliance', 'Multi-step'];
        }

        // Add category-specific suggestions
        if ($workflow->category === 'e-commerce') {
            $suggestions = array_merge($suggestions, ['Customer-facing', 'Automated']);
        }

        return array_unique($suggestions);
    }
}
```

### Advanced Search Implementation

```php
class WorkflowSearchController extends Controller
{
    public function advancedSearch(Request $request)
    {
        $query = Workflow::query();

        // Group filter
        if ($request->has('groups')) {
            $query->whereIn('group', $request->input('groups'));
        }

        // Category filter
        if ($request->has('categories')) {
            $query->whereIn('category', $request->input('categories'));
        }

        // Must have ALL these tags
        if ($request->has('must_have_tags')) {
            $query->byTags($request->input('must_have_tags'));
        }

        // Must have ANY of these tags
        if ($request->has('any_of_tags')) {
            $query->byAnyTag($request->input('any_of_tags'));
        }

        // Text search in name and description
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Enabled only filter
        if ($request->boolean('enabled_only')) {
            $query->isEnabled();
        }

        return $query->paginate(20);
    }
}
```

## Configuration Examples

Define group and category metadata in your application configuration:

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

## Organization Service Methods

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
