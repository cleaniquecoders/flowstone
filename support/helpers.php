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

if (! function_exists('workflow_can')) {
    /**
     * Check if a transition can be applied to the model.
     */
    function workflow_can($model, string $transition): bool
    {
        if (! method_exists($model, 'canApplyTransition')) {
            return false;
        }

        return $model->canApplyTransition($transition);
    }
}

if (! function_exists('workflow_transitions')) {
    /**
     * Get all enabled transitions for the model.
     */
    function workflow_transitions($model): array
    {
        if (! method_exists($model, 'getEnabledTransitions')) {
            return [];
        }

        return $model->getEnabledTransitions();
    }
}

if (! function_exists('workflow_transition')) {
    /**
     * Get a specific transition by name.
     */
    function workflow_transition($model, string $name): ?object
    {
        $transitions = workflow_transitions($model);

        foreach ($transitions as $transition) {
            if ($transition->getName() === $name) {
                return $transition;
            }
        }

        return null;
    }
}

if (! function_exists('workflow_marked_places')) {
    /**
     * Get the current marked places for the model.
     */
    function workflow_marked_places($model): array
    {
        if (! method_exists($model, 'getMarking')) {
            return [];
        }

        $marking = $model->getMarking();

        return $marking ? $marking->getPlaces() : [];
    }
}

if (! function_exists('workflow_has_marked_place')) {
    /**
     * Check if the model has a specific marked place.
     */
    function workflow_has_marked_place($model, string $place): bool
    {
        $places = workflow_marked_places($model);

        return isset($places[$place]);
    }
}

if (! function_exists('workflow_transition_blockers')) {
    /**
     * Get blockers for a specific transition.
     */
    function workflow_transition_blockers($model, string $transition): array
    {
        if (! method_exists($model, 'getTransitionBlockers')) {
            return [];
        }

        return $model->getTransitionBlockers($transition);
    }
}

if (! function_exists('workflow_metadata')) {
    /**
     * Get metadata for workflow, place, or transition.
     *
     * @param  mixed  $model  The workflow model
     * @param  string  $key  The metadata key to retrieve
     * @param  string  $subject  The subject type: 'workflow', 'place', or 'transition'
     * @param  string|null  $subjectName  The name of the place or transition (required for 'place' and 'transition')
     */
    function workflow_metadata($model, string $key, string $subject = 'workflow', ?string $subjectName = null): mixed
    {
        if (! method_exists($model, 'getWorkflow')) {
            return null;
        }

        $workflow = $model->getWorkflow();
        $metadataStore = $workflow->getMetadataStore();

        return match ($subject) {
            'workflow' => $metadataStore->getWorkflowMetadata()[$key] ?? null,
            'place' => $subjectName ? ($metadataStore->getPlaceMetadata($subjectName)[$key] ?? null) : null,
            'transition' => $subjectName ? ($metadataStore->getTransitionMetadata($workflow->getDefinition()->getTransitions()[$subjectName] ?? null)[$key] ?? null) : null,
            default => null,
        };
    }
}
