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
            'type' => $this->faker->randomElement(['article', 'task', 'order', 'project']),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'config' => [
                'type' => 'state_machine',
                'supports' => [Workflow::class],
                'places' => [
                    Status::DRAFT->value => null,
                    Status::PENDING->value => null,
                    Status::IN_PROGRESS->value => null,
                    Status::COMPLETED->value => null,
                ],
                'transitions' => [
                    'submit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                    ],
                    'start' => [
                        'from' => [Status::PENDING->value],
                        'to' => Status::IN_PROGRESS->value,
                    ],
                    'complete' => [
                        'from' => [Status::IN_PROGRESS->value],
                        'to' => Status::COMPLETED->value,
                    ],
                ],
                'marking_store' => [
                    'property' => 'marking',
                ],
                'metadata' => [
                    'type' => [
                        'value' => 'test-workflow',
                    ],
                ],
            ],
            'marking' => Status::DRAFT->value,
            'workflow' => null, // Will be set by the trait
            'is_enabled' => true,
            'created_by' => [
                'id' => $this->faker->numberBetween(1, 100),
                'name' => $this->faker->name(),
            ],
            'updated_by' => [
                'id' => $this->faker->numberBetween(1, 100),
                'name' => $this->faker->name(),
            ],
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

    public function withMarking(Status $status): static
    {
        return $this->state(fn (array $attributes) => [
            'marking' => $status->value,
        ]);
    }
}
