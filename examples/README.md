# Flowstone Examples

This directory contains practical examples of implementing workflows using Flowstone in Laravel applications.

## Examples Overview

1. **[Document Approval Workflow](document-approval/)** - Classic approval process with roles and permissions
2. **[E-commerce Order Processing](ecommerce-order/)** - Complete order lifecycle from cart to delivery
3. **[Content Publishing Pipeline](content-publishing/)** - Blog/CMS content workflow with editorial process
4. **[Bug Tracking System](bug-tracking/)** - Issue management workflow for development teams
5. **[Employee Onboarding Process](employee-onboarding/)** - HR workflow for new employee integration

## Common Patterns

### Basic Setup

All examples follow this basic pattern:

1. **Model Implementation**: Implement the `Workflow` contract
2. **Database Configuration**: Define workflow in database tables
3. **Controller Logic**: Handle transitions and permissions
4. **Frontend Integration**: Display states and available actions

### Installation for Examples

Each example includes:

- Migration files
- Model definitions
- Controller examples
- Blade view templates
- Seeder for sample data
- Tests for workflow logic

### Running Examples

1. Choose an example directory
2. Copy the files to your Laravel application
3. Run the migrations
4. Run the seeders
5. Access the routes to see the workflow in action

## Quick Implementation Guide

### 1. Copy Example Files

```bash
# Copy model and migration
cp examples/document-approval/Models/Document.php app/Models/
cp examples/document-approval/database/migrations/* database/migrations/

# Copy controllers
cp examples/document-approval/Controllers/* app/Http/Controllers/

# Copy views
cp -r examples/document-approval/views/* resources/views/
```

### 2. Run Migrations and Seeds

```bash
php artisan migrate
php artisan db:seed --class=DocumentApprovalSeeder
```

### 3. Add Routes

```php
// routes/web.php
use App\Http\Controllers\DocumentController;

Route::resource('documents', DocumentController::class);
Route::post('documents/{document}/transition', [DocumentController::class, 'transition'])
    ->name('documents.transition');
```

### 4. Test the Workflow

Navigate to `/documents` to see the workflow in action.

## Customization Tips

### Adapting to Your Needs

1. **Change Status Names**: Modify the places in your workflow configuration
2. **Add Custom Metadata**: Include additional information in transition metadata
3. **Implement Role Checking**: Add your authentication logic to controllers
4. **Customize Views**: Modify the Blade templates to match your design
5. **Add Notifications**: Implement event listeners for workflow transitions

### Integration Patterns

```php
// Event-driven notifications
Event::listen('workflow.completed.document-approval', function ($event) {
    Mail::to($event->getSubject()->author)->send(new DocumentApprovedMail());
});

// Queue-based processing
class ProcessWorkflowTransition implements ShouldQueue
{
    public function handle($model, $transition)
    {
        $workflow = $model->getWorkflow();
        $workflow->apply($model, $transition);
        $model->save();
    }
}

// API integration
class WorkflowController extends Controller
{
    public function getTransitions(Request $request, $model)
    {
        return response()->json([
            'current_state' => $model->getMarking(),
            'transitions' => $model->getEnabledToTransitions(),
            'metadata' => $model->getAllEnabledTransitionRoles(),
        ]);
    }
}
```

## Best Practices Demonstrated

### 1. Separation of Concerns

Each example separates:

- **Model Logic**: Workflow contract implementation
- **Business Logic**: Controller methods for validation and processing
- **Presentation Logic**: Blade templates for UI
- **Data Logic**: Migrations and seeders

### 2. Role-Based Security

```php
// Controller method with role checking
public function transition(Request $request, Document $document)
{
    $transition = $request->input('transition');

    // Check workflow allows transition
    if (!$document->getWorkflow()->can($document, $transition)) {
        abort(422, 'Invalid transition');
    }

    // Check user has required role
    $requiredRoles = $document->getRolesFromTransition();
    if (!auth()->user()->hasAnyRole($requiredRoles)) {
        abort(403, 'Insufficient permissions');
    }

    // Apply transition
    $document->getWorkflow()->apply($document, $transition);
    $document->save();

    return redirect()->back()->with('success', 'Status updated successfully');
}
```

### 3. Event-Driven Architecture

