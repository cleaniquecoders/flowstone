# Document Approval Workflow Example

A comprehensive document approval workflow demonstrating the classic approval process with role-based permissions.

> **💡 Note**: This example uses Flowstone's trait-based approach (`InteractsWithWorkflow`) instead of Symfony's `supports` configuration. This provides full IDE autocomplete, type safety, and better Laravel developer experience. See [Configuration Guide](../../docs/02-configuration/01-configuration.md#model-integration-trait-vs-supports-configuration) for details.

## Overview

This example implements a document management system with the following workflow:

**Draft → Submitted → Under Review → Approved/Rejected**

### Workflow States

- **Draft**: Initial state where authors create and edit documents
- **Submitted**: Document submitted for review, no longer editable by author
- **Under Review**: Being reviewed by managers/reviewers
- **Approved**: Document approved and published
- **Rejected**: Document rejected, can be revised and resubmitted

### Roles and Permissions

- **Author**: Can create, edit (draft only), and submit documents
- **Reviewer**: Can start review process and provide feedback
- **Manager**: Can approve or reject documents
- **Admin**: Has all permissions

## Implementation Files

### 1. Document Model

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Enums\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'title',
        'content',
        'status',
        'workflow_type',
        'author_id',
        'reviewer_id',
        'approved_by',
        'rejected_reason',
        'submitted_at',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Workflow Contract Implementation
    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->workflow_type ?? 'document-approval'
        );
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(
            get: fn () => 'workflow_type'
        );
    }

    public function getMarking(): string
    {
        return $this->status ?? Status::DRAFT->value;
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;

        // Update timestamps based on status
        switch ($marking) {
            case 'submitted':
                $this->submitted_at = now();
                break;
            case 'under_review':
                $this->reviewed_at = now();
                break;
            case 'approved':
                $this->approved_at = now();
                $this->approved_by = auth()->id();
                break;
        }
    }

    // Relationships
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper Methods
    public function isEditable(): bool
    {
        return $this->status === 'draft' && auth()->id() === $this->author_id;
    }

    public function canBeViewed(): bool
    {
        return auth()->user()->hasRole(['admin', 'manager']) ||
               auth()->id() === $this->author_id ||
               auth()->id() === $this->reviewer_id;
    }

    // Scopes
    public function scopeForUser($query, User $user)
    {
        if ($user->hasRole(['admin', 'manager'])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('author_id', $user->id)
              ->orWhere('reviewer_id', $user->id);
        });
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
```

### 2. Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content')->nullable();

            // Workflow fields
            $table->string('status')->default('draft');
            $table->string('workflow_type')->default('document-approval');

            // User relationships
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('reviewer_id')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');

            // Additional fields
            $table->text('rejected_reason')->nullable();

            // Timestamps for workflow states
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['author_id', 'status']);
            $table->index(['reviewer_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
```

