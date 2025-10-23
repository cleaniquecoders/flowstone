<?php

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;

describe('Database-Driven Workflow Integration', function () {
    it('can create workflow configuration in database', function () {
        // Create a workflow configuration with places and transitions
        $workflow = Workflow::factory()->withPlacesAndTransitions()->create([
            'name' => 'Article Workflow',
            'type' => 'state_machine',
            'initial_marking' => Status::DRAFT->value,
        ]);

        expect($workflow->places)->toHaveCount(4);
        expect($workflow->transitions)->toHaveCount(3);

        // Verify Symfony config generation
        $config = $workflow->getSymfonyConfig();
        expect($config)->toHaveKey('type', 'state_machine');
        expect($config)->toHaveKey('places');
        expect($config)->toHaveKey('transitions');
        expect($config['initial_marking'])->toBe(Status::DRAFT->value);
    });

    it('can generate symfony workflow from database configuration', function () {
        // Create workflow configuration
        $workflow = Workflow::factory()->withPlacesAndTransitions()->create([
            'name' => 'Article Publishing',
        ]);

        $config = $workflow->getSymfonyConfig();

        // Verify the structure matches Symfony expectations
        expect($config['places'])->toHaveKeys([
            Status::DRAFT->value,
            Status::PENDING->value,
            Status::IN_PROGRESS->value,
            Status::COMPLETED->value,
        ]);

        expect($config['transitions'])->toHaveKeys([
            'submit',
            'start',
            'complete',
        ]);

        // Verify transition structure
        $submitTransition = $config['transitions']['submit'];
        expect($submitTransition['from'])->toBe([Status::DRAFT->value]);
        expect($submitTransition['to'])->toBe(Status::PENDING->value);
    });

    it('can work with helper functions for database-driven workflows', function () {
        // Create a workflow configuration in database
        Workflow::factory()->withPlacesAndTransitions()->create([
            'name' => 'Test Workflow',
        ]);

        // Test helper function to get workflow config from database
        $config = get_workflow_config('Test Workflow');

        expect($config)->toBeArray();
        expect($config)->toHaveKey('type');
        expect($config)->toHaveKey('places');
        expect($config)->toHaveKey('transitions');
    });

    it('returns default config when workflow not found in database', function () {
        // Try to get a workflow that doesn't exist
        $config = get_workflow_config('Non-Existent Workflow');

        // Should return default configuration
        expect($config)->toBeArray();
        expect($config)->not->toBeEmpty();
    });

    it('can create symfony workflow from database config', function () {
        // Create workflow in database
        $workflow = Workflow::factory()->withPlacesAndTransitions()->create([
            'name' => 'Database Workflow',
        ]);

        // Get config and create Symfony workflow
        $config = $workflow->getSymfonyConfig();
        $symfonyWorkflow = create_workflow($config);

        expect($symfonyWorkflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);

        // Test that workflow has the expected places and transitions
        expect($symfonyWorkflow->getDefinition()->getPlaces())->toContain(Status::DRAFT->value);
        expect($symfonyWorkflow->getDefinition()->getPlaces())->toContain(Status::PENDING->value);
    });
});
