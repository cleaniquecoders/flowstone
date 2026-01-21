<?php

use CleaniqueCoders\Flowstone\Http\Controllers\FlowstoneApiController;
use CleaniqueCoders\Flowstone\Http\Controllers\FlowstoneAssetController;
use CleaniqueCoders\Flowstone\Livewire\Dashboard;
use CleaniqueCoders\Flowstone\Livewire\WorkflowIndex;
use CleaniqueCoders\Flowstone\Livewire\WorkflowShow;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Facades\Route;

Route::get('/vendor/flowstone/{asset}', [FlowstoneAssetController::class, 'serve'])
    ->where('asset', '.*')
    ->name('flowstone.asset');

// Determine if we should use Livewire 4 routing
$useLivewire4Routing = (function () {
    $setting = config('flowstone.ui.livewire', 'auto');
    if ($setting === 'v4') {
        return true;
    }
    if ($setting === 'v3') {
        return false;
    }

    // Auto-detect: check if Route::livewire() macro exists (Livewire 4)
    return Route::hasMacro('livewire');
})();

Route::group([
    'domain' => config('flowstone.ui.domain'),
    'prefix' => trim(config('flowstone.ui.path'), '/'),
    'middleware' => array_filter(config('flowstone.ui.middleware', [])),
], function () use ($useLivewire4Routing) {
    // Authorization gate for UI access
    Route::middleware(['can:'.config('flowstone.ui.gate')])->group(function () use ($useLivewire4Routing) {
        // Dashboard
        if ($useLivewire4Routing) {
            Route::livewire('/', 'flowstone::dashboard')
                ->name('flowstone.dashboard');
        } else {
            Route::get('/', Dashboard::class)
                ->name('flowstone.dashboard');
        }

        // Workflows list
        if ($useLivewire4Routing) {
            Route::livewire('/workflows', 'flowstone::workflow-index')
                ->name('flowstone.workflows.index');
        } else {
            Route::get('/workflows', WorkflowIndex::class)
                ->name('flowstone.workflows.index');
        }

        if ($useLivewire4Routing) {
            Route::livewire('/workflows/{workflow}/details', 'flowstone::workflow-show')
                ->name('flowstone.workflows.show');
        } else {
            Route::get('/workflows/{workflow}/details', WorkflowShow::class)
                ->name('flowstone.workflows.show');
        }

        // Workflow designer
        Route::get('/workflows/{workflow}/designer', function (Workflow $workflow) {
            return view('flowstone::workflows.designer', compact('workflow'));
        })->name('flowstone.workflows.designer');

        // JSON API
        Route::prefix('api')->group(function () {
            Route::get('/workflows', [FlowstoneApiController::class, 'index']);
            Route::get('/workflows/{workflow}', [FlowstoneApiController::class, 'show']);
            Route::patch('/workflows/{workflow}', [FlowstoneApiController::class, 'update'])->name('flowstone.api.workflow.update');
            Route::get('/workflows/{workflow}/graph', [FlowstoneApiController::class, 'graph']);
        });
    });
});
