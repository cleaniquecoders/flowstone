<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;

// Note: RefreshDatabase is not used here since we rely on testbench migrations

describe('Event Listeners Management', function () {
    it('can add an event listener to a workflow', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => [],
        ]);

        $listener = 'App\Listeners\SendNotificationOnApproval';
        $workflow->addEventListener($listener);
        $workflow->save();

        expect($workflow->fresh()->event_listeners)
            ->toBeArray()
            ->toContain($listener);
    });

    it('can check if a workflow has an event listener', function () {
        $listener = 'App\Listeners\SendNotificationOnApproval';
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => [$listener],
        ]);

        expect($workflow->hasEventListener($listener))->toBeTrue();
        expect($workflow->hasEventListener('App\Listeners\NonExistent'))->toBeFalse();
    });

    it('can remove an event listener from a workflow', function () {
        $listener1 = 'App\Listeners\SendNotificationOnApproval';
        $listener2 = 'App\Listeners\LogWorkflowTransition';

        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => [$listener1, $listener2],
        ]);

        $workflow->removeEventListener($listener1);
        $workflow->save();

        $fresh = $workflow->fresh();
        expect($fresh->event_listeners)
            ->not->toContain($listener1)
            ->toContain($listener2);
    });

    it('does not add duplicate event listeners', function () {
        $listener = 'App\Listeners\SendNotificationOnApproval';
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => [$listener],
        ]);

        $workflow->addEventListener($listener);
        $workflow->save();

        expect($workflow->fresh()->event_listeners)
            ->toHaveCount(1)
            ->toContain($listener);
    });

    it('can add multiple event listeners', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => [],
        ]);

        $listeners = [
            'App\Listeners\SendNotificationOnApproval',
            'App\Listeners\LogWorkflowTransition',
            'App\Listeners\UpdateRelatedModels',
        ];

        foreach ($listeners as $listener) {
            $workflow->addEventListener($listener);
        }
        $workflow->save();

        $fresh = $workflow->fresh();
        expect($fresh->event_listeners)
            ->toHaveCount(3)
            ->toEqual($listeners);
    });
});

describe('Event Dispatch Configuration', function () {
    it('can configure specific events to dispatch', function () {
        $eventsToDispatch = [
            'workflow.guard',
            'workflow.completed',
            'workflow.entered',
        ];

        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => $eventsToDispatch,
        ]);

        expect($workflow->events_to_dispatch)
            ->toBeArray()
            ->toEqual($eventsToDispatch);
    });

    it('returns true when events_to_dispatch is empty (all events enabled)', function () {
        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => [],
        ]);

        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('leave'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('completed'))->toBeTrue();
    });

    it('returns true when events_to_dispatch is null (all events enabled)', function () {
        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => null,
        ]);

        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('announce'))->toBeTrue();
    });

    it('checks if specific event types should be dispatched', function () {
        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => [
                'workflow.guard',
                'workflow.completed',
            ],
        ]);

        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('completed'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('leave'))->toBeFalse();
        expect($workflow->shouldDispatchEvent('announce'))->toBeFalse();
    });

    it('supports wildcard event patterns', function () {
        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => [
                'workflow.*.guard',
                'workflow.*.completed',
            ],
        ]);

        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('completed'))->toBeTrue();
    });
});

describe('Event Dispatch Flags', function () {
    it('can disable specific event types using boolean flags', function () {
        $workflow = WorkflowFactory::new()->create([
            'dispatch_guard_events' => true,
            'dispatch_leave_events' => false,
            'dispatch_announce_events' => false,
        ]);

        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('leave'))->toBeFalse();
        expect($workflow->shouldDispatchEvent('announce'))->toBeFalse();
    });

    it('defaults to true when boolean flags are not explicitly set', function () {
        $workflow = WorkflowFactory::new()->create([
            // Don't set dispatch_guard_events - it should default to true from database
        ]);

        // The important check: shouldDispatchEvent should return true
        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('leave'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('completed'))->toBeTrue();
    });

    it('boolean flags take precedence over events_to_dispatch array', function () {
        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => ['workflow.leave', 'workflow.guard'],
            'dispatch_leave_events' => false,
        ]);

        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('leave'))->toBeFalse();
    });

    it('can disable all announce events for performance', function () {
        $workflow = WorkflowFactory::new()->create([
            'dispatch_announce_events' => false,
        ]);

        expect($workflow->shouldDispatchEvent('announce'))->toBeFalse();
        expect($workflow->shouldDispatchEvent('guard'))->toBeTrue();
        expect($workflow->shouldDispatchEvent('completed'))->toBeTrue();
    });
});

