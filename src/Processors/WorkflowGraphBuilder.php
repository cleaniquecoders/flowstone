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

        // Build a graph structure to calculate layout
        $graph = $this->buildGraphStructure($workflow);
        $positions = $this->calculatePositions($graph);

        foreach ($workflow->places as $place) {
            $position = $positions[$place->name] ?? ['x' => 0, 'y' => 0];

            $nodes[] = [
                'id' => (string) $place->name,
                'data' => [
                    'label' => $this->formatLabel($place->name),
                    'meta' => $place->meta ?? [],
                ],
                'position' => $position,
                'type' => 'workflow',
            ];
        }

        foreach ($workflow->transitions as $transition) {
            $edges[] = [
                'id' => (string) ($transition->name.':'.$transition->from_place.'->'.$transition->to_place),
                'source' => (string) $transition->from_place,
                'target' => (string) $transition->to_place,
                'label' => $this->formatLabel($transition->name),
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

    /**
     * Build a graph structure for layout calculation
     */
    protected function buildGraphStructure(Workflow $workflow): array
    {
        $graph = [
            'nodes' => [],
            'edges' => [],
        ];

        foreach ($workflow->places as $place) {
            $graph['nodes'][$place->name] = [
                'id' => $place->name,
                'incoming' => [],
                'outgoing' => [],
            ];
        }

        foreach ($workflow->transitions as $transition) {
            $graph['edges'][] = [
                'from' => $transition->from_place,
                'to' => $transition->to_place,
            ];

            if (isset($graph['nodes'][$transition->from_place])) {
                $graph['nodes'][$transition->from_place]['outgoing'][] = $transition->to_place;
            }
            if (isset($graph['nodes'][$transition->to_place])) {
                $graph['nodes'][$transition->to_place]['incoming'][] = $transition->from_place;
            }
        }

        return $graph;
    }

    /**
     * Calculate positions using a simple hierarchical layout
     */
    protected function calculatePositions(array $graph): array
    {
        $positions = [];
        $levels = $this->assignLevels($graph);

        $levelWidth = 300; // Horizontal spacing
        $nodeHeight = 120; // Vertical spacing

        // Count nodes per level for centering
        $levelCounts = array_count_values($levels);
        $levelCounters = array_fill_keys(array_keys($levelCounts), 0);

        foreach ($levels as $nodeId => $level) {
            $nodesInLevel = $levelCounts[$level];
            $counter = $levelCounters[$level]++;

            // Center nodes in each level
            $yOffset = ($counter - ($nodesInLevel - 1) / 2) * $nodeHeight;

            $positions[$nodeId] = [
                'x' => $level * $levelWidth,
                'y' => $yOffset,
            ];
        }

        return $positions;
    }

    /**
     * Assign levels to nodes using topological sort
     */
    protected function assignLevels(array $graph): array
    {
        $levels = [];
        $queue = [];
        $inDegree = [];

        // Initialize in-degrees
        foreach ($graph['nodes'] as $id => $node) {
            $inDegree[$id] = count($node['incoming']);
            if ($inDegree[$id] === 0) {
                $queue[] = $id;
                $levels[$id] = 0;
            }
        }

        // Process queue
        while (! empty($queue)) {
            $current = array_shift($queue);
            $currentLevel = $levels[$current];

            foreach ($graph['nodes'][$current]['outgoing'] ?? [] as $neighbor) {
                $inDegree[$neighbor]--;

                if (! isset($levels[$neighbor])) {
                    $levels[$neighbor] = $currentLevel + 1;
                } else {
                    $levels[$neighbor] = max($levels[$neighbor], $currentLevel + 1);
                }

                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        // Handle any remaining nodes (cycles or disconnected)
        foreach ($graph['nodes'] as $id => $node) {
            if (! isset($levels[$id])) {
                $levels[$id] = 0;
            }
        }

        return $levels;
    }

    /**
     * Format label from snake_case to Title Case
     */
    protected function formatLabel(string $text): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $text));
    }
}
