<?php

namespace CleaniqueCoders\Flowstone\Database\Factories;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\CleaniqueCoders\Flowstone\Models\WorkflowTransition>
 */
class WorkflowTransitionFactory extends Factory
{
    protected $model = WorkflowTransition::class;

    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'name' => $this->faker->randomElement(['submit', 'approve', 'reject', 'complete', 'cancel']),
            'from_place' => 'draft',
            'to_place' => 'pending',
            'sort_order' => $this->faker->numberBetween(1, 10),
            'meta' => [
                'title' => $this->faker->words(2, true),
                'description' => $this->faker->sentence(),
                'role' => $this->faker->randomElements(['admin', 'user', 'manager'], $this->faker->numberBetween(1, 2)),
            ],
        ];
    }
}
