<?php

namespace CleaniqueCoders\Flowstone\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorkflowTransitions extends Component
{
    public $model;

    public $transitions;

    public $showBlockers;

    public $buttonClass;

    public $disabledClass;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $model  The workflow model instance
     * @param  bool  $showBlockers  Whether to show blocker messages
     * @param  string  $buttonClass  CSS classes for enabled transition buttons
     * @param  string  $disabledClass  CSS classes for disabled transition buttons
     */
    public function __construct(
        $model,
        bool $showBlockers = true,
        string $buttonClass = 'px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700',
        string $disabledClass = 'px-4 py-2 bg-gray-300 text-gray-500 rounded cursor-not-allowed'
    ) {
        $this->model = $model;
        $this->showBlockers = $showBlockers;
        $this->buttonClass = $buttonClass;
        $this->disabledClass = $disabledClass;
        $this->transitions = $this->getTransitionsData();
    }

    /**
     * Get transitions data with blocker information
     */
    protected function getTransitionsData(): array
    {
        $transitions = workflow_transitions($this->model);
        $data = [];

        foreach ($transitions as $transition) {
            $name = $transition->getName();
            $canApply = workflow_can($this->model, $name);
            $blockers = ! $canApply ? workflow_transition_blockers($this->model, $name) : [];

            $data[] = [
                'name' => $name,
                'transition' => $transition,
                'can_apply' => $canApply,
                'blockers' => $blockers,
                'blocker_messages' => array_map(fn ($blocker) => $blocker->getMessage(), $blockers),
            ];
        }

        return $data;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('flowstone::components.workflow-transitions');
    }
}
