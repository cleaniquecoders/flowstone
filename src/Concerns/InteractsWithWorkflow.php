<?php

namespace CleaniqueCoders\Flowstone\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Workflow\Workflow;

trait InteractsWithWorkflow
{
    public function setWorkflow(): self
    {
        if (! empty(data_get($this, 'config'))) {
            return $this;
        }

        $this->update([
            'config' => get_workflow_config(
                $this->workflow_type,
                $this->workflow_type_field
            ),
        ]);

        $this->refresh();

        return $this;
    }

    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type,
        );
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(
            get: fn () => 'type',
        );
    }

    public function getWorkflow(): Workflow
    {
        if (empty($this->config)) {
            $this->setWorkflow();
        }

        return Cache::remember(
            $this->getWorkflowKey(),
            config('app.debug') ? 3 : config('cache.duration'), function () {
                return create_workflow($this->config);
            });
    }

    public function getWorkflowKey(): string
    {
        $baseKey = str(get_called_class())->lower()->replace('\\', '-')->toString();

        if (method_exists($this, 'getKeyName') && $this->{$this->getKeyName()}) {
            return $baseKey.'-'.$this->{$this->getKeyName()};
        }

        return $baseKey;
    }

    public function getMarking(): string
    {
        return $this->marking;
    }

    public function getEnabledTransitions(): array
    {
        return $this->getWorkflow()->getEnabledTransitions($this);
    }

    public function getEnabledToTransitions(): array
    {
        $trans = [];
        foreach ($this->getEnabledTransitions() as $transition) {
            $tos = $transition->getTos();
            $to = array_pop($tos);
            $trans[$to] = str($to)->headline()->toString();
        }

        return $trans;
    }

    public function hasEnabledToTransitions(): bool
    {
        return ! empty($this->getEnabledToTransitions());
    }

    public function getRolesFromTransition($marking = null, $type = 'to'): array
    {
        return get_roles_from_transition(
            $this->config ?? [],
            empty($marking) ? $this->getMarking() : $marking,
            $type
        );
    }

    public function getAllEnabledTransitionRoles(): array
    {
        $roles = [];
        foreach ($this->getEnabledToTransitions() as $key => $value) {
            $roles[$key] = $this->getRolesFromTransition($key);
        }

        return $roles;
    }

    /**
     * Apply a workflow transition with optional audit logging.
     *
     * @param  string  $transitionName  The name of the transition to apply
     * @param  array  $context  Additional context to pass to the transition
     * @param  bool  $logTransition  Whether to log this transition (defaults to workflow's audit_trail_enabled)
     *
     * @throws \Symfony\Component\Workflow\Exception\LogicException
     */
    public function applyTransition(string $transitionName, array $context = [], ?bool $logTransition = null): \Symfony\Component\Workflow\Marking
    {
        $workflow = $this->getWorkflow();
        $fromPlace = $this->getMarking();

        // Apply the transition
        $marking = $workflow->apply($this, $transitionName, $context);

        // Determine if we should log (check workflow setting if not explicitly set)
        $shouldLog = $logTransition ?? $this->shouldLogAuditTrail();

        if ($shouldLog) {
            $this->logWorkflowTransition($transitionName, $fromPlace, $marking->getPlaces(), $context);
        }

        return $marking;
    }

    /**
     * Determine if audit trail should be logged for this workflow.
     */
    protected function shouldLogAuditTrail(): bool
    {
        // If this model is a Workflow model itself, check its audit_trail_enabled field
        if ($this instanceof \CleaniqueCoders\Flowstone\Models\Workflow) {
            return $this->audit_trail_enabled ?? false;
        }

        // For other models, find the workflow configuration and check its setting
        $workflowConfig = \CleaniqueCoders\Flowstone\Models\Workflow::where('name', $this->workflow_type)
            ->orWhere('type', $this->workflow_type)
            ->first();

        return $workflowConfig?->audit_trail_enabled ?? false;
    }

    /**
     * Log a workflow transition to the audit trail.
     */
    protected function logWorkflowTransition(string $transitionName, string $fromPlace, array $toPlaces, array $context = []): void
    {
        $toPlace = array_key_first($toPlaces) ?? null;

        if (! $toPlace) {
            return;
        }

        // Find the workflow record
        $workflow = \CleaniqueCoders\Flowstone\Models\Workflow::where('name', $this->workflow_type)
            ->orWhere('type', $this->workflow_type)
            ->first();

        \CleaniqueCoders\Flowstone\Models\WorkflowAuditLog::create([
            'workflow_id' => $workflow?->id,
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'from_place' => $fromPlace,
            'to_place' => $toPlace,
            'transition' => $transitionName,
            'user_id' => auth()->id(),
            'context' => $context,
            'metadata' => [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toIso8601String(),
            ],
            'created_at' => now(),
        ]);
    }

    /**
     * Get the audit trail for this model.
     */
    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(
            \CleaniqueCoders\Flowstone\Models\WorkflowAuditLog::class,
            'subject'
        );
    }

    /**
     * Get the audit trail for this model.
     *
     * @param  int|null  $limit  Limit the number of records
     */
    public function getAuditTrail(?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->auditLogs()->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get recent audit logs for this model.
     */
    public function recentAuditLogs(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getAuditTrail($limit);
    }

    /**
     * Check if this model has any audit logs.
     */
    public function hasAuditLogs(): bool
    {
        return $this->auditLogs()->exists();
    }
}
