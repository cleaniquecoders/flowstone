<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('flowstone::components.layout')]
class WorkflowIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $type = null;

    public ?string $enabled = null;

    protected $queryString = ['search', 'type', 'enabled'];

    public function updating($name, $value): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Workflow::query()->withCount(['places', 'transitions']);

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->enabled !== null && $this->enabled !== '') {
            $query->where('is_enabled', (bool) $this->enabled);
        }

        return view('flowstone::livewire.workflow-index', [
            'workflows' => $query->orderBy('name')->paginate(10),
        ]);
    }
}
