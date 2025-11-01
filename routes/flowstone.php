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

Route::group([
    'domain' => config('flowstone.ui.domain'),
    'prefix' => trim(config('flowstone.ui.path'), '/'),
    'middleware' => array_filter(config('flowstone.ui.middleware', [])),
], function () {
    // Authorization gate for UI access
    Route::middleware(['can:'.config('flowstone.ui.gate')])->group(function () {
        // Dashboard
        Route::get('/', Dashboard::class)
            ->name('flowstone.dashboard');

        // Workflows list
        Route::get('/workflows', WorkflowIndex::class)
            ->name('flowstone.workflows.index');

        Route::get('/workflows/{workflow}/details', WorkflowShow::class)
            ->name('flowstone.workflows.show');

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
