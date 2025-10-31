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
        ]);

        $workflow->update([
            'config' => $validated['config'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $workflow->fresh(),
        ]);
    }
}
