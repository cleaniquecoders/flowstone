<?php

namespace CleaniqueCoders\Flowstone\Processors;

use CleaniqueCoders\Flowstone\Models\Workflow;

class WorkflowGraphBuilder
{
    /**
     * Build React Flow compatible graph from a Workflow model.
     *
     * Output shape:
     * - nodes: [{ id, data: { label, meta? }, position: { x, y }, type? }]
     * - edges: [{ id, source, target, label?, animated?, data? }]
     * - meta: { initial_marking, current_marking, counts }
     */
    public function build(Workflow $workflow): array
    {
        $workflow->loadMissing([
            'places' => fn ($q) => $q->orderBy('sort_order'),
            'transitions' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $nodes = [];
        $edges = [];

        // Simple horizontal layout for initial version
        $x = 0;
        $y = 0;
        $xStep = 240;

        foreach ($workflow->places as $place) {
            $nodes[] = [
                'id' => (string) $place->name,
                'data' => [
                    'label' => $place->name,
                    'meta' => $place->meta ?? [],
                ],
                'position' => ['x' => $x, 'y' => $y],
                'type' => 'default',
            ];
            $x += $xStep;
        }

        foreach ($workflow->transitions as $transition) {
            $edges[] = [
                'id' => (string) ($transition->name.':'.$transition->from_place.'->'.$transition->to_place),
                'source' => (string) $transition->from_place,
                'target' => (string) $transition->to_place,
                'label' => $transition->name,
                'animated' => false,
                'data' => [
                    'meta' => $transition->meta ?? [],
                ],
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'meta' => [
                'initial_marking' => $workflow->initial_marking,
                'current_marking' => $workflow->marking,
                'counts' => [
                    'places' => $workflow->places->count(),
                    'transitions' => $workflow->transitions->count(),
                ],
            ],
        ];
    }
}