### 3. Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query()
            ->with(['author', 'reviewer', 'approver'])
            ->forUser(Auth::user());

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by user role
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'authored':
                    $query->where('author_id', Auth::id());
                    break;
                case 'reviewing':
                    $query->where('reviewer_id', Auth::id())
                          ->whereIn('status', ['submitted', 'under_review']);
                    break;
                case 'pending_approval':
                    $query->where('status', 'under_review')
                          ->whereNull('approved_by');
                    break;
            }
        }

        $documents = $query->latest()->paginate(15);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $document = Document::create([
            'title' => $request->title,
            'content' => $request->content,
            'author_id' => Auth::id(),
            'status' => 'draft',
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document created successfully.');
    }

    public function show(Document $document)
    {
        Gate::authorize('view', $document);

        $availableTransitions = [];

        if ($this->canUserTransition($document)) {
            $availableTransitions = $document->getEnabledToTransitions();
        }

        return view('documents.show', compact('document', 'availableTransitions'));
    }

    public function edit(Document $document)
    {
        Gate::authorize('update', $document);

        if (!$document->isEditable()) {
            return redirect()->route('documents.show', $document)
                ->withErrors(['document' => 'Document cannot be edited in current status.']);
        }

        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        Gate::authorize('update', $document);

        if (!$document->isEditable()) {
            return redirect()->route('documents.show', $document)
                ->withErrors(['document' => 'Document cannot be edited in current status.']);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $document->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);

        if ($document->status !== 'draft') {
            return redirect()->route('documents.index')
                ->withErrors(['document' => 'Only draft documents can be deleted.']);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function transition(Request $request, Document $document)
    {
        $request->validate([
            'transition' => 'required|string',
            'reviewer_id' => 'nullable|exists:users,id',
            'rejected_reason' => 'nullable|string|required_if:transition,reject',
        ]);

        $transition = $request->transition;
        $workflow = $document->getWorkflow();

        // Check if transition is allowed by workflow
        if (!$workflow->can($document, $transition)) {
            return back()->withErrors(['transition' => 'Invalid transition for current state.']);
        }

        // Check user permissions
        if (!$this->canUserPerformTransition($document, $transition)) {
            return back()->withErrors(['transition' => 'You do not have permission to perform this transition.']);
        }

        // Handle specific transition logic
        switch ($transition) {
            case 'submit':
                $this->handleSubmit($document, $request);
                break;
            case 'start_review':
                $this->handleStartReview($document, $request);
                break;
            case 'approve':
                $this->handleApprove($document, $request);
                break;
            case 'reject':
                $this->handleReject($document, $request);
                break;
        }

        // Apply workflow transition
        $workflow->apply($document, $transition);
        $document->save();

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document status updated successfully.');
    }

    private function canUserTransition(Document $document): bool
    {
        $user = Auth::user();

        return $user->hasRole(['admin']) ||
               ($document->author_id === $user->id) ||
               ($document->reviewer_id === $user->id) ||
               $user->hasRole(['manager', 'reviewer']);
    }

    private function canUserPerformTransition(Document $document, string $transition): bool
    {
        $user = Auth::user();
        $requiredRoles = $document->getRolesFromTransition();

        // Admin can do anything
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check specific transition permissions
        switch ($transition) {
            case 'submit':
            case 'revise':
                return $document->author_id === $user->id;

            case 'start_review':
                return $user->hasRole('reviewer') || $user->hasRole('manager');

            case 'approve':
            case 'reject':
                return $user->hasRole(['manager', 'admin']);

            default:
                return false;
        }
    }

    private function handleSubmit(Document $document, Request $request): void
    {
        // Optionally assign a reviewer
        if ($request->filled('reviewer_id')) {
            $document->reviewer_id = $request->reviewer_id;
        }
    }

    private function handleStartReview(Document $document, Request $request): void
    {
        // Assign current user as reviewer if not set
        if (!$document->reviewer_id) {
            $document->reviewer_id = Auth::id();
        }
    }

    private function handleApprove(Document $document, Request $request): void
    {
        $document->approved_by = Auth::id();
    }

    private function handleReject(Document $document, Request $request): void
    {
        $document->rejected_reason = $request->rejected_reason;
        $document->approved_by = null;
        $document->approved_at = null;
    }
}
```

### 4. Policy

```php
<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view documents list
    }

    public function view(User $user, Document $document): bool
    {
        // Admin and managers can view any document
        if ($user->hasRole(['admin', 'manager'])) {
            return true;
        }

        // Authors can view their own documents
        if ($document->author_id === $user->id) {
            return true;
        }

        // Reviewers can view assigned documents
        if ($document->reviewer_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create documents
    }

    public function update(User $user, Document $document): bool
    {
        // Only authors can edit their own draft documents
        return $document->author_id === $user->id && $document->status === 'draft';
    }

    public function delete(User $user, Document $document): bool
    {
        // Admin can delete any document
        if ($user->hasRole('admin')) {
            return true;
        }

        // Authors can delete their own draft documents
        return $document->author_id === $user->id && $document->status === 'draft';
    }
}
```

## Setup Instructions

### 1. Install Required Dependencies

```bash
composer require cleaniquecoders/flowstone
composer require spatie/laravel-permission # For role management
```

### 2. Run Migrations

```bash
# Flowstone migrations
php artisan vendor:publish --tag="flowstone-migrations"
php artisan migrate

# Permission system
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

# Document migrations (copy from above)
php artisan make:migration create_documents_table
# Copy migration content from above
php artisan migrate
```

### 3. Create Workflow Configuration

Run this in `php artisan tinker`:

```php
use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

// Create workflow
$workflow = Workflow::create([
    'name' => 'document-approval',
    'description' => 'Document approval workflow with role-based transitions',
    'type' => 'state_machine',
    'initial_marking' => 'draft',
    'is_enabled' => true,
]);

// Create places
$places = [
    'draft', 'submitted', 'under_review', 'approved', 'rejected'
];

foreach ($places as $index => $place) {
    WorkflowPlace::create([
        'workflow_id' => $workflow->id,
        'name' => $place,
        'sort_order' => $index + 1,
    ]);
}

// Create transitions
$transitions = [
    [
        'name' => 'submit',
        'from_place' => 'draft',
        'to_place' => 'submitted',
        'meta' => ['roles' => ['author']],
    ],
    [
        'name' => 'start_review',
        'from_place' => 'submitted',
        'to_place' => 'under_review',
        'meta' => ['roles' => ['reviewer', 'manager']],
    ],
    [
        'name' => 'approve',
        'from_place' => 'under_review',
        'to_place' => 'approved',
        'meta' => ['roles' => ['manager', 'admin']],
    ],
    [
        'name' => 'reject',
        'from_place' => 'under_review',
        'to_place' => 'rejected',
        'meta' => ['roles' => ['manager', 'admin']],
    ],
    [
        'name' => 'revise',
        'from_place' => 'rejected',
        'to_place' => 'draft',
        'meta' => ['roles' => ['author']],
    ],
];

foreach ($transitions as $index => $transition) {
    WorkflowTransition::create([
        'workflow_id' => $workflow->id,
        'name' => $transition['name'],
        'from_place' => $transition['from_place'],
        'to_place' => $transition['to_place'],
        'sort_order' => $index + 1,
        'meta' => $transition['meta'],
    ]);
}
```

### 4. Set Up Roles and Permissions

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
Role::create(['name' => 'admin']);
Role::create(['name' => 'manager']);
Role::create(['name' => 'reviewer']);
Role::create(['name' => 'author']);

// Assign roles to users
$user = User::find(1);
$user->assignRole('admin');
```

### 5. Add Routes

```php
// routes/web.php
use App\Http\Controllers\DocumentController;

Route::middleware(['auth'])->group(function () {
    Route::resource('documents', DocumentController::class);
    Route::post('documents/{document}/transition', [DocumentController::class, 'transition'])
        ->name('documents.transition');
});
```

## View Templates

Create these Blade templates in `resources/views/documents/`:

### index.blade.php

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Documents</h1>
                <a href="{{ route('documents.create') }}" class="btn btn-primary">Create Document</a>
            </div>

            <!-- Filters -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="filter" class="form-control">
                            <option value="">All Documents</option>
                            <option value="authored" {{ request('filter') === 'authored' ? 'selected' : '' }}>My Documents</option>
                            <option value="reviewing" {{ request('filter') === 'reviewing' ? 'selected' : '' }}>Reviewing</option>
                            <option value="pending_approval" {{ request('filter') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-secondary">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Documents Table -->
            <div class="card">
                <div class="card-body">
                    @if($documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Reviewer</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                        <tr>
                                            <td>
                                                <a href="{{ route('documents.show', $document) }}">
                                                    {{ $document->title }}
                                                </a>
                                            </td>
                                            <td>{{ $document->author->name }}</td>
                                            <td>
                                                <span class="badge badge-{{ $document->status === 'approved' ? 'success' : ($document->status === 'rejected' ? 'danger' : 'secondary') }}">
                                                    {{ str($document->status)->title() }}
                                                </span>
                                            </td>
                                            <td>{{ $document->reviewer?->name ?? '-' }}</td>
                                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                @if($document->isEditable())
                                                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $documents->links() }}
                    @else
                        <p class="text-muted text-center py-4">No documents found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### show.blade.php

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $document->title }}</h5>
                    <span class="badge badge-{{ $document->status === 'approved' ? 'success' : ($document->status === 'rejected' ? 'danger' : 'secondary') }}">
                        {{ str($document->status)->title() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Author:</strong> {{ $document->author->name }}<br>
                        <strong>Created:</strong> {{ $document->created_at->format('M d, Y H:i') }}<br>
                        @if($document->reviewer)
                            <strong>Reviewer:</strong> {{ $document->reviewer->name }}<br>
                        @endif
                        @if($document->approved_at)
                            <strong>Approved:</strong> {{ $document->approved_at->format('M d, Y H:i') }} by {{ $document->approver->name }}<br>
                        @endif
                        @if($document->rejected_reason)
                            <div class="mt-2 p-2 bg-light border-left border-danger">
                                <strong>Rejection Reason:</strong> {{ $document->rejected_reason }}
                            </div>
                        @endif
                    </div>

                    <div class="content">
                        {!! nl2br(e($document->content)) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Workflow Actions -->
            @if($availableTransitions)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Available Actions</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('documents.transition', $document) }}">
                            @csrf

                            @foreach($availableTransitions as $transition => $label)
                                <div class="mb-2">
                                    <button type="submit" name="transition" value="{{ $transition }}"
                                            class="btn btn-sm btn-outline-primary w-100"
                                            onclick="return confirmTransition('{{ $transition }}', '{{ $label }}')">
                                        {{ $label }}
                                    </button>
                                </div>
                            @endforeach

                            <!-- Additional fields for specific transitions -->
                            @if(in_array('reject', array_keys($availableTransitions)))
                                <div class="mt-3">
                                    <label for="rejected_reason">Rejection Reason:</label>
                                    <textarea name="rejected_reason" class="form-control" rows="3"
                                              placeholder="Please provide reason for rejection..."></textarea>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($document->isEditable())
                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-secondary w-100 mb-2">Edit Document</a>
                    @endif

                    @can('delete', $document)
                        <form method="POST" action="{{ route('documents.destroy', $document) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                    onclick="return confirm('Are you sure you want to delete this document?')">
                                Delete Document
                            </button>
                        </form>
                    @endcan

                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary w-100 mt-2">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmTransition(transition, label) {
    return confirm(`Are you sure you want to ${label.toLowerCase()} this document?`);
}
</script>
@endsection
```

## Testing

Create tests for the workflow:

```php
<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'author']);
        Role::create(['name' => 'reviewer']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'admin']);
    }

    public function test_author_can_submit_draft_document()
    {
        $author = User::factory()->create();
        $author->assignRole('author');

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
        $this->assertNotNull($document->fresh()->submitted_at);
    }

    public function test_reviewer_can_start_review_on_submitted_document()
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('reviewer');

        $document = Document::factory()->create(['status' => 'submitted']);

        $this->actingAs($reviewer)
            ->post(route('documents.transition', $document), [
                'transition' => 'start_review'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $document->refresh();
        $this->assertEquals('under_review', $document->status);
        $this->assertEquals($reviewer->id, $document->reviewer_id);
    }

    public function test_manager_can_approve_document_under_review()
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $document = Document::factory()->create(['status' => 'under_review']);

        $this->actingAs($manager)
            ->post(route('documents.transition', $document), [
                'transition' => 'approve'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $document->refresh();
        $this->assertEquals('approved', $document->status);
        $this->assertEquals($manager->id, $document->approved_by);
        $this->assertNotNull($document->approved_at);
    }

    public function test_author_cannot_approve_their_own_document()
    {
        $author = User::factory()->create();
        $author->assignRole('author');

        $document = Document::factory()->create([
            'author_id' => $author->id,
            'status' => 'under_review'
        ]);

        $this->actingAs($author)
            ->post(route('documents.transition', $document), [
                'transition' => 'approve'
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();

        $this->assertEquals('under_review', $document->fresh()->status);
    }

    public function test_rejected_document_can_be_revised()
    {
        $author = User::factory()->create();
        $author->assignRole('author');

        $document = Document::factory()->create([
            'author_id' => $author->id,
            'status' => 'rejected',
            'rejected_reason' => 'Needs more details'
        ]);

        $this->actingAs($author)
            ->post(route('documents.transition', $document), [
                'transition' => 'revise'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('draft', $document->fresh()->status);
    }
}
```

## Usage Examples

### Create a Document

```php
$document = Document::create([
    'title' => 'Project Proposal',
    'content' => 'Detailed project description...',
    'author_id' => auth()->id(),
]);
```

### Check Available Transitions

```php
$transitions = $document->getEnabledToTransitions();
// Returns: ['submitted' => 'Submitted'] for draft documents
```

### Perform Workflow Transition

```php
$workflow = $document->getWorkflow();

if ($workflow->can($document, 'submit')) {
    $workflow->apply($document, 'submit');
    $document->save();
}
```

### Check User Permissions

```php
$requiredRoles = $document->getRolesFromTransition();
$canTransition = auth()->user()->hasAnyRole($requiredRoles);
```

This example provides a complete, production-ready document approval workflow that demonstrates all key features of Flowstone including role-based permissions, state management, and user interface integration.
