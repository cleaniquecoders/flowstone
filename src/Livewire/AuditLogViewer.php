<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read \Illuminate\Contracts\Pagination\LengthAwarePaginator $auditLogs
 */
class AuditLogViewer extends Component
{
    use WithPagination;

    public $workflowId = null;

    public $subjectType = null;

    public $subjectId = null;

    public $userId = null;

    public $transition = null;

    public $place = null;

    public $startDate = null;

    public $endDate = null;

    public $perPage = 20;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    protected $queryString = [
        'workflowId' => ['except' => null],
        'subjectType' => ['except' => null],
        'subjectId' => ['except' => null],
        'userId' => ['except' => null],
        'transition' => ['except' => null],
        'place' => ['except' => null],
        'startDate' => ['except' => null],
        'endDate' => ['except' => null],
        'perPage' => ['except' => 20],
    ];

    public function mount(
        ?int $workflowId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?int $userId = null
    ): void {
        $this->workflowId = $workflowId;
        $this->subjectType = $subjectType;
        $this->subjectId = $subjectId;
        $this->userId = $userId;
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingWorkflowId(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectType(): void
    {
        $this->resetPage();
    }

    public function updatingSubjectId(): void
    {
        $this->resetPage();
    }

    public function updatingUserId(): void
    {
        $this->resetPage();
    }

    public function updatingTransition(): void
    {
        $this->resetPage();
    }

    public function updatingPlace(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'workflowId',
            'subjectType',
            'subjectId',
            'userId',
            'transition',
            'place',
            'startDate',
            'endDate',
        ]);

        $this->resetPage();
    }

    public function getAuditLogsProperty()
    {
        $query = WorkflowAuditLog::query()
            ->with(['workflow', 'subject', 'user']);

        // Apply filters
        if ($this->workflowId) {
            $query->forWorkflow($this->workflowId);
        }

        if ($this->subjectType && $this->subjectId) {
            $query->forSubject($this->subjectType, $this->subjectId);
        }

        if ($this->userId) {
            $query->byUser($this->userId);
        }

        if ($this->transition) {
            $query->byTransition($this->transition);
        }

        if ($this->place) {
            $query->byPlace($this->place);
        }

        if ($this->startDate && $this->endDate) {
            $query->inDateRange($this->startDate, $this->endDate);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('flowstone::livewire.audit-log-viewer', [
            'auditLogs' => $this->auditLogs,
        ]);
    }
}
