<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Str;

describe('Workflow Model', function () {
    beforeEach(function () {
        $this->workflow = WorkflowFactory::new()->create();
    });

    it('can be created', function () {
        expect($this->workflow)->toBeInstanceOf(Workflow::class);
        expect($this->workflow->exists)->toBeTrue();
    });

    it('has fillable attributes', function () {
        $workflow = new Workflow;

        expect($workflow->getFillable())->toContain('name');
        expect($workflow->getFillable())->toContain('description');
        expect($workflow->getFillable())->toContain('type');
        expect($workflow->getFillable())->toContain('initial_marking');
        expect($workflow->getFillable())->toContain('is_enabled');
        expect($workflow->getFillable())->toContain('meta');
    });

    it('has correct casts', function () {
        $casts = $this->workflow->getCasts();

        expect($casts)->toHaveKey('is_enabled', 'boolean');
        expect($casts)->toHaveKey('meta', 'array');
    });

    it('generates uuid on creation', function () {
        $workflow = WorkflowFactory::new()->create();

        expect($workflow->uuid)->not->toBeNull();
        expect(Str::isUuid((string) $workflow->uuid))->toBeTrue();
    });

    it('can be created with specific initial marking', function () {
        $workflow = WorkflowFactory::new()
            ->withInitialMarking(Status::PENDING)
            ->create();

        expect($workflow->initial_marking)->toBe(Status::PENDING->value);
    });

    it('can be enabled and disabled', function () {
        $enabledWorkflow = WorkflowFactory::new()->enabled()->create();
        $disabledWorkflow = WorkflowFactory::new()->disabled()->create();

        expect($enabledWorkflow->is_enabled)->toBeTrue();
        expect($disabledWorkflow->is_enabled)->toBeFalse();
    });

    it('has workflow type as enum values', function () {
        $workflow = WorkflowFactory::new()->create(['type' => 'state_machine']);
        expect($workflow->type)->toBe('state_machine');

        $workflow = WorkflowFactory::new()->create(['type' => 'workflow']);
        expect($workflow->type)->toBe('workflow');
    });

    it('can scope enabled workflows', function () {
        WorkflowFactory::new()->enabled()->create();
        WorkflowFactory::new()->disabled()->create();

        $enabledWorkflows = Workflow::isEnabled()->get();

        expect($enabledWorkflows)->toHaveCount(2); // Including the beforeEach workflow
        expect($enabledWorkflows->every(fn ($workflow) => $workflow->is_enabled))->toBeTrue();
    });

    it('has relationships to places and transitions', function () {
        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create();

        expect($workflow->places)->toHaveCount(4);
        expect($workflow->transitions)->toHaveCount(3);
        expect($workflow->places->first())->toBeInstanceOf(\CleaniqueCoders\Flowstone\Models\WorkflowPlace::class);
        expect($workflow->transitions->first())->toBeInstanceOf(\CleaniqueCoders\Flowstone\Models\WorkflowTransition::class);
    });

    it('generates symfony workflow config', function () {
        $workflow = WorkflowFactory::new()->withPlacesAndTransitions()->create();
        $config = $workflow->getSymfonyConfig();

        expect($config)->toHaveKey('type');
        expect($config)->toHaveKey('places');
        expect($config)->toHaveKey('transitions');
        expect($config)->toHaveKey('initial_marking');
        expect($config)->toHaveKey('metadata');

        expect($config['type'])->toBe('state_machine');
        expect($config['places'])->toBeArray();
        expect($config['transitions'])->toBeArray();
        expect($config['initial_marking'])->toBe(Status::DRAFT->value);
    });

    it('stores meta as json', function () {
        $meta = [
            'priority' => 'high',
            'department' => 'IT',
            'tags' => ['urgent', 'security'],
        ];

        $workflow = WorkflowFactory::new()->create(['meta' => $meta]);

        expect($workflow->meta)->toEqual($meta);
        expect($workflow->getAttributes()['meta'])->toBeString();
    });

    it('can be soft deleted', function () {
        $workflow = WorkflowFactory::new()->create();
        $workflowId = $workflow->id;

        $workflow->delete();

        expect(Workflow::find($workflowId))->toBeNull();
        expect(Workflow::withTrashed()->find($workflowId))->not->toBeNull();
    });

    it('has table name workflows', function () {
        expect((new Workflow)->getTable())->toBe('workflows');
    });
});
