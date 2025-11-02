<?php

namespace CleaniqueCoders\Flowstone\View\Components;

use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class WorkflowTimeline extends Component
{
    public $model;

    public $logs;

    public $limit;

    public $showUser;

    public $showContext;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $model  The workflow model instance
     * @param  int|null  $limit  Maximum number of logs to display (null = all)
     * @param  bool  $showUser  Whether to show user information
     * @param  bool  $showContext  Whether to show transition context
     */
    public function __construct(
        $model,
        ?int $limit = null,
        bool $showUser = true,
        bool $showContext = false
    ) {
        $this->model = $model;
        $this->limit = $limit;
        $this->showUser = $showUser;
        $this->showContext = $showContext;
        $this->logs = $this->getAuditLogs();
    }

    /**
     * Get audit logs for the model
     */
    protected function getAuditLogs(): Collection
    {
        if (! method_exists($this->model, 'getAuditTrail')) {
            return collect();
        }

        $query = $this->model->getAuditTrail();

        if ($this->limit) {
            $query->limit($this->limit);
        }

        return $query->get();
    }

    /**
     * Get timeline item color based on transition
     */
    public function getTimelineColor(WorkflowAuditLog $log): string
    {
        // Color mapping based on common transitions
        $colorMap = [
            'submit' => 'bg-blue-500',
            'approve' => 'bg-green-500',
            'reject' => 'bg-red-500',
            'cancel' => 'bg-gray-500',
            'complete' => 'bg-emerald-500',
            'pause' => 'bg-indigo-500',
            'resume' => 'bg-blue-500',
            'archive' => 'bg-slate-500',
        ];

        return $colorMap[$log->transition] ?? 'bg-blue-500';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('flowstone::components.workflow-timeline');
    }
}
