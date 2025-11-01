<?php

namespace CleaniqueCoders\Flowstone\Http\Controllers;

use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Routing\Controller;

class FlowstoneApiController extends Controller
{
    public function index()
    {
        $workflows = Workflow::query()
            ->withCount(['places', 'transitions'])
            ->orderBy('name')
            ->get(['id', 'uuid', 'name', 'type', 'initial_marking', 'marking', 'is_enabled']);

        return response()->json([
            'data' => $workflows,
        ]);
    }

    public function show(Workflow $workflow)
    {
        $workflow->load(['places', 'transitions']);

        return response()->json([
            'data' => [
                'id' => $workflow->id,
                'uuid' => $workflow->uuid,
                'name' => $workflow->name,
                'type' => $workflow->type,
                'initial_marking' => $workflow->initial_marking,
                'marking' => $workflow->marking,
                'is_enabled' => $workflow->is_enabled,
                'places' => $workflow->places,
                'transitions' => $workflow->transitions,
                'designer' => $workflow->designer,
            ],
        ]);
    }

    public function update(Workflow $workflow)
    {
        $validated = request()->validate([
            'config' => 'required|array',
            'config.type' => 'required|string|in:state_machine,workflow',
            'config.places' => 'required|array',
            'config.transitions' => 'required|array',
            'config.initial_marking' => 'nullable|string',
            'designer' => 'nullable|array',
            'designer.nodes' => 'nullable|array',
            'designer.edges' => 'nullable|array',
            'designer.viewport' => 'nullable|array',
        ]);

        $config = $validated['config'];

        // Update workflow basic info
        $workflow->update([
            'type' => $config['type'],
            'initial_marking' => $config['initial_marking'] ?? null,
        ]);

        // Sync places from config
        $workflow->places()->delete();
        $sortOrder = 0;
        foreach ($config['places'] as $placeName => $placeData) {
            $workflow->places()->create([
                'name' => $placeName,
                'sort_order' => $sortOrder++,
                'meta' => is_array($placeData) ? $placeData : [],
            ]);
        }

        // Sync transitions from config
        $workflow->transitions()->delete();
        $sortOrder = 0;
        foreach ($config['transitions'] as $transitionName => $transitionData) {
            // Handle multiple 'from' places - create a transition for each
            $fromPlaces = $transitionData['from'] ?? [];
            if (! is_array($fromPlaces)) {
                $fromPlaces = [$fromPlaces];
            }

            foreach ($fromPlaces as $fromPlace) {
                $workflow->transitions()->create([
                    'name' => $transitionName,
                    'from_place' => $fromPlace,
                    'to_place' => $transitionData['to'],
                    'sort_order' => $sortOrder++,
                    'meta' => $transitionData['metadata'] ?? [],
                ]);
            }
        }

        // Regenerate config from database relationships
        $workflow->update([
            'config' => $workflow->getSymfonyConfig(),
        ]);

        // Save designer layout data if provided
        if (isset($validated['designer'])) {
            $workflow->update([
                'designer' => $validated['designer'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $workflow->fresh(['places', 'transitions']),
        ]);
    }
}
