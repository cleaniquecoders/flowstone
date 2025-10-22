<?php

use App\Models\Workflow;
use App\Processors\Workflow as ProcessorsWorkflow;
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
    function get_workflow_config(string $value, string $field): array
    {
        $metadataField = 'config->metadata->'.$field.'->value';
        $workflow = Workflow::query()
            ->isEnabled()
            ->whereJsonContains(
                $metadataField,
                $value
            )->latest()->first();

        return data_get($workflow, 'config', ProcessorsWorkflow::getDefaultWorkflow());
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
    function roles_exists_in_transitions(array $transitionRoles)
    {
        foreach ($transitionRoles as $key => $roles) {
            foreach ($roles as $role) {
                $roleExists = Role::where('name', $role)->exists();
                if (! $roleExists) {
                    return false; // Return false if any role does not exist
                }
            }
        }
    }
}
