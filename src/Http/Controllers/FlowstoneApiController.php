<?php

namespace CleaniqueCoders\Flowstone\Http\Controllers;

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Processors\WorkflowGraphBuilder;
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

    public function graph(Workflow $workflow, WorkflowGraphBuilder $builder)
    {
        return response()->json($builder->build($workflow));
    }
}