describe('Event Configuration Retrieval', function () {
    it('can get complete event configuration', function () {
        $listeners = [
            'App\Listeners\SendNotificationOnApproval',
            'App\Listeners\LogWorkflowTransition',
        ];
        $eventsToDispatch = ['workflow.guard', 'workflow.completed'];

        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => $listeners,
            'events_to_dispatch' => $eventsToDispatch,
            'dispatch_guard_events' => true,
            'dispatch_leave_events' => false,
        ]);

        $config = $workflow->getEventConfiguration();

        expect($config)
            ->toBeArray()
            ->toHaveKeys(['event_listeners', 'events_to_dispatch', 'dispatch_flags']);

        expect($config['event_listeners'])->toEqual($listeners);
        expect($config['events_to_dispatch'])->toEqual($eventsToDispatch);
        expect($config['dispatch_flags'])
            ->toBeArray()
            ->toHaveKeys(['guard', 'leave', 'transition', 'enter', 'entered', 'completed', 'announce']);
        expect($config['dispatch_flags']['guard'])->toBeTrue();
        expect($config['dispatch_flags']['leave'])->toBeFalse();
    });

    it('returns empty arrays when no event configuration is set', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => null,
            'events_to_dispatch' => null,
        ]);

        $config = $workflow->getEventConfiguration();

        expect($config['event_listeners'])->toBeArray()->toBeEmpty();
        expect($config['events_to_dispatch'])->toBeArray()->toBeEmpty();
        expect($config['dispatch_flags'])->toBeArray()->not->toBeEmpty();
    });
});

describe('Symfony Config Integration', function () {
    it('includes event listeners in Symfony config', function () {
        $listeners = [
            'App\Listeners\SendNotificationOnApproval',
            'App\Listeners\LogWorkflowTransition',
        ];

        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create([
            'event_listeners' => $listeners,
        ]);

        $config = $workflow->getSymfonyConfig();

        expect($config)
            ->toHaveKey('event_listeners')
            ->and($config['event_listeners'])->toEqual($listeners);
    });

    it('includes events_to_dispatch in Symfony config', function () {
        $eventsToDispatch = ['workflow.guard', 'workflow.completed'];

        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create([
            'events_to_dispatch' => $eventsToDispatch,
        ]);

        $config = $workflow->getSymfonyConfig();

        expect($config)
            ->toHaveKey('events_to_dispatch')
            ->and($config['events_to_dispatch'])->toEqual($eventsToDispatch);
    });

    it('does not include event config when empty', function () {
        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create([
            'event_listeners' => [],
            'events_to_dispatch' => [],
        ]);

        $config = $workflow->getSymfonyConfig();

        expect($config)->not->toHaveKey('event_listeners');
        expect($config)->not->toHaveKey('events_to_dispatch');
    });

    it('includes both event config and marking store config', function () {
        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create([
            'event_listeners' => ['App\Listeners\TestListener'],
            'marking_store_type' => 'single_state',
            'marking_store_property' => 'status',
        ]);

        $config = $workflow->getSymfonyConfig();

        expect($config)
            ->toHaveKey('event_listeners')
            ->toHaveKey('marking_store')
            ->and($config['marking_store']['type'])->toBe('single_state');
    });
});

describe('Factory Support', function () {
    it('can create workflows with event configuration via factory', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => [
                'App\Listeners\SendNotificationOnApproval',
            ],
            'events_to_dispatch' => ['workflow.guard', 'workflow.completed'],
            'dispatch_announce_events' => false,
        ]);

        expect($workflow->event_listeners)->toHaveCount(1);
        expect($workflow->events_to_dispatch)->toHaveCount(2);
        expect($workflow->dispatch_announce_events)->toBeFalse();
    });
});

describe('Edge Cases', function () {
    it('handles removing non-existent listener gracefully', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => ['App\Listeners\TestListener'],
        ]);

        $workflow->removeEventListener('App\Listeners\NonExistent');
        $workflow->save();

        expect($workflow->fresh()->event_listeners)
            ->toHaveCount(1)
            ->toContain('App\Listeners\TestListener');
    });

    it('handles null event_listeners gracefully', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => null,
        ]);

        expect($workflow->hasEventListener('App\Listeners\Test'))->toBeFalse();

        $workflow->addEventListener('App\Listeners\Test');
        expect($workflow->event_listeners)->toContain('App\Listeners\Test');
    });

    it('shouldDispatchEvent handles invalid event types', function () {
        $workflow = WorkflowFactory::new()->create([
            'events_to_dispatch' => ['workflow.guard'],
        ]);

        expect($workflow->shouldDispatchEvent('invalid_event_type'))->toBeFalse();
    });

    it('removes listener and maintains array indexing', function () {
        $workflow = WorkflowFactory::new()->create([
            'event_listeners' => ['Listener1', 'Listener2', 'Listener3'],
        ]);

        $workflow->removeEventListener('Listener2');
        $workflow->save();

        $listeners = $workflow->fresh()->event_listeners;
        expect($listeners)
            ->toHaveCount(2)
            ->toEqual(['Listener1', 'Listener3']); // Should reindex array
    });
});
