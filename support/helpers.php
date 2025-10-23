<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Processors\Workflow as ProcessorsWorkflow;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

if (! function_exists('create_workflow')) {
    function create_workflow(array $configuration, ?Registry $registry = null): SymfonyWorkflow
    {
        if (empty($registry)) {
            $registry = app(Registry::class);
        }

        return ProcessorsWorkflow::createWorkflow($configuration, $registry);
    }
}

if (! function_exists('get_workflow_config')) {
    function get_workflow_config(string $name, string $field = 'name'): array
    {
        return Cache::remember(
            "workflow.config.{$name}",
            3600,
            function () use ($name, $field) {
                $workflow = Workflow::query()
                    ->isEnabled()
                    ->where($field, $name)
                    ->with(['places', 'transitions'])
                    ->latest()
                    ->first();

                return $workflow ? $workflow->config : ProcessorsWorkflow::getDefaultWorkflow();
            }
        );
    }
}

/**
 * Get roles can use in `to` transition (by default).
 * This helper mainly to get all roles that can go `to` given place.
 * Alternatively, this helper also can get roles that transition coming `from` other place.
 */
if (! function_exists('get_roles_from_marking')) {
    function get_roles_from_transition(array $workflow, string $marking, string $type = 'to'): array
    {
        // Convert the transitions array into a collection for easier manipulation
        $transitions = collect(data_get($workflow, 'transitions', []));

        // Filter the transitions to get only those starting from the specified marking
        $filteredTransitions = $transitions->filter(function ($transition) use ($marking, $type) {
            $data = data_get($transition, $type);

            return is_array($data) ? in_array($marking, $data) : $marking == $data;
        });

        // Extract all roles involved in the filtered transitions
        $roles = $filteredTransitions->flatMap(function ($transition) {
            return data_get($transition, 'metadata.role');
        })->unique()->values();

        // Return the roles as an array
        return $roles->isEmpty() ? [] : $roles->toArray();
    }
}

if (! function_exists('roles_exists_in_transitions')) {
    /**
     * Check if roles exist in the system.
     * Note: This function requires a Role model to be implemented in the application.
     */
    function roles_exists_in_transitions(array $transitionRoles): bool
    {
        // This function should be implemented by the application
        // as it requires knowledge of the Role model structure
        return true;
    }
}
