<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('flowstone::components.layout')]
class ManageWorkflowMetadata extends Component
{
    public Workflow $workflow;

    public bool $showModal = false;

    public array $metadata = [];

    public string $editingKey = '';

    public string $key = '';

    public string $value = '';

    public string $type = 'string';

    public bool $isEditing = false;

    public array $types = [
        'string' => 'String',
        'text' => 'Text',
        'integer' => 'Integer',
        'numeric' => 'Numeric',
        'boolean' => 'Boolean',
        'date' => 'Date',
        'datetime' => 'Date & Time',
        'array' => 'Array (JSON)',
    ];

    public function mount(Workflow $workflow): void
    {
        $this->workflow = $workflow;
        $this->loadMetadata();
    }

    public function loadMetadata(): void
    {
        $this->metadata = $this->workflow->meta ?? [];
    }

    #[On('open-metadata-modal')]
    public function openModal(): void
    {
        $this->loadMetadata();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->key = '';
        $this->value = '';
        $this->type = 'string';
        $this->isEditing = false;
        $this->editingKey = '';
        $this->resetValidation();
    }

    public function addMetadata(): void
    {
        $this->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
            'type' => 'required|in:'.implode(',', array_keys($this->types)),
        ]);

        // Convert value based on type
        $convertedValue = $this->convertValueByType($this->value, $this->type);

        if ($this->isEditing) {
            // Remove old key if it's different
            if ($this->editingKey !== $this->key) {
                unset($this->metadata[$this->editingKey]);
            }
        }

        // Check if key already exists (and not editing the same key)
        if (isset($this->metadata[$this->key]) && ! $this->isEditing) {
            $this->addError('key', 'This key already exists.');

            return;
        }

        // Add or update metadata
        $this->metadata[$this->key] = [
            'value' => $convertedValue,
            'type' => $this->type,
        ];

        // Save to database
        $this->workflow->update([
            'meta' => $this->metadata,
        ]);

        $this->resetForm();
        $this->loadMetadata();

        session()->flash('success', $this->isEditing ? 'Metadata updated successfully!' : 'Metadata added successfully!');

        $this->dispatch('workflow-updated');
    }

    public function editMetadata(string $key): void
    {
        if (! isset($this->metadata[$key])) {
            return;
        }

        $this->isEditing = true;
        $this->editingKey = $key;
        $this->key = $key;

        $metaItem = $this->metadata[$key];
        $this->type = $metaItem['type'] ?? 'string';
        $this->value = $this->formatValueForEdit($metaItem['value'], $this->type);
    }

    public function deleteMetadata(string $key): void
    {
        unset($this->metadata[$key]);

        $this->workflow->update([
            'meta' => $this->metadata,
        ]);

        $this->loadMetadata();

        session()->flash('success', 'Metadata deleted successfully!');

        $this->dispatch('workflow-updated');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    protected function convertValueByType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'numeric' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array' => json_decode($value, true) ?? [],
            'date', 'datetime' => $value,
            default => (string) $value,
        };
    }

    protected function formatValueForEdit(mixed $value, string $type): string
    {
        return match ($type) {
            'array' => json_encode($value, JSON_PRETTY_PRINT),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    public function render(): View
    {
        return view('flowstone::livewire.manage-workflow-metadata');
    }
}
