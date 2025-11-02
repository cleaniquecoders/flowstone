<?php

namespace CleaniqueCoders\Flowstone\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorkflowStatus extends Component
{
    public $model;

    public $places;

    public $badgeClass;

    public $showLabel;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $model  The workflow model instance
     * @param  bool  $showLabel  Whether to show "Status:" label
     * @param  string  $badgeClass  CSS classes for status badges
     */
    public function __construct(
        $model,
        bool $showLabel = true,
        string $badgeClass = 'px-3 py-1 text-sm font-medium rounded-full'
    ) {
        $this->model = $model;
        $this->showLabel = $showLabel;
        $this->badgeClass = $badgeClass;
        $this->places = workflow_marked_places($model);
    }

    /**
     * Get badge color classes based on place name
     */
    public function getBadgeColor(string $place): string
    {
        // Common workflow state color mapping
        $colorMap = [
            'draft' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'under_review' => 'bg-purple-100 text-purple-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'on_hold' => 'bg-orange-100 text-orange-800',
            'cancelled' => 'bg-gray-100 text-gray-800',
            'completed' => 'bg-emerald-100 text-emerald-800',
            'failed' => 'bg-red-100 text-red-800',
            'paused' => 'bg-indigo-100 text-indigo-800',
            'archived' => 'bg-slate-100 text-slate-800',
        ];

        return $colorMap[$place] ?? 'bg-blue-100 text-blue-800';
    }

    /**
     * Format place name for display
     */
    public function formatPlace(string $place): string
    {
        return ucwords(str_replace('_', ' ', $place));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('flowstone::components.workflow-status');
    }
}
