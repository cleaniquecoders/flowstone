# Workflow Organization Examples

This document provides practical examples of using Flowstone's organization features.

## Example 1: E-commerce Order Processing

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

## Example 2: HR Onboarding Process

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

// Query all HR workflows with compliance requirements
$complianceWorkflows = Workflow::byGroup('hr')
    ->byTag('Compliance')
    ->isEnabled()
    ->get();
```

## Example 3: Financial Document Approval

```php
// Invoice approval workflow
$invoiceWorkflow = Workflow::create([
    'name' => 'Invoice Approval',
    'description' => 'Multi-level invoice approval workflow',
    'group' => 'finance',
    'category' => 'document-management',
    'tags' => ['Critical', 'Approval Required', 'SLA', 'Multi-step'],
    'type' => 'state_machine',
    'initial_marking' => 'draft',
    'is_enabled' => true,
]);

// Expense report workflow
$expenseWorkflow = Workflow::create([
    'name' => 'Expense Report',
    'description' => 'Employee expense report approval',
    'group' => 'finance',
    'category' => 'document-management',
    'tags' => ['Approval Required', 'Internal', 'Automated'],
    'type' => 'state_machine',
    'initial_marking' => 'submitted',
    'is_enabled' => true,
]);

// Find all finance workflows requiring approval
$approvalWorkflows = Workflow::byGroup('finance')
    ->byTag('Approval Required')
    ->get();

// Find critical SLA workflows
$criticalSlaWorkflows = Workflow::byTags(['Critical', 'SLA'])->get();
```

## Example 4: Dashboard with Statistics

```php
use CleaniqueCoders\Flowstone\Services\WorkflowOrganizationService;

class WorkflowDashboardController extends Controller
{
    public function index(WorkflowOrganizationService $service)
    {
        $summary = $service->getSummary();
        $groupsWithCounts = $service->getGroupsWithCounts();
        $tagsWithCounts = $service->getTagsWithCounts();

        return view('workflows.dashboard', [
            'totalWorkflows' => $summary['total_workflows'],
            'enabledWorkflows' => $summary['enabled_workflows'],
            'groups' => $groupsWithCounts,
            'categories' => $service->getCategoriesWithCounts(),
            'popularTags' => $tagsWithCounts->take(10),
        ]);
    }
}
```

## Example 5: Filtering in Livewire Component

```php
use CleaniqueCoders\Flowstone\Models\Workflow;
use Livewire\Component;

class WorkflowList extends Component
{
    public $selectedGroup = null;
    public $selectedCategory = null;
    public $selectedTags = [];
    public $searchTerm = '';

    public function render()
    {
        $query = Workflow::query();

        if ($this->selectedGroup) {
            $query->byGroup($this->selectedGroup);
        }

        if ($this->selectedCategory) {
            $query->byCategory($this->selectedCategory);
        }

        if (!empty($this->selectedTags)) {
            $query->byAnyTag($this->selectedTags);
        }

        if ($this->searchTerm) {
            $query->search($this->searchTerm);
        }

        return view('livewire.workflow-list', [
            'workflows' => $query->isEnabled()->paginate(20),
            'availableGroups' => Workflow::getAllGroups(),
            'availableCategories' => Workflow::getAllCategories(),
            'availableTags' => Workflow::getAllTags(),
        ]);
    }
}
```

## Example 6: Tag Management

```php
use CleaniqueCoders\Flowstone\Services\WorkflowOrganizationService;

class WorkflowMaintenanceController extends Controller
{
    public function renameTag(Request $request, WorkflowOrganizationService $service)
    {
        $validated = $request->validate([
            'old_tag' => 'required|string',
            'new_tag' => 'required|string',
        ]);

        $count = $service->renameTag(
            $validated['old_tag'],
            $validated['new_tag']
        );

        return response()->json([
            'message' => "Renamed tag in {$count} workflows",
            'count' => $count,
        ]);
    }

    public function deleteTag(Request $request, WorkflowOrganizationService $service)
    {
        $validated = $request->validate([
            'tag' => 'required|string',
        ]);

        $count = $service->deleteTag($validated['tag']);

        return response()->json([
            'message' => "Deleted tag from {$count} workflows",
            'count' => $count,
        ]);
    }

