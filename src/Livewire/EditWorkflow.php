<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('flowstone::components.layout')]
class EditWorkflow extends Component
{
    public Workflow $workflow;

    public bool $showModal = false;

    public string $name = '';

    public string $description = '';

    public string $group = '';

    public string $category = '';

    public string $tags = '';

    public string $type = 'state_machine';

    public bool $is_enabled = true;

    public array $types = [
        'state_machine' => 'State Machine - Only one state at a time',
        'workflow' => 'Workflow - Multiple states simultaneously',
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'group' => 'nullable|string|max:255',
        'category' => 'nullable|string|max:255',
        'tags' => 'nullable|string',
        'type' => 'required|in:state_machine,workflow',
        'is_enabled' => 'boolean',
    ];

    public function mount(Workflow $workflow): void
    {
        $this->workflow = $workflow;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->name = $this->workflow->name;
        $this->description = $this->workflow->description ?? '';
        $this->group = $this->workflow->group ?? '';
        $this->category = $this->workflow->category ?? '';
        $this->tags = is_array($this->workflow->tags) ? implode(', ', $this->workflow->tags) : '';
        $this->type = $this->workflow->type;
        $this->is_enabled = $this->workflow->is_enabled;
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function update(): void
    {
        $this->validate();

        $this->workflow->update([
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group ?: null,
            'category' => $this->category ?: null,
            'tags' => $this->tags ? array_map('trim', explode(',', $this->tags)) : null,
            'type' => $this->type,
            'is_enabled' => $this->is_enabled,
        ]);

        $this->closeModal();

        session()->flash('success', 'Workflow updated successfully!');

        // Refresh the parent component
        $this->dispatch('workflow-updated');
    }

    protected $listeners = [
        'open-edit-modal' => 'handleOpenEditModal',
    ];

    public function handleOpenEditModal($workflowId): void
    {
        if ((int) $workflowId === $this->workflow->id) {
            $this->openModal();
        }
    }

    public function render(): View
    {
        return view('flowstone::livewire.edit-workflow');
    }
}
