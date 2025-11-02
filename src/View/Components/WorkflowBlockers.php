<?php

namespace CleaniqueCoders\Flowstone\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorkflowBlockers extends Component
{
    public $model;

    public $transition;

    public $blockers;

    public $messages;

    public $showIcon;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $model  The workflow model instance
     * @param  string  $transition  The transition name to check blockers for
     * @param  bool  $showIcon  Whether to show warning icon
     */
    public function __construct(
        $model,
        string $transition,
        bool $showIcon = true
    ) {
        $this->model = $model;
        $this->transition = $transition;
        $this->showIcon = $showIcon;
        $this->blockers = workflow_transition_blockers($model, $transition);
        $this->messages = array_map(fn ($blocker) => $blocker->getMessage(), $this->blockers);
    }

    /**
     * Check if there are any blockers
     */
    public function hasBlockers(): bool
    {
        return count($this->blockers) > 0;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('flowstone::components.workflow-blockers');
    }
}
