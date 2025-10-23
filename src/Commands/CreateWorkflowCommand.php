<?php

namespace CleaniqueCoders\Flowstone\Commands;

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Console\Command;

class CreateWorkflowCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flowstone:create-workflow
                          {name : The name of the workflow}
                          {--type=state_machine : The type of workflow (state_machine or workflow)}
                          {--initial=draft : The initial marking}
                          {--description= : Optional description}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new workflow configuration in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $type = $this->option('type');
        $initialMarking = $this->option('initial');
        $description = $this->option('description') ?? "Workflow configuration for {$name}";

        // Validate type
        if (! in_array($type, ['state_machine', 'workflow'])) {
            $this->error('Invalid workflow type. Must be either "state_machine" or "workflow".');

            return self::FAILURE;
        }

        // Create workflow
        $workflow = Workflow::create([
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'initial_marking' => $initialMarking,
            'is_enabled' => true,
        ]);

        $this->info("Created workflow '{$name}' with ID: {$workflow->id}");

        // Ask if user wants to add default places and transitions
        if ($this->confirm('Would you like to add default places and transitions?')) {
            $this->addDefaultPlacesAndTransitions($workflow);
        }

        $this->info('Workflow created successfully!');

        return self::SUCCESS;
    }

    private function addDefaultPlacesAndTransitions(Workflow $workflow): void
    {
        // Create default places
        $places = [
            [
                'name' => Status::DRAFT->value,
                'sort_order' => 1,
                'metadata' => [
                    'title' => 'Draft',
                    'description' => 'Initial state for new items',
                ],
            ],
            [
                'name' => Status::PENDING->value,
                'sort_order' => 2,
                'metadata' => [
                    'title' => 'Pending Review',
                    'description' => 'Waiting for approval',
                ],
            ],
            [
                'name' => Status::IN_PROGRESS->value,
                'sort_order' => 3,
                'metadata' => [
                    'title' => 'In Progress',
                    'description' => 'Currently being worked on',
                ],
            ],
            [
                'name' => Status::COMPLETED->value,
                'sort_order' => 4,
                'metadata' => [
                    'title' => 'Completed',
                    'description' => 'Work has been finished',
                ],
            ],
        ];

        foreach ($places as $place) {
            $workflow->places()->create($place);
        }

        // Create default transitions
        $transitions = [
            [
                'name' => 'submit',
                'from_place' => Status::DRAFT->value,
                'to_place' => Status::PENDING->value,
                'sort_order' => 1,
                'metadata' => [
                    'title' => 'Submit for Review',
                    'description' => 'Submit item for review',
                ],
            ],
            [
                'name' => 'start',
                'from_place' => Status::PENDING->value,
                'to_place' => Status::IN_PROGRESS->value,
                'sort_order' => 2,
                'metadata' => [
                    'title' => 'Start Work',
                    'description' => 'Begin working on the item',
                ],
            ],
            [
                'name' => 'complete',
                'from_place' => Status::IN_PROGRESS->value,
                'to_place' => Status::COMPLETED->value,
                'sort_order' => 3,
                'metadata' => [
                    'title' => 'Complete',
                    'description' => 'Mark item as complete',
                ],
            ],
        ];

        foreach ($transitions as $transition) {
            $workflow->transitions()->create($transition);
        }

        $this->info('Added 4 places and 3 transitions to the workflow.');
    }
}