    public function consolidateTags(WorkflowOrganizationService $service)
    {
        // Example: Consolidate similar tags
        $service->renameTag('high-priority', 'Critical');
        $service->renameTag('urgent', 'Critical');
        $service->renameTag('auto', 'Automated');

        return redirect()->back()->with('success', 'Tags consolidated successfully');
    }
}
```

## Example 7: Workflow Organization Report

```php
use CleaniqueCoders\Flowstone\Services\WorkflowOrganizationService;

class WorkflowReportController extends Controller
{
    public function organizationReport(WorkflowOrganizationService $service)
    {
        $summary = $service->getSummary();
        $organized = $service->getOrganizedWorkflows();

        $report = [
            'summary' => $summary,
            'by_category' => [],
        ];

        foreach ($organized as $category => $groupedWorkflows) {
            $report['by_category'][$category] = [
                'total' => $groupedWorkflows->flatten()->count(),
                'by_group' => [],
            ];

            foreach ($groupedWorkflows as $group => $workflows) {
                $report['by_category'][$category]['by_group'][$group] = [
                    'count' => $workflows->count(),
                    'enabled' => $workflows->where('is_enabled', true)->count(),
                    'workflows' => $workflows->pluck('name')->toArray(),
                ];
            }
        }

        return view('workflows.report', compact('report'));
    }
}
```

## Example 8: Seeding Workflows with Organization

```php
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    public function run()
    {
        // Finance workflows
        Workflow::factory()->create([
            'name' => 'Invoice Approval',
            'group' => 'finance',
            'category' => 'document-management',
            'tags' => ['Critical', 'Approval Required', 'SLA'],
        ]);

        Workflow::factory()->create([
            'name' => 'Payment Processing',
            'group' => 'finance',
            'category' => 'payment',
            'tags' => ['Automated', 'Time-sensitive', 'Critical'],
        ]);

        // HR workflows
        Workflow::factory()->create([
            'name' => 'Employee Onboarding',
            'group' => 'hr',
            'category' => 'recruitment',
            'tags' => ['Multi-step', 'Internal', 'Compliance'],
        ]);

        Workflow::factory()->create([
            'name' => 'Leave Request',
            'group' => 'hr',
            'category' => 'employee-management',
            'tags' => ['Approval Required', 'Internal', 'Automated'],
        ]);

        // Operations workflows
        Workflow::factory()->create([
            'name' => 'Inventory Management',
            'group' => 'operations',
            'category' => 'inventory',
            'tags' => ['Automated', 'Time-sensitive', 'Critical'],
        ]);

        Workflow::factory()->create([
            'name' => 'Quality Control',
            'group' => 'operations',
            'category' => 'quality',
            'tags' => ['Multi-step', 'Compliance', 'Manual Review'],
        ]);
    }
}
```

## Example 9: Advanced Search

```php
use CleaniqueCoders\Flowstone\Models\Workflow;

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

        // Tag filters
        if ($request->has('must_have_tags')) {
            $query->byTags($request->input('must_have_tags'));
        }

        if ($request->has('any_of_tags')) {
            $query->byAnyTag($request->input('any_of_tags'));
        }

        // Text search
        if ($request->has('search')) {
            $query->search($request->input('search'));
        }

        // Enabled only
        if ($request->boolean('enabled_only')) {
            $query->isEnabled();
        }

        return $query->paginate(20);
    }
}
```

## Example 10: API Endpoints

```php
use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Services\WorkflowOrganizationService;
use Illuminate\Http\Request;

// Get all organization options
Route::get('/api/workflows/organization', function () {
    return response()->json([
        'groups' => Workflow::getAllGroups(),
        'categories' => Workflow::getAllCategories(),
        'tags' => Workflow::getAllTags(),
    ]);
});

// Get workflows by organization
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

// Get organization statistics
Route::get('/api/workflows/statistics', function (WorkflowOrganizationService $service) {
    return response()->json($service->getSummary());
});
```
