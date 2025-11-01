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
            \Livewire\LivewireServiceProvider::class,
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

        // Create articles table for testing workflow trait
        \Illuminate\Support\Facades\Schema::dropIfExists('articles');
        \Illuminate\Support\Facades\Schema::create('articles', function ($table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('marking')->nullable();
            $table->string('workflow_type')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }
}
