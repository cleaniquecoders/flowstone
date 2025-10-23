<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Facades\Cache;

describe('Workflow Performance Tests', function () {
    it('caches workflow instances to avoid repeated processing', function () {
        $workflow = WorkflowFactory::new()->create();

        // Clear cache first
        Cache::flush();

        $startTime = microtime(true);

        // First call - should cache the result
        $workflow1 = $workflow->getWorkflow();
        $firstCallTime = microtime(true) - $startTime;

        $startTime = microtime(true);

        // Second call - should use cache
        $workflow2 = $workflow->getWorkflow();
        $secondCallTime = microtime(true) - $startTime;

        expect($workflow1)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
        expect($workflow2)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);

        // Second call should be significantly faster (cached)
        expect($secondCallTime)->toBeLessThan($firstCallTime);
    });

    it('handles bulk workflow creation efficiently', function () {
        $startTime = microtime(true);

        // Create 100 workflows
        $workflows = WorkflowFactory::new()->count(100)->create();

        $creationTime = microtime(true) - $startTime;

        expect($workflows)->toHaveCount(100);
        expect($creationTime)->toBeLessThan(5.0); // Should complete within 5 seconds

        // Each workflow should be properly created
        foreach ($workflows->take(10) as $workflow) {
            expect($workflow->exists)->toBeTrue();
            expect($workflow->uuid)->not->toBeNull();
        }
    });

    it('efficiently queries enabled workflows', function () {
        // Create mix of enabled and disabled workflows
        WorkflowFactory::new()->enabled()->count(50)->create();
        WorkflowFactory::new()->disabled()->count(50)->create();

        $startTime = microtime(true);

        $enabledWorkflows = Workflow::isEnabled()->get();

        $queryTime = microtime(true) - $startTime;

        expect($enabledWorkflows)->toHaveCount(50);
        expect($queryTime)->toBeLessThan(1.0); // Should complete within 1 second
        expect($enabledWorkflows->every(fn ($w) => $w->is_enabled))->toBeTrue();
    });

    it('efficiently processes workflow configuration lookups', function () {
        // Create workflows with different names
        $names = ['article', 'task', 'order', 'project', 'review'];

        foreach ($names as $name) {
            WorkflowFactory::new()->create([
                'type' => 'state_machine',
                'name' => $name,
                'config' => [
                    'metadata' => ['type' => ['value' => $name]],
                ],
            ]);
        }

        $startTime = microtime(true);

        // Lookup each name
        foreach ($names as $name) {
            $config = get_workflow_config($name, 'name');
            expect($config)->toBeArray();
        }

        $lookupTime = microtime(true) - $startTime;

        expect($lookupTime)->toBeLessThan(1.0); // Should complete within 1 second
    });

    it('handles concurrent workflow access efficiently', function () {
        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create();

        $results = [];
        $startTime = microtime(true);

        // Simulate concurrent access
        for ($i = 0; $i < 20; $i++) {
            $results[] = $workflow->getWorkflow();
            $results[] = $workflow->getEnabledToTransitions();
            $results[] = $workflow->getMarking();
        }

        $accessTime = microtime(true) - $startTime;

        expect(count($results))->toBe(60); // 20 iterations × 3 calls each
        expect($accessTime)->toBeLessThan(2.0); // Should complete within 2 seconds
    });

    it('efficiently generates workflow cache keys', function () {
        $workflows = WorkflowFactory::new()->count(1000)->create();

        $startTime = microtime(true);

        $keys = [];
        foreach ($workflows as $workflow) {
            $keys[] = $workflow->getWorkflowKey();
        }

        $keyGenerationTime = microtime(true) - $startTime;

        expect(count($keys))->toBe(1000);
        expect($keyGenerationTime)->toBeLessThan(1.0); // Should complete within 1 second

        // All keys should be unique since they have different IDs
        $uniqueKeys = array_unique($keys);
        expect(count($uniqueKeys))->toBe(1000);
    });

    it('efficiently processes role lookups from transitions', function () {
        $workflow = WorkflowFactory::new()->create([
            'config' => [
                'transitions' => array_fill(0, 100, [
                    'from' => ['state_a'],
                    'to' => 'state_b',
                    'metadata' => [
                        'role' => ['admin', 'manager', 'supervisor'],
                    ],
                ]),
            ],
        ]);

        $startTime = microtime(true);

        // Test role lookup performance
        for ($i = 0; $i < 50; $i++) {
            $roles = $workflow->getRolesFromTransition('state_b', 'to');
            expect($roles)->toBeArray();
        }

        $roleTime = microtime(true) - $startTime;

        expect($roleTime)->toBeLessThan(1.0); // Should complete within 1 second
    });

    it('maintains performance with complex workflow configurations', function () {
        // Create a complex workflow with many places and transitions
        $places = [];
        $transitions = [];

        for ($i = 0; $i < 50; $i++) {
            $places["state_{$i}"] = null;

            // Each state can transition to the next 3 states
            for ($j = 1; $j <= 3; $j++) {
                $nextState = $i + $j;
                if ($nextState < 50) {
                    $transitions["transition_{$i}_to_{$nextState}"] = [
                        'from' => ["state_{$i}"],
                        'to' => "state_{$nextState}",
                        'metadata' => [
                            'role' => ['user', 'admin'],
                        ],
                    ];
                }
            }
        }

        $complexConfig = [
            'type' => 'state_machine',
            'supports' => [Workflow::class],
            'places' => $places,
            'transitions' => $transitions,
            'marking_store' => ['property' => 'marking'],
        ];

        $startTime = microtime(true);

        $workflow = create_workflow($complexConfig);

        $creationTime = microtime(true) - $startTime;

        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
        expect($creationTime)->toBeLessThan(2.0); // Should complete within 2 seconds

        $definition = $workflow->getDefinition();
        expect(count($definition->getPlaces()))->toBe(50);
        expect(count($definition->getTransitions()))->toBeGreaterThan(100);
    });
});