```php
// Listener registration
protected $listen = [
    'workflow.entered' => [
        WorkflowStateChangeListener::class,
    ],
    'workflow.transition' => [
        WorkflowTransitionLogger::class,
    ],
];

// Listener implementation
class WorkflowStateChangeListener
{
    public function handle($event)
    {
        $model = $event->getSubject();
        $marking = $event->getMarking();

        // Send notifications based on new state
        switch ($marking) {
            case 'approved':
                event(new DocumentApprovedEvent($model));
                break;
            case 'rejected':
                event(new DocumentRejectedEvent($model));
                break;
        }
    }
}
```

### 4. Testing Strategies

```php
// Feature test example
class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_submit_draft_document()
    {
        $author = User::factory()->create();
        $document = Document::factory()->create([
            'author_id' => $author->id,
            'status' => 'draft'
        ]);

        $this->actingAs($author)
            ->post(route('documents.transition', $document), [
                'transition' => 'submit'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('submitted', $document->fresh()->status);
    }

    public function test_reviewer_cannot_approve_draft_document()
    {
        $reviewer = User::factory()->withRole('reviewer')->create();
        $document = Document::factory()->create(['status' => 'draft']);

        $this->actingAs($reviewer)
            ->post(route('documents.transition', $document), [
                'transition' => 'approve'
            ])
            ->assertStatus(422);
    }
}
```

## Advanced Examples

### Multi-tenant Workflows

```php
// Tenant-specific workflow configurations
class TenantAwareDocument extends Document
{
    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => "document-approval-{$this->tenant_id}"
        );
    }
}
```

### Conditional Transitions

```php
// Custom workflow processor with conditions
class ConditionalWorkflowProcessor
{
    public function canTransition($model, $transition): bool
    {
        $workflow = $model->getWorkflow();

        if (!$workflow->can($model, $transition)) {
            return false;
        }

        // Custom conditions
        switch ($transition) {
            case 'approve':
                return $model->hasAllRequiredFields() && $model->passesValidation();
            case 'publish':
                return $model->approved_at && $model->scheduled_at <= now();
            default:
                return true;
        }
    }
}
```

### Workflow Analytics

```php
// Track workflow metrics
class WorkflowAnalytics
{
    public function getAverageTimeInState(string $workflowType, string $state): float
    {
        return DB::table('workflow_transitions_log')
            ->where('workflow_type', $workflowType)
            ->where('to_state', $state)
            ->avg('duration_minutes');
    }

    public function getTransitionSuccess(string $transition): float
    {
        $total = DB::table('workflow_transitions_log')
            ->where('transition_name', $transition)
            ->count();

        $successful = DB::table('workflow_transitions_log')
            ->where('transition_name', $transition)
            ->where('status', 'completed')
            ->count();

        return $total > 0 ? ($successful / $total) * 100 : 0;
    }
}
```

## Performance Considerations

### Caching Strategies

```php
// Cache workflow configurations
class CachedWorkflowService
{
    public function getWorkflowConfig(string $type): array
    {
        return Cache::tags(['workflows'])
            ->remember("workflow.config.{$type}", 3600, function () use ($type) {
                return Workflow::where('name', $type)->first()->getSymfonyConfig();
            });
    }

    public function clearWorkflowCache(string $type = null): void
    {
        if ($type) {
            Cache::forget("workflow.config.{$type}");
        } else {
            Cache::tags(['workflows'])->flush();
        }
    }
}
```

### Database Optimization

```php
// Optimized queries for workflow data
class OptimizedWorkflowQuery
{
    public function getModelsWithTransitions(string $modelClass): Collection
    {
        return $modelClass::query()
            ->select(['id', 'status', 'workflow_type', 'updated_at'])
            ->with(['workflow:id,name,config'])
            ->whereHas('workflow', function ($query) {
                $query->where('is_enabled', true);
            })
            ->get()
            ->map(function ($model) {
                $model->available_transitions = $model->getEnabledToTransitions();
                return $model;
            });
    }
}
```

## Next Steps

1. **Choose an Example**: Pick the example that best matches your use case
2. **Implement Base Code**: Copy and adapt the example files
3. **Customize Workflow**: Modify states and transitions for your needs
4. **Add Business Logic**: Implement your specific validation and processing
5. **Test Thoroughly**: Use the provided test examples as a starting point
6. **Monitor Performance**: Implement caching and optimization as needed

Each example directory contains detailed README files with specific implementation instructions and customization options.
