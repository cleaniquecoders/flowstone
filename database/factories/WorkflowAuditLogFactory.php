<?php

namespace CleaniqueCoders\Flowstone\Database\Factories;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowAuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowAuditLogFactory extends Factory
{
    protected $model = WorkflowAuditLog::class;

    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'subject_type' => 'App\\Models\\Document',
            'subject_id' => $this->faker->numberBetween(1, 100),
            'from_place' => 'draft',
            'to_place' => 'review',
            'transition' => 'submit',
            'user_id' => null,
            'context' => [
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
            ],
            'metadata' => [
                'notes' => $this->faker->sentence(),
            ],
            'created_at' => now(),
        ];
    }

    public function forWorkflow(Workflow $workflow): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_id' => $workflow->id,
        ]);
    }

    public function withUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    public function forTransition(string $from, string $to, string $transition): static
    {
        return $this->state(fn (array $attributes) => [
            'from_place' => $from,
            'to_place' => $to,
            'transition' => $transition,
        ]);
    }
}
