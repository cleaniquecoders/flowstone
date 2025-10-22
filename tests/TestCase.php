<?php

namespace CleaniqueCoders\LaravelWorklfow\Tests;

use CleaniqueCoders\LaravelWorklfow\LaravelWorklfowServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => match ($modelName) {
                \CleaniqueCoders\LaravelWorklfow\Models\Workflow::class => \CleaniqueCoders\LaravelWorklfow\Database\Factories\WorkflowFactory::class,
                default => 'CleaniqueCoders\\LaravelWorklfow\\Database\\Factories\\'.class_basename($modelName).'Factory'
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelWorklfowServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Load test configuration
        $app['config']->set('worklfow', include __DIR__.'/config/worklfow.php');

        // Create the workflow table for testing
        $migration = include __DIR__.'/database/migrations/create_workflows_table_for_testing.php';
        $migration->up();
    }
}
