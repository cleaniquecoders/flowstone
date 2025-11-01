<?php

namespace CleaniqueCoders\Flowstone;

use CleaniqueCoders\Flowstone\Commands\CreateWorkflowCommand;
use CleaniqueCoders\Flowstone\Commands\FlowstoneCommand;
use CleaniqueCoders\Flowstone\Commands\PublishAssetsCommand;
use CleaniqueCoders\Flowstone\Livewire\CreateWorkflow;
use CleaniqueCoders\Flowstone\Livewire\Dashboard;
use CleaniqueCoders\Flowstone\Livewire\EditWorkflow;
use CleaniqueCoders\Flowstone\Livewire\ManagePlaceMetadata;
use CleaniqueCoders\Flowstone\Livewire\ManageTransitionMetadata;
use CleaniqueCoders\Flowstone\Livewire\ManageWorkflowMetadata;
use CleaniqueCoders\Flowstone\Livewire\WorkflowIndex;
use CleaniqueCoders\Flowstone\Livewire\WorkflowShow;
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
            ->hasMigration('add_designer_column_to_workflows_table')
            ->hasCommands([
                FlowstoneCommand::class,
                CreateWorkflowCommand::class,
                PublishAssetsCommand::class,
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
        $this->registerAssetPublishing();
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
        // Only attempt to register Livewire components when the class exists
        // AND the Livewire container binding is available. This prevents
        // failures during CLI contexts (e.g., composer scripts/Testbench) where
        // the Livewire service provider may not be booted yet.
        if (! class_exists(Livewire::class) || ! app()->bound('livewire')) {
            return;
        }

        Livewire::component('flowstone.dashboard', Dashboard::class);
        Livewire::component('flowstone.workflows-index', WorkflowIndex::class);
        Livewire::component('flowstone.workflow-show', WorkflowShow::class);
        Livewire::component('flowstone.create-workflow', CreateWorkflow::class);
        Livewire::component('flowstone.edit-workflow', EditWorkflow::class);
        Livewire::component('flowstone.manage-workflow-metadata', ManageWorkflowMetadata::class);
        Livewire::component('flowstone.manage-place-metadata', ManagePlaceMetadata::class);
        Livewire::component('flowstone.manage-transition-metadata', ManageTransitionMetadata::class);
    }

    protected function registerBladeComponents(): void
    {
        // Register package Blade component namespace for <x-flowstone::... /> usage
        Blade::componentNamespace('CleaniqueCoders\\Flowstone\\View\\Components', 'flowstone');
    }

    protected function registerAssetPublishing(): void
    {
        // Allow publishing built UI assets to the host app's public/vendor/flowstone
        $dist = __DIR__.'/../dist';
        if (is_dir($dist)) {
            $this->publishes([
                $dist => public_path('vendor/flowstone'),
            ], 'flowstone-ui-assets');
        }
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
