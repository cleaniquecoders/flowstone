<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

describe('InteractsWithWorkflow Trait', function () {
    beforeEach(function () {
        $this->workflow = WorkflowFactory::new()->create([
            'type' => 'test-workflow',
            'config' => [
                'type' => 'state_machine',
                'supports' => [Workflow::class],
                'places' => [
                    Status::DRAFT->value => null,
                    Status::PENDING->value => null,
                    Status::IN_PROGRESS->value => null,
                    Status::COMPLETED->value => null,
                ],
                'transitions' => [
                    'submit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                    ],
                    'start' => [
                        'from' => [Status::PENDING->value],
                        'to' => Status::IN_PROGRESS->value,
                    ],
                    'complete' => [
                        'from' => [Status::IN_PROGRESS->value],
                        'to' => Status::COMPLETED->value,
                    ],
                ],
                'marking_store' => [
                    'property' => 'marking',
                ],
                'metadata' => [
                    'type' => [
                        'value' => 'test-workflow',
                    ],
                ],
            ],
        ]);
    });

    it('can set workflow configuration', function () {
        $workflow = WorkflowFactory::new()->create(['workflow' => null]);

        expect($workflow->workflow)->toBeNull();

        $workflow->setWorkflow();

        expect($workflow->workflow)->not->toBeNull();
        expect($workflow->workflow)->toBeArray();
    });

    it('does not override existing workflow configuration', function () {
        $existingConfig = ['existing' => 'configuration'];
        $workflow = WorkflowFactory::new()->create(['workflow' => $existingConfig]);

        $workflow->setWorkflow();

        expect($workflow->workflow)->toEqual($existingConfig);
    });

    it('has workflow type accessor', function () {
        expect($this->workflow->workflow_type)->toBe($this->workflow->type);
    });

    it('has workflow type field accessor', function () {
        expect($this->workflow->workflow_type_field)->toBe('type');
    });

    it('can get symfony workflow instance', function () {
        $workflow = $this->workflow->getWorkflow();

        expect($workflow)->toBeInstanceOf(SymfonyWorkflow::class);
    });

    it('caches workflow instance', function () {
        Cache::shouldReceive('remember')
            ->once()
            ->with(
                $this->workflow->getWorkflowKey(),
                \Mockery::any(),
                \Mockery::type('callable')
            )
            ->andReturn(\Mockery::mock(SymfonyWorkflow::class));

        $this->workflow->getWorkflow();
    });

    it('generates correct workflow cache key', function () {
        $key = $this->workflow->getWorkflowKey();

        expect($key)->toBeString();
        expect($key)->toContain('cleaniquecoder');
        expect($key)->toContain('flowstone');
        expect($key)->toContain('models');
        expect($key)->toContain('workflow');
        expect($key)->toContain((string) $this->workflow->id);
    });

    it('can get marking', function () {
        expect($this->workflow->getMarking())->toBe($this->workflow->marking);
    });

    it('can get enabled transitions', function () {
        $workflow = WorkflowFactory::new()->withMarking(Status::DRAFT)->create();

        // Mock the Symfony workflow to return test transitions
        $mockWorkflow = \Mockery::mock(SymfonyWorkflow::class);
        $mockTransition = \Mockery::mock(\Symfony\Component\Workflow\Transition::class);
        $mockTransition->shouldReceive('getTos')->andReturn([Status::PENDING->value]);

        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->with($workflow)
            ->andReturn([$mockTransition]);

        Cache::shouldReceive('remember')
            ->andReturn($mockWorkflow);

        $transitions = $workflow->getEnabledTransitions();

        expect($transitions)->toBeArray();
        expect($transitions)->toHaveCount(1);
    });

    it('can get enabled to transitions', function () {
        $workflow = WorkflowFactory::new()->withMarking(Status::DRAFT)->create();

        // Mock the Symfony workflow and transitions
        $mockTransition = \Mockery::mock(\Symfony\Component\Workflow\Transition::class);
        $mockTransition->shouldReceive('getTos')->andReturn([Status::PENDING->value]);

        $mockWorkflow = \Mockery::mock(SymfonyWorkflow::class);
        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->with($workflow)
            ->andReturn([$mockTransition]);

        Cache::shouldReceive('remember')
            ->andReturn($mockWorkflow);

        $toTransitions = $workflow->getEnabledToTransitions();

        expect($toTransitions)->toBeArray();
        expect($toTransitions)->toHaveKey(Status::PENDING->value);
        expect($toTransitions[Status::PENDING->value])->toBe('Pending');
    });

    it('can check if has enabled to transitions', function () {
        $workflow = WorkflowFactory::new()->withMarking(Status::DRAFT)->create();

        // Mock empty transitions
        $mockWorkflow = \Mockery::mock(SymfonyWorkflow::class);
        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->with($workflow)
            ->andReturn([]);

        Cache::shouldReceive('remember')
            ->andReturn($mockWorkflow);

        expect($workflow->hasEnabledToTransitions())->toBeFalse();

        // Mock with transitions
        $mockTransition = \Mockery::mock(\Symfony\Component\Workflow\Transition::class);
        $mockTransition->shouldReceive('getTos')->andReturn([Status::PENDING->value]);

        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->with($workflow)
            ->andReturn([$mockTransition]);

        Cache::shouldReceive('remember')
            ->andReturn($mockWorkflow);

        expect($workflow->hasEnabledToTransitions())->toBeTrue();
    });

    it('can get roles from transition', function () {
        $workflow = WorkflowFactory::new()->create([
            'workflow' => [
                'transitions' => [
                    'submit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                        'metadata' => [
                            'role' => ['admin', 'manager'],
                        ],
                    ],
                ],
            ],
        ]);

        $roles = $workflow->getRolesFromTransition(Status::PENDING->value, 'to');

        expect($roles)->toBeArray();
    });

    it('can get all enabled transition roles', function () {
        $workflow = WorkflowFactory::new()->withMarking(Status::DRAFT)->create();

        // Mock transitions
        $mockTransition = \Mockery::mock(\Symfony\Component\Workflow\Transition::class);
        $mockTransition->shouldReceive('getTos')->andReturn([Status::PENDING->value]);

        $mockWorkflow = \Mockery::mock(SymfonyWorkflow::class);
        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->with($workflow)
            ->andReturn([$mockTransition]);

        Cache::shouldReceive('remember')
            ->andReturn($mockWorkflow);

        $allRoles = $workflow->getAllEnabledTransitionRoles();

        expect($allRoles)->toBeArray();
        expect($allRoles)->toHaveKey(Status::PENDING->value);
    });
});
