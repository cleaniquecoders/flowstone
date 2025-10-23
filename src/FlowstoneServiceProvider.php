<?php

namespace CleaniqueCoders\Flowstone;

use CleaniqueCoders\Flowstone\Commands\CreateWorkflowCommand;
use CleaniqueCoders\Flowstone\Commands\FlowstoneCommand;
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
            ]);
    }

    public function register(): void
    {
        parent::register();

        // Register Symfony Workflow Registry in the container
        $this->app->singleton(Registry::class, function () {
            return new Registry;
        });
    }
}
