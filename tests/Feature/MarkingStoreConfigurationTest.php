<?php

use CleaniqueCoders\Flowstone\Models\Workflow;
use CleaniqueCoders\Flowstone\Models\WorkflowPlace;
use CleaniqueCoders\Flowstone\Models\WorkflowTransition;

describe('Per-Workflow Marking Store Configuration', function () {
    beforeEach(function () {
        $this->workflow = Workflow::factory()->create([
            'name' => 'document-workflow',
            'type' => 'state_machine',
            'initial_marking' => 'draft',
        ]);

        // Create places
        WorkflowPlace::create([
            'workflow_id' => $this->workflow->id,
            'name' => 'draft',
            'sort_order' => 1,
        ]);

        WorkflowPlace::create([
            'workflow_id' => $this->workflow->id,
            'name' => 'review',
            'sort_order' => 2,
        ]);

        WorkflowPlace::create([
            'workflow_id' => $this->workflow->id,
            'name' => 'published',
            'sort_order' => 3,
        ]);

        // Create transitions
        WorkflowTransition::create([
            'workflow_id' => $this->workflow->id,
            'name' => 'submit',
            'from_place' => 'draft',
            'to_place' => 'review',
            'sort_order' => 1,
        ]);

        WorkflowTransition::create([
            'workflow_id' => $this->workflow->id,
            'name' => 'publish',
            'from_place' => 'review',
            'to_place' => 'published',
            'sort_order' => 2,
        ]);
    });

    it('has marking_store_type field', function () {
        $workflow = Workflow::factory()->create([
            'marking_store_type' => 'single_state',
        ]);

        expect($workflow->marking_store_type)->toBe('single_state');
    });

    it('has marking_store_property field', function () {
        $workflow = Workflow::factory()->create([
            'marking_store_property' => 'status',
        ]);

        expect($workflow->marking_store_property)->toBe('status');
    });

    it('defaults to method type if not set', function () {
        $workflow = Workflow::factory()->create();

        expect($workflow->getMarkingStoreType())->toBe('method');
    });

    it('defaults to marking property if not set', function () {
        $workflow = Workflow::factory()->create();

        expect($workflow->getMarkingStoreProperty())->toBe('marking');
    });

    it('returns custom marking store type', function () {
        $workflow = Workflow::factory()->create([
            'marking_store_type' => 'multiple_state',
        ]);

        expect($workflow->getMarkingStoreType())->toBe('multiple_state');
    });

    it('returns custom marking store property', function () {
        $workflow = Workflow::factory()->create([
            'marking_store_property' => 'current_status',
        ]);

        expect($workflow->getMarkingStoreProperty())->toBe('current_status');
    });

    it('includes marking store in Symfony config when set', function () {
        $this->workflow->update([
            'marking_store_type' => 'single_state',
            'marking_store_property' => 'status',
        ]);

        $config = $this->workflow->getSymfonyConfig();

        expect($config)->toHaveKey('marking_store');
        expect($config['marking_store']['type'])->toBe('single_state');
        expect($config['marking_store']['property'])->toBe('status');
    });

    it('excludes marking store from Symfony config when not set', function () {
        $config = $this->workflow->fresh()->getSymfonyConfig();

        expect($config)->not->toHaveKey('marking_store');
    });

    it('can be created with marking store configuration', function () {
        $workflow = Workflow::create([
            'name' => 'approval-workflow',
            'type' => 'state_machine',
            'marking_store_type' => 'single_state',
            'marking_store_property' => 'approval_status',
            'initial_marking' => 'pending',
        ]);

        expect($workflow->marking_store_type)->toBe('single_state');
        expect($workflow->marking_store_property)->toBe('approval_status');
        expect($workflow->getMarkingStoreType())->toBe('single_state');
        expect($workflow->getMarkingStoreProperty())->toBe('approval_status');
    });

    it('supports multiple_state marking store type', function () {
        $workflow = Workflow::factory()->create([
            'type' => 'workflow',
            'marking_store_type' => 'multiple_state',
            'marking_store_property' => 'current_places',
        ]);

        $config = $workflow->getSymfonyConfig();

        expect($config['marking_store']['type'])->toBe('multiple_state');
        expect($config['marking_store']['property'])->toBe('current_places');
    });

    it('can update marking store configuration', function () {
        $this->workflow->update([
            'marking_store_type' => 'multiple_state',
            'marking_store_property' => 'places',
        ]);

        expect($this->workflow->fresh()->marking_store_type)->toBe('multiple_state');
        expect($this->workflow->fresh()->marking_store_property)->toBe('places');
    });

    it('falls back to config values when fields are null', function () {
        config(['flowstone.default.marking_store.type' => 'single_state']);
        config(['flowstone.default.marking_store.property' => 'workflow_state']);

        $workflow = Workflow::factory()->create([
            'marking_store_type' => null,
            'marking_store_property' => null,
        ]);

        expect($workflow->getMarkingStoreType())->toBe('single_state');
        expect($workflow->getMarkingStoreProperty())->toBe('workflow_state');
    });
});

describe('Workflow Processor Marking Store Creation', function () {
    it('creates single state marking store by default', function () {
        $config = [
            'type' => 'state_machine',
            'places' => ['draft' => null, 'published' => null],
            'transitions' => [
                'publish' => ['from' => ['draft'], 'to' => 'published'],
            ],
            'supports' => [Workflow::class],
        ];

        $registry = new \Symfony\Component\Workflow\Registry;
        $workflow = \CleaniqueCoders\Flowstone\Processors\Workflow::createWorkflow($config, $registry);

        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
    });

    it('creates single state marking store with explicit configuration', function () {
        $config = [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'single_state',
                'property' => 'status',
            ],
            'places' => ['draft' => null, 'published' => null],
            'transitions' => [
                'publish' => ['from' => ['draft'], 'to' => 'published'],
            ],
            'supports' => [Workflow::class],
        ];

        $registry = new \Symfony\Component\Workflow\Registry;
        $workflow = \CleaniqueCoders\Flowstone\Processors\Workflow::createWorkflow($config, $registry);

        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
    });

    it('creates multiple state marking store', function () {
        $config = [
            'type' => 'workflow',
            'marking_store' => [
                'type' => 'multiple_state',
                'property' => 'places',
            ],
            'places' => ['editing' => null, 'reviewing' => null, 'published' => null],
            'transitions' => [
                'start_review' => ['from' => ['editing'], 'to' => 'reviewing'],
                'publish' => ['from' => ['reviewing'], 'to' => 'published'],
            ],
            'supports' => [Workflow::class],
        ];

        $registry = new \Symfony\Component\Workflow\Registry;
        $workflow = \CleaniqueCoders\Flowstone\Processors\Workflow::createWorkflow($config, $registry);

        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
    });

    it('uses custom property name', function () {
        $config = [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'method',
                'property' => 'current_state',
            ],
            'places' => ['draft' => null, 'published' => null],
            'transitions' => [
                'publish' => ['from' => ['draft'], 'to' => 'published'],
            ],
            'supports' => [Workflow::class],
        ];

        $registry = new \Symfony\Component\Workflow\Registry;
        $workflow = \CleaniqueCoders\Flowstone\Processors\Workflow::createWorkflow($config, $registry);

        expect($workflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
    });
});
