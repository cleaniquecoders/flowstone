<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Processors\Workflow as WorkflowProcessor;

describe('Workflow Edge Cases and Error Handling', function () {
    it('handles empty workflow configuration gracefully', function () {
        $workflow = WorkflowFactory::new()->create([
            'config' => [],
        ]);

        expect($workflow->config)->toEqual([]);

        // Should still be able to set workflow
        $workflow->setWorkflow();
        expect($workflow->workflow)->not->toBeNull();
    });

    it('handles null workflow configuration', function () {
        $workflow = WorkflowFactory::new()->create([
            'config' => null,
        ]);

        expect($workflow->config)->toBeNull();

        // Should still be able to set workflow
        $workflow->setWorkflow();
        expect($workflow->workflow)->not->toBeNull();
    });

    it('handles malformed transition configurations', function () {
        $workflow = WorkflowFactory::new()->create([
            'workflow' => [
                'transitions' => [
                    'malformed' => [
                        // Missing 'from' and 'to'
                        'metadata' => ['role' => ['admin']],
                    ],
                ],
            ],
        ]);

        $roles = $workflow->getRolesFromTransition('some-state', 'to');
        expect($roles)->toEqual([]);
    });

    it('handles empty transitions array', function () {
        $workflow = WorkflowFactory::new()->create([
            'workflow' => [
                'transitions' => [],
            ],
        ]);

        $roles = $workflow->getRolesFromTransition('draft', 'to');
        expect($roles)->toEqual([]);
    });

    it('handles workflow without metadata roles', function () {
        $workflow = WorkflowFactory::new()->create([
            'workflow' => [
                'transitions' => [
                    'submit' => [
                        'from' => ['draft'],
                        'to' => 'pending',
                        // No metadata
                    ],
                ],
            ],
        ]);

        $roles = $workflow->getRolesFromTransition('pending', 'to');
        expect($roles)->toEqual([]);
    });

    it('handles invalid status enum values gracefully', function () {
        expect(Status::tryFrom('invalid-status'))->toBeNull();
        expect(fn () => Status::from('invalid-status'))
            ->toThrow(ValueError::class);
    });

    it('handles workflow with circular dependencies', function () {
        $config = [
            'type' => 'state_machine',
            'supports' => [Workflow::class],
            'places' => [
                'state_a' => null,
                'state_b' => null,
            ],
            'transitions' => [
                'a_to_b' => [
                    'from' => ['state_a'],
                    'to' => 'state_b',
                ],
                'b_to_a' => [
                    'from' => ['state_b'],
                    'to' => 'state_a',
                ],
            ],
            'marking_store' => [
                'property' => 'marking',
            ],
        ];

        $workflow = create_workflow($config);
        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
    });

    it('handles large number of transitions', function () {
        $places = [];
        $transitions = [];

        // Create 100 states and transitions
        for ($i = 0; $i < 100; $i++) {
            $places["state_{$i}"] = null;
            if ($i > 0) {
                $transitions["transition_{$i}"] = [
                    'from' => ['state_'.($i - 1)],
                    'to' => "state_{$i}",
                ];
            }
        }

        $config = [
            'type' => 'state_machine',
            'supports' => [Workflow::class],
            'places' => $places,
            'transitions' => $transitions,
            'marking_store' => ['property' => 'marking'],
        ];

        $workflow = create_workflow($config);
        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);

        $definition = $workflow->getDefinition();
        expect(count($definition->getPlaces()))->toBe(100);
        expect(count($definition->getTransitions()))->toBe(99);
    });

    it('handles workflow cache key generation for models without ID', function () {
        $workflow = new Workflow;
        // Model without ID (not saved to database)

        $key = $workflow->getWorkflowKey();
        expect($key)->toBeString();

        // Should not end with a hyphen followed by an ID when no ID is present
        expect($key)->not->toEndWith('-'); // Should NOT end with hyphen when no ID is present
        expect($key)->toContain('cleaniquecoders-flowstone-models-workflow');

        // Create a saved model to compare
        $savedWorkflow = WorkflowFactory::new()->create();
        $savedKey = $savedWorkflow->getWorkflowKey();
        expect($savedKey)->toEndWith('-'.$savedWorkflow->id); // Should end with ID when model is saved
    });

    it('handles missing workflow configuration in database lookup', function () {
        // Clear all workflows
        Workflow::query()->delete();

        $config = get_workflow_config('non-existent-type', 'type');

        // Should return default configuration
        expect($config)->toBeArray();
        expect($config)->toHaveKey('type');
        expect($config)->toHaveKey('places');
        expect($config['type'])->toBe('state_machine');
    });

    it('handles custom workflow processor errors gracefully', function () {
        expect(fn () => WorkflowProcessor::getCustomWorkflow('non-existent'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('handles database connection errors during workflow lookup', function () {
        // Mock database error by using invalid connection
        config(['database.connections.testing.database' => '/invalid/path']);

        // Should still return default config when database fails
        $config = get_workflow_config('test', 'type');
        expect($config)->toBeArray();
    })->skip('Database mocking complex for this test environment');

    it('handles extremely long workflow type names', function () {
        $longTypeName = str_repeat('a', 1000);

        $workflow = WorkflowFactory::new()->create([
            'type' => $longTypeName,
        ]);

        expect($workflow->workflow_type)->toBe($longTypeName);
        // The workflow key is based on class name + ID, not type, so it should be reasonable length
        expect(strlen($workflow->getWorkflowKey()))->toBeGreaterThan(10);
        expect(strlen($workflow->getWorkflowKey()))->toBeLessThan(200);
    });

    it('handles workflow with no enabled transitions', function () {
        $workflow = WorkflowFactory::new()->create([
            'marking' => 'final_state', // State with no outgoing transitions
        ]);

        // Mock workflow with no enabled transitions
        $mockWorkflow = \Mockery::mock(\Symfony\Component\Workflow\Workflow::class);
        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->andReturn([]);

        \Illuminate\Support\Facades\Cache::shouldReceive('remember')
            ->andReturn($mockWorkflow);

        expect($workflow->getEnabledToTransitions())->toEqual([]);
        expect($workflow->hasEnabledToTransitions())->toBeFalse();
        expect($workflow->getAllEnabledTransitionRoles())->toEqual([]);
    });
});
