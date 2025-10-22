<?php

namespace CleaniqueCoders\LaravelWorklfow;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\LaravelWorklfow\Commands\LaravelWorklfowCommand;

class LaravelWorklfowServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-worklfow')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_worklfow_table')
            ->hasCommand(LaravelWorklfowCommand::class);
    }
}
