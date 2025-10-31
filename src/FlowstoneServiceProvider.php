<?php

namespace CleaniqueCoders\Flowstone;

use CleaniqueCoders\Flowstone\Commands\CreateWorkflowCommand;
use CleaniqueCoders\Flowstone\Commands\FlowstoneCommand;
use CleaniqueCoders\Flowstone\Livewire\Dashboard;
use CleaniqueCoders\Flowstone\Livewire\WorkflowShow;
use CleaniqueCoders\Flowstone\Livewire\WorkflowsIndex;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\Workflow\Registry;

class FlowstoneServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('flowstone')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_workflows_table')
            ->hasCommands([
                FlowstoneCommand::class,
                CreateWorkflowCommand::class,
            ])
            ->hasRoute('flowstone');
    }

    public function packageRegistered()
    {
        $this->app->singleton(Registry::class, function () {
            return new Registry;
        });
    }

    public function packageBooted(): void
    {
        if (! config('flowstone.ui.enabled')) {
            return;
        }
        $this->registerUiAuthorization();
        $this->registerUiRoutes();
        $this->registerLivewireComponents();
        $this->registerBladeComponents();
    }

    protected function registerUiAuthorization(): void
    {
        $gateName = config('flowstone.ui.gate', 'viewFlowstone');

        // Define a default gate only if the application hasn't defined it
        if (! Gate::has($gateName)) {
            Gate::define($gateName, function ($user = null) {
                // Default: allow only in local environment if no explicit auth configured
                return app()->environment('local');
            });
        }
    }

    protected function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::component('flowstone.dashboard', Dashboard::class);
        Livewire::component('flowstone.workflows-index', WorkflowsIndex::class);
        Livewire::component('flowstone.workflow-show', WorkflowShow::class);
    }

    protected function registerBladeComponents(): void
    {
        // Register package Blade component namespace for <x-flowstone::... /> usage
        Blade::componentNamespace('CleaniqueCoders\\Flowstone\\View\\Components', 'flowstone');
    }

    /**
     * Register dashboard routes behind auth middleware and prefix
     */
    protected function registerUiRoutes(): void
    {
        // Load package routes
        $this->loadRoutesFrom(__DIR__.'/../routes/flowstone.php');
    }
}
