<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Processors\WorkflowGraphBuilder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WorkflowShow extends Component
{
    public int $workflowId;

    public array $graph = [];

    public function mount(Workflow $workflow, WorkflowGraphBuilder $builder): void
    {
        $this->workflowId = $workflow->id ?? $this->workflowId;
        $this->graph = $builder->build($workflow);
    }

    public function refreshGraph(WorkflowGraphBuilder $builder): void
    {
        $workflow = Workflow::with(['places', 'transitions'])->findOrFail($this->workflowId);
        $this->graph = $builder->build($workflow);
        $this->dispatch('flowstone:graph:update', graph: $this->graph);
    }

    public function render(): View
    {
        return view('flowstone.livewire.workflow-show');
    }
}
