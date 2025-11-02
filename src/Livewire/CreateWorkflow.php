<?php

namespace CleaniqueCoders\Flowstone\Livewire;

use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('flowstone::components.layout')]
class CreateWorkflow extends Component
{
    public bool $showModal = false;

    public string $name = '';

    public string $description = '';

    public string $group = '';

    public string $category = '';

    public string $tags = '';

    public string $type = 'workflow';

    public bool $audit_trail_enabled = false;

    public string $marking_store_type = 'method';

    public string $marking_store_property = 'marking';

    public array $types = [
        'workflow' => 'Workflow - Can be in multiple places simultaneously, requires all previous places for transitions',
        'state_machine' => 'State Machine - Can only be in one place at a time, requires at least one previous place for transitions',
    ];

    public array $markingStoreTypes = [
        'method' => 'Method - Standard getter/setter approach (recommended)',
        'single_state' => 'Single State - Explicit single state for state machines',
        'multiple_state' => 'Multiple State - Multiple simultaneous states for workflows',
    ];

    protected array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:1000',
        'group' => 'nullable|string|max:255',
        'category' => 'nullable|string|max:255',
        'tags' => 'nullable|string',
        'type' => 'required|in:workflow,state_machine',
        'audit_trail_enabled' => 'boolean',
        'marking_store_type' => 'required|in:method,single_state,multiple_state',
        'marking_store_property' => 'required|string|max:255',
    ];

    protected array $messages = [
        'name.required' => 'Please provide a name for the workflow.',
        'description.required' => 'Please provide a description for the workflow.',
        'type.required' => 'Please select a workflow type.',
        'type.in' => 'Invalid workflow type selected.',
    ];

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function getListeners(): array
    {
        return [
            'open-create-modal' => 'openModal',
        ];
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->group = '';
        $this->category = '';
        $this->tags = '';
        $this->type = 'workflow';
        $this->audit_trail_enabled = false;
        $this->marking_store_type = 'method';
        $this->marking_store_property = 'marking';
        $this->resetValidation();
    }

    public function updatedType($value): void
    {
        // Auto-set marking store type based on workflow type
        if ($value === 'state_machine') {
            $this->marking_store_type = 'single_state';
        } elseif ($value === 'workflow') {
            $this->marking_store_type = 'multiple_state';
        }
    }

    public function create(): void
    {
        $this->validate();

        $workflow = Workflow::create([
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group ?: null,
            'category' => $this->category ?: null,
            'tags' => $this->tags ? array_map('trim', explode(',', $this->tags)) : null,
            'type' => $this->type,
            'marking_store_type' => $this->marking_store_type,
            'marking_store_property' => $this->marking_store_property,
            'config' => $this->getDefaultConfig(),
            'is_enabled' => true,
            'audit_trail_enabled' => $this->audit_trail_enabled,
        ]);

        $this->closeModal();

        // Redirect to designer
        redirect()->route('flowstone.workflows.designer', $workflow)
            ->with('success', 'Workflow created successfully! You can now design its places and transitions.');
    }

    private function getDefaultConfig(): array
    {
        if ($this->type === 'state_machine') {
            return [
                'type' => 'state_machine',
                'places' => [
                    'draft' => ['label' => 'Draft'],
                    'published' => ['label' => 'Published'],
                ],
                'transitions' => [
                    'publish' => [
                        'from' => ['draft'],
                        'to' => 'published',
                        'label' => 'Publish',
                    ],
                ],
                'metadata' => [
                    'initial_marking' => 'draft',
                ],
            ];
        }

        return [
            'type' => 'workflow',
            'places' => [
                'draft' => ['label' => 'Draft'],
                'review' => ['label' => 'Under Review'],
                'approved' => ['label' => 'Approved'],
                'published' => ['label' => 'Published'],
            ],
            'transitions' => [
                'submit_for_review' => [
                    'from' => ['draft'],
                    'to' => 'review',
                    'label' => 'Submit for Review',
                ],
                'approve' => [
                    'from' => ['review'],
                    'to' => 'approved',
                    'label' => 'Approve',
                ],
                'publish' => [
                    'from' => ['approved'],
                    'to' => 'published',
                    'label' => 'Publish',
                ],
            ],
            'metadata' => [
                'initial_marking' => ['draft'],
            ],
        ];
    }

    public function render(): View
    {
        return view('flowstone::livewire.create-workflow');
    }
}
