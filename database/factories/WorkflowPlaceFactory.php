<?php

namespace CleaniqueCoders\Flowstone\Database\Factories;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\CleaniqueCoders\Flowstone\Models\WorkflowPlace>
 */
class WorkflowPlaceFactory extends Factory
{
    protected $model = WorkflowPlace::class;

    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'name' => $this->faker->randomElement(['draft', 'pending', 'approved', 'rejected', 'completed']),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'meta' => [
                'title' => $this->faker->words(2, true),
                'description' => $this->faker->sentence(),
            ],
        ];
    }
}
