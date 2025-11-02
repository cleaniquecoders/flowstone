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

    /**
     * Check if a transition can be applied without actually applying it.
     *
     * @param  string  $transitionName  The name of the transition to check
     * @return bool True if the transition can be applied, false otherwise
     */
    public function canApplyTransition(string $transitionName): bool
    {
        $blockers = $this->getTransitionBlockers($transitionName);

        return empty($blockers);
    }

    /**
     * Get all blockers preventing a transition from being applied.
     *
     * @param  string  $transitionName  The name of the transition to check
     * @return array Array of TransitionBlocker instances
     */
    public function getTransitionBlockers(string $transitionName): array
    {
        $workflow = $this->getWorkflow();
        $blockers = [];

        // Check if transition exists and is enabled based on current marking
        $enabledTransitions = $this->getEnabledTransitions();
        $transitionEnabled = false;

        foreach ($enabledTransitions as $transition) {
            if ($transition->getName() === $transitionName) {
                $transitionEnabled = true;
                break;
            }
        }

        if (! $transitionEnabled) {
            $blockers[] = \CleaniqueCoders\Flowstone\Guards\TransitionBlocker::createBlockedByMarking(
                "The transition '{$transitionName}' is not available from the current state."
            );

            return $blockers;
        }

        // Check guard conditions from transition metadata
        $guards = $this->getTransitionGuards($transitionName);

        foreach ($guards as $guard) {
            if (! $this->checkGuard($guard)) {
                $blockers[] = $this->createBlockerFromGuard($guard);
            }
        }

        return $blockers;
    }

    /**
     * Get guard conditions for a transition.
     *
     * @param  string  $transitionName  The transition name
     * @return array Array of guard configurations
     */
    protected function getTransitionGuards(string $transitionName): array
    {
        // Get transition configuration
        $config = $this->config ?? [];
        $transitions = $config['transitions'] ?? [];

        if (! isset($transitions[$transitionName])) {
            return [];
        }

        $transition = $transitions[$transitionName];
        $metadata = $transition['metadata'] ?? [];
        $guards = [];

        // Support multiple guard formats
        if (isset($metadata['guard'])) {
            $guard = $metadata['guard'];

            // If it's a string, treat as expression or method name
            if (is_string($guard)) {
                $guards[] = ['type' => 'expression', 'value' => $guard];
            } elseif (is_array($guard)) {
                $guards[] = $guard;
            }
        }

        // Support guards array for multiple conditions
        if (isset($metadata['guards']) && is_array($metadata['guards'])) {
            foreach ($metadata['guards'] as $guard) {
                if (is_string($guard)) {
                    $guards[] = ['type' => 'expression', 'value' => $guard];
                } elseif (is_array($guard)) {
                    $guards[] = $guard;
                }
            }
        }

        // Support role-based guards
        if (isset($metadata['roles']) && is_array($metadata['roles'])) {
            $guards[] = ['type' => 'role', 'value' => $metadata['roles']];
        }

        // Support permission-based guards
        if (isset($metadata['permission'])) {
            $guards[] = ['type' => 'permission', 'value' => $metadata['permission']];
        }

        if (isset($metadata['permissions']) && is_array($metadata['permissions'])) {
            foreach ($metadata['permissions'] as $permission) {
                $guards[] = ['type' => 'permission', 'value' => $permission];
            }
        }

        return $guards;
    }

    /**
     * Check if a guard condition is met.
     *
     * @param  array  $guard  The guard configuration
     * @return bool True if guard passes, false otherwise
     */
    protected function checkGuard(array $guard): bool
    {
        $type = $guard['type'] ?? 'expression';
        $value = $guard['value'] ?? null;

        if ($value === null) {
            return true;
        }

        switch ($type) {
            case 'role':
                return $this->checkRoleGuard($value);

            case 'permission':
                return $this->checkPermissionGuard($value);

            case 'method':
                return $this->checkMethodGuard($value);

            case 'expression':
            default:
                return $this->checkExpressionGuard($value);
        }
    }

    /**
     * Check role-based guard.
     */
    protected function checkRoleGuard(array|string $roles): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if (auth()->user()->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check permission-based guard.
     */
    protected function checkPermissionGuard(string $permission): bool
    {
        if (! auth()->check()) {
            return false;
        }

        // Try common permission checking methods
        $user = auth()->user();

        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }

        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        // Fallback to Laravel's Gate
        return \Illuminate\Support\Facades\Gate::allows($permission, $this);
    }

    /**
     * Check method-based guard (custom method on the model).
     */
    protected function checkMethodGuard(string $method): bool
    {
        if (! method_exists($this, $method)) {
            return false;
        }

        return (bool) $this->{$method}();
    }

    /**
     * Check expression-based guard.
     * For now, this handles simple expressions. Full expression language support will be added later.
     */
    protected function checkExpressionGuard(string $expression): bool
    {
        // Simple expression parsing for common patterns
        $expression = trim($expression);

        // Handle is_granted() expressions
        if (preg_match("/is_granted\(['\"](.*?)['\"]\)/", $expression, $matches)) {
            $permission = $matches[1];

            return $this->checkPermissionGuard($permission);
        }

        // Handle method calls on subject
        if (preg_match('/subject\.(\w+)\(\)/', $expression, $matches)) {
            $method = $matches[1];

            return $this->checkMethodGuard($method);
        }

        // For complex expressions, we'll need the Expression Language component
        // For now, treat as a method name
        if (method_exists($this, $expression)) {
            return $this->checkMethodGuard($expression);
        }

        // Default to true for unrecognized expressions (permissive)
        return true;
    }

    /**
     * Create a blocker from a guard configuration.
     */
    protected function createBlockerFromGuard(array $guard): \CleaniqueCoders\Flowstone\Guards\TransitionBlocker
    {
        $type = $guard['type'] ?? 'expression';
        $value = $guard['value'] ?? '';

        switch ($type) {
            case 'role':
                $roles = is_array($value) ? $value : [$value];

                return \CleaniqueCoders\Flowstone\Guards\TransitionBlocker::createBlockedByRole($roles);

            case 'permission':
                return \CleaniqueCoders\Flowstone\Guards\TransitionBlocker::createBlockedByPermission($value);

            case 'expression':
                return \CleaniqueCoders\Flowstone\Guards\TransitionBlocker::createBlockedByExpressionGuard($value);

            case 'method':
                return \CleaniqueCoders\Flowstone\Guards\TransitionBlocker::createBlockedByCustomGuard(
                    "The guard condition '{$value}' was not met."
                );

            default:
                return \CleaniqueCoders\Flowstone\Guards\TransitionBlocker::createUnknown();
        }
    }

    /**
     * Get a user-friendly list of blocker messages.
     *
     * @param  string  $transitionName  The transition name
     * @return array Array of blocker message strings
     */
    public function getTransitionBlockerMessages(string $transitionName): array
    {
        $blockers = $this->getTransitionBlockers($transitionName);

        return array_map(fn ($blocker) => $blocker->getMessage(), $blockers);
    }

    // ====================================
    // Multiple State Support
    // ====================================

    /**
     * Check if this workflow supports multiple simultaneous states.
     * State machines can only be in one state, workflows can be in multiple.
     */
    public function supportsMultipleStates(): bool
    {
        $config = $this->config ?? [];
        $type = $config['type'] ?? 'state_machine';

        return $type === 'workflow';
    }

    /**
     * Get all current marked places.
     * Returns array of place names that are currently marked.
     */
    public function getMarkedPlaces(): array
    {
        $workflow = $this->getWorkflow();
        $marking = $workflow->getMarking($this);

        return array_keys($marking->getPlaces());
    }

    /**
     * Check if the model is in a specific place.
     */
    public function isInPlace(string $place): bool
    {
        return in_array($place, $this->getMarkedPlaces());
    }

    /**
     * Check if the model is in all specified places.
     */
    public function isInAllPlaces(array $places): bool
    {
        $markedPlaces = $this->getMarkedPlaces();

        foreach ($places as $place) {
            if (! in_array($place, $markedPlaces)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the model is in any of the specified places.
     */
    public function isInAnyPlace(array $places): bool
    {
        $markedPlaces = $this->getMarkedPlaces();

        foreach ($places as $place) {
            if (in_array($place, $markedPlaces)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate that the marking store type matches the workflow type.
     * Should be called during workflow initialization.
     */
    public function validateMarkingStoreType(): bool
    {
        $config = $this->config ?? [];
        $workflowType = $config['type'] ?? 'state_machine';
        $markingStoreType = $config['marking_store']['type'] ?? 'single_state';

        // State machines should use single_state, workflows can use multiple_state
        if ($workflowType === 'state_machine' && $markingStoreType !== 'single_state') {
            throw new \LogicException(
                "State machine workflows must use 'single_state' marking store. Current: {$markingStoreType}"
            );
        }

        return true;
    }

    // ====================================
    // Context Support
    // ====================================

    /**
     * Apply transition with context and return the context along with marking.
     */
    public function applyTransitionWithContext(string $transitionName, array $context = [], ?bool $logTransition = null): array
    {
        $marking = $this->applyTransition($transitionName, $context, $logTransition);

        return [
            'marking' => $marking,
            'context' => $context,
        ];
    }

    /**
     * Get context from the last transition (from audit log).
     */
    public function getLastTransitionContext(): ?array
    {
        $lastLog = $this->auditLogs()->latest()->first();

        return $lastLog?->context;
    }

    /**
     * Get context from a specific transition by name (from audit log).
     */
    public function getTransitionContext(string $transitionName): ?array
    {
        $log = $this->auditLogs()
            ->where('transition', $transitionName)
            ->latest()
            ->first();

        return $log?->context;
    }

    /**
     * Check guard with context support.
     * Passes context to guard methods if they accept it.
     */
    protected function checkGuardWithContext(array $guard, array $context = []): bool
    {
        $type = $guard['type'] ?? 'expression';
        $value = $guard['value'] ?? null;

        if ($value === null) {
            return true;
        }

        // For method guards, try to pass context if the method accepts it
        if ($type === 'method') {
            return $this->checkMethodGuardWithContext($value, $context);
        }

        // For expression guards, make context available
        if ($type === 'expression' && ! empty($context)) {
            return $this->checkExpressionGuardWithContext($value, $context);
        }

        // Fall back to regular guard checking
        return $this->checkGuard($guard);
    }

    /**
     * Check method guard with context parameter.
     */
    protected function checkMethodGuardWithContext(string $method, array $context = []): bool
    {
        if (! method_exists($this, $method)) {
            return false;
        }

        $reflection = new \ReflectionMethod($this, $method);
        $parameters = $reflection->getParameters();

        // If method accepts parameters, pass context
        if (count($parameters) > 0) {
            return (bool) $this->{$method}($context);
        }

        // Otherwise call without parameters
        return (bool) $this->{$method}();
    }

    /**
     * Check expression guard with context available.
     * This is a placeholder for full expression language support.
     */
    protected function checkExpressionGuardWithContext(string $expression, array $context = []): bool
    {
        // For now, context is stored but not used in expression evaluation
        // Full Expression Language support will make context available in expressions
        return $this->checkExpressionGuard($expression);
    }

    // ====================================
    // Metadata Support
    // ====================================

    /**
     * Get metadata from the workflow, place, or transition.
     *
     * @param  string|null  $key  The metadata key to retrieve (null to get all)
     * @param  string  $type  'workflow', 'place', or 'transition'
     * @param  string|null  $name  The place or transition name (required for place/transition)
     * @return mixed The metadata value or array
     */
    public function getMetadata(?string $key = null, string $type = 'workflow', ?string $name = null): mixed
    {
        $workflow = $this->getWorkflow();
        $metadataStore = $workflow->getMetadataStore();

        $metadata = match ($type) {
            'workflow' => $metadataStore->getWorkflowMetadata(),
            'place' => $name ? $metadataStore->getPlaceMetadata($name) : [],
            'transition' => $name ? $this->getTransitionMetadataFromStore($name, $metadataStore) : [],
            default => [],
        };

        return $key ? ($metadata[$key] ?? null) : $metadata;
    }

    /**
     * Get workflow-level metadata.
     */
    public function getWorkflowMetadata(?string $key = null): mixed
    {
        return $this->getMetadata($key, 'workflow');
    }

    /**
     * Get place-level metadata.
     */
    public function getPlaceMetadata(string $place, ?string $key = null): mixed
    {
        return $this->getMetadata($key, 'place', $place);
    }

    /**
     * Get transition-level metadata.
     */
    public function getTransitionMetadata(string $transition, ?string $key = null): mixed
    {
        return $this->getMetadata($key, 'transition', $transition);
    }

    /**
     * Helper to get transition metadata from the metadata store.
     */
    protected function getTransitionMetadataFromStore(string $transitionName, $metadataStore): array
    {
        $workflow = $this->getWorkflow();
        $definition = $workflow->getDefinition();

        foreach ($definition->getTransitions() as $transition) {
            if ($transition->getName() === $transitionName) {
                return $metadataStore->getTransitionMetadata($transition);
            }
        }

        return [];
    }

    /**
     * Get all available places with their metadata.
     */
    public function getPlacesWithMetadata(): array
    {
        $workflow = $this->getWorkflow();
        $definition = $workflow->getDefinition();
        $places = [];

        foreach ($definition->getPlaces() as $place) {
            $places[$place] = $this->getPlaceMetadata($place);
        }

        return $places;
    }

    /**
     * Get all available transitions with their metadata.
     */
    public function getTransitionsWithMetadata(): array
    {
        $workflow = $this->getWorkflow();
        $definition = $workflow->getDefinition();
        $transitions = [];

        foreach ($definition->getTransitions() as $transition) {
            $transitions[$transition->getName()] = $this->getTransitionMetadata($transition->getName());
        }

        return $transitions;
    }
}
