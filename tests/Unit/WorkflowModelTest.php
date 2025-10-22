<?php

use CleaniqueCoders\LaravelWorklfow\Database\Factories\WorkflowFactory;
use CleaniqueCoders\LaravelWorklfow\Enums\Status;
use CleaniqueCoders\LaravelWorklfow\Models\Workflow;
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

        expect($workflow->getFillable())->toContain('type');
        expect($workflow->getFillable())->toContain('name');
        expect($workflow->getFillable())->toContain('description');
        expect($workflow->getFillable())->toContain('config');
        expect($workflow->getFillable())->toContain('marking');
        expect($workflow->getFillable())->toContain('workflow');
        expect($workflow->getFillable())->toContain('is_enabled');
    });

    it('has correct casts', function () {
        $casts = $this->workflow->getCasts();

        expect($casts)->toHaveKey('config', 'array');
        expect($casts)->toHaveKey('is_enabled', 'bool');
        expect($casts)->toHaveKey('created_by', 'array');
        expect($casts)->toHaveKey('updated_by', 'array');
        expect($casts)->toHaveKey('deleted_by', 'array');
        expect($casts)->toHaveKey('meta', 'array');
    });

    it('generates uuid on creation', function () {
        $workflow = WorkflowFactory::new()->create();

        expect($workflow->uuid)->not->toBeNull();
        expect(Str::isUuid((string) $workflow->uuid))->toBeTrue();
    });

    it('can be created with specific marking', function () {
        $workflow = WorkflowFactory::new()
            ->withMarking(Status::PENDING)
            ->create();

        expect($workflow->marking)->toBe(Status::PENDING->value);
    });

    it('can be enabled and disabled', function () {
        $enabledWorkflow = WorkflowFactory::new()->enabled()->create();
        $disabledWorkflow = WorkflowFactory::new()->disabled()->create();

        expect($enabledWorkflow->is_enabled)->toBeTrue();
        expect($disabledWorkflow->is_enabled)->toBeFalse();
    });

    it('has workflow type attribute accessor', function () {
        expect($this->workflow->workflow_type)->toBe($this->workflow->type);
    });

    it('has workflow type field attribute accessor', function () {
        expect($this->workflow->workflow_type_field)->toBe('type');
    });

    it('can get marking', function () {
        expect($this->workflow->getMarking())->toBe($this->workflow->marking);
    });

    it('can scope enabled workflows', function () {
        WorkflowFactory::new()->enabled()->create();
        WorkflowFactory::new()->disabled()->create();

        $enabledWorkflows = Workflow::isEnabled()->get();

        expect($enabledWorkflows)->toHaveCount(2); // Including the beforeEach workflow
        expect($enabledWorkflows->every(fn ($workflow) => $workflow->is_enabled))->toBeTrue();
    });

    it('stores config as json', function () {
        $config = [
            'type' => 'state_machine',
            'places' => ['draft', 'published'],
            'transitions' => [
                'publish' => [
                    'from' => ['draft'],
                    'to' => 'published',
                ],
            ],
        ];

        $workflow = WorkflowFactory::new()->create(['config' => $config]);

        expect($workflow->config)->toEqual($config);
        expect($workflow->getAttributes()['config'])->toBeString();
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

    it('stores created_by as json', function () {
        $createdBy = [
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $workflow = WorkflowFactory::new()->create(['created_by' => $createdBy]);

        expect($workflow->created_by)->toEqual($createdBy);
        expect($workflow->getAttributes()['created_by'])->toBeString();
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
