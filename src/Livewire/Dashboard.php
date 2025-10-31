<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public int $workflows = 0;

    public int $enabled = 0;

    public int $places = 0;

    public int $transitions = 0;

    public function mount(): void
    {
        $this->workflows = Workflow::count();
        $this->enabled = Workflow::isEnabled()->count();
        $this->places = WorkflowPlace::count();
        $this->transitions = WorkflowTransition::count();
    }

    public function render(): View
    {
        return view('flowstone.livewire.dashboard');
    }
}
