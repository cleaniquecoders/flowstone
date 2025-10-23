<?php

namespace CleaniqueCoders\Flowstone\Tests;

use CleaniqueCoders\Flowstone\FlowstoneServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => match ($modelName) {
                \CleaniqueCoders\Flowstone\Models\Workflow::class => \CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory::class,
                default => 'CleaniqueCoders\\Flowstone\\Database\\Factories\\'.class_basename($modelName).'Factory'
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FlowstoneServiceProvider::class,
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
        $app['config']->set('flowstone', include __DIR__.'/config/flowstone.php');

        // Create the workflow table for testing
        $migration = include __DIR__.'/database/migrations/create_workflows_table_for_testing.php';
        $migration->up();
    }
}
