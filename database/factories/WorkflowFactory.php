<?php

namespace CleaniqueCoders\Flowstone\Database\Factories;

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowFactory extends Factory
{
    protected $model = Workflow::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => 'state_machine',
            'initial_marking' => Status::DRAFT->value,
            'marking' => Status::DRAFT->value,
            'is_enabled' => true,
            'meta' => [
                'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
                'department' => $this->faker->randomElement(['IT', 'HR', 'Finance']),
            ],
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function withInitialMarking(Status $status): static
    {
        return $this->state(fn (array $attributes) => [
            'initial_marking' => $status->value,
        ]);
    }

    public function withPlacesAndTransitions(): static
    {
        return $this->afterCreating(function (Workflow $workflow) {
            // Create places
            $workflow->places()->createMany([
                [
                    'name' => Status::DRAFT->value,
                    'sort_order' => 1,
                    'meta' => ['title' => 'Draft', 'color' => '#6c757d'],
                ],
                [
                    'name' => Status::PENDING->value,
                    'sort_order' => 2,
                    'meta' => ['title' => 'Pending', 'color' => '#ffc107'],
                ],
                [
                    'name' => Status::IN_PROGRESS->value,
                    'sort_order' => 3,
                    'meta' => ['title' => 'In Progress', 'color' => '#0dcaf0'],
                ],
                [
                    'name' => Status::COMPLETED->value,
                    'sort_order' => 4,
                    'meta' => ['title' => 'Completed', 'color' => '#198754'],
                ],
            ]);

            // Create transitions
            $workflow->transitions()->createMany([
                [
                    'name' => 'submit',
                    'from_place' => Status::DRAFT->value,
                    'to_place' => Status::PENDING->value,
                    'sort_order' => 1,
                    'meta' => ['title' => 'Submit for Review'],
                ],
                [
                    'name' => 'start',
                    'from_place' => Status::PENDING->value,
                    'to_place' => Status::IN_PROGRESS->value,
                    'sort_order' => 2,
                    'meta' => ['title' => 'Start Work'],
                ],
                [
                    'name' => 'complete',
                    'from_place' => Status::IN_PROGRESS->value,
                    'to_place' => Status::COMPLETED->value,
                    'sort_order' => 3,
                    'meta' => ['title' => 'Mark as Complete'],
                ],
            ]);
        });
    }
}
