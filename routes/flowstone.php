<?php

use CleaniqueCoders\Flowstone\Http\Controllers\FlowstoneApiController;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Facades\Route;

Route::group([
    'domain' => config('flowstone.ui.domain'),
    'prefix' => trim(config('flowstone.ui.path'), '/'),
    'middleware' => array_filter(config('flowstone.ui.middleware', [])),
], function () {
    // Authorization gate for UI access
    Route::middleware(['can:'.config('flowstone.ui.gate')])->group(function () {
        // Dashboard
        Route::get('/', function () {
            return view('flowstone::dashboard');
        })->name('flowstone.dashboard');

        // Workflows list
        Route::get('/workflows', function () {
            return view('flowstone::workflows.index');
        })->name('flowstone.workflows.index');

        // Workflow detail
        Route::get('/workflows/{workflow}', function (Workflow $workflow) {
            return view('flowstone::workflows.show', compact('workflow'));
        })->name('flowstone.workflows.show');

        // JSON API
        Route::prefix('api')->group(function () {
            Route::get('/workflows', [FlowstoneApiController::class, 'index']);
            Route::get('/workflows/{workflow}', [FlowstoneApiController::class, 'show']);
            Route::get('/workflows/{workflow}/graph', [FlowstoneApiController::class, 'graph']);
        });
    });
});
