<?php

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Processors\Workflow;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

describe('Workflow Processor', function () {
    it('can get default workflow configuration', function () {
        $config = Workflow::getDefaultWorkflow();

        expect($config)->toBeArray();
        expect($config)->toHaveKey('type');
        expect($config)->toHaveKey('places');
        expect($config)->toHaveKey('transitions');
        expect($config)->toHaveKey('supports');
        expect($config)->toHaveKey('marking_store');
    });

    it('auto-generates places from Status enum', function () {
        config(['flowstone.default.places' => null]);

        $config = Workflow::getDefaultWorkflow();

        expect($config['places'])->toBeArray();
        expect($config['places'])->toHaveKey(Status::DRAFT->value);
        expect($config['places'])->toHaveKey(Status::PENDING->value);
        expect($config['places'])->toHaveKey(Status::IN_PROGRESS->value);
        expect($config['places'])->toHaveKey(Status::COMPLETED->value);
        expect($config['places'])->toHaveKey(Status::CANCELLED->value);

        // Check that all Status enum cases are included
        foreach (Status::cases() as $status) {
            expect($config['places'])->toHaveKey($status->value);
        }
    });

    it('auto-generates default transitions when null', function () {
        config(['flowstone.default.transitions' => null]);

        $config = Workflow::getDefaultWorkflow();

        expect($config['transitions'])->toBeArray();
        expect($config['transitions'])->not->toBeEmpty();

        // Check some key transitions exist
        $transitionNames = array_keys($config['transitions']);
        expect($transitionNames)->toContain('Draft to Pending');
        expect($transitionNames)->toContain('Pending to In Progress');
        expect($transitionNames)->toContain('In Progress to Completed');
    });

    it('generates correct default transitions structure', function () {
        $transitions = Workflow::getDefaultWorkflow()['transitions'];

        // Check Draft to Pending transition
        $draftToPending = $transitions['Draft to Pending'];
        expect($draftToPending['from'])->toEqual([Status::DRAFT->value]);
        expect($draftToPending['to'])->toBe(Status::PENDING->value);

        // Check In Progress to Completed transition
        $inProgressToCompleted = $transitions['In Progress to Completed'];
        expect($inProgressToCompleted['from'])->toEqual([Status::IN_PROGRESS->value]);
        expect($inProgressToCompleted['to'])->toBe(Status::COMPLETED->value);

        // Check transitions that can go to cancelled
        $toCancelledTransitions = collect($transitions)->filter(function ($transition) {
            return $transition['to'] === Status::CANCELLED->value;
        });

        expect($toCancelledTransitions)->not->toBeEmpty();
    });

    it('can get custom workflow configuration', function () {
        config([
            'flowstone.custom.test_workflow' => [
                'type' => 'workflow',
                'places' => [
                    'start' => null,
                    'end' => null,
                ],
                'transitions' => [
                    'finish' => [
                        'from' => ['start'],
                        'to' => 'end',
                    ],
                ],
            ],
        ]);

        $config = Workflow::getCustomWorkflow('test_workflow');

        expect($config)->toBeArray();
        expect($config['type'])->toBe('workflow');
        expect($config['places'])->toHaveKey('start');
        expect($config['places'])->toHaveKey('end');
        expect($config['transitions'])->toHaveKey('finish');
    });

    it('auto-generates places for custom workflow when null', function () {
        config([
            'flowstone.custom.test_workflow' => [
                'type' => 'state_machine',
                'places' => null, // Will be auto-generated
                'transitions' => [
                    'submit' => [
                        'from' => ['draft'],
                        'to' => 'pending',
                    ],
                ],
            ],
        ]);

        $config = Workflow::getCustomWorkflow('test_workflow');

        expect($config['places'])->toBeArray();
        foreach (Status::cases() as $status) {
            expect($config['places'])->toHaveKey($status->value);
        }
    });

    it('throws exception for non-existent custom workflow', function () {
        expect(fn () => Workflow::getCustomWorkflow('non_existent'))
            ->toThrow(InvalidArgumentException::class, "Custom workflow 'non_existent' not found in configuration.");
    });

    it('can create symfony workflow instance', function () {
        $config = [
            'type' => 'state_machine',
            'supports' => [\CleaniqueCoders\Flowstone\Models\Workflow::class],
            'places' => [
                Status::DRAFT->value => null,
                Status::PENDING->value => null,
                Status::COMPLETED->value => null,
            ],
            'transitions' => [
                'submit' => [
                    'from' => [Status::DRAFT->value],
                    'to' => Status::PENDING->value,
                ],
                'complete' => [
                    'from' => [Status::PENDING->value],
                    'to' => Status::COMPLETED->value,
                ],
            ],
            'marking_store' => [
                'property' => 'marking',
            ],
        ];

        $registry = new Registry;
        $workflow = Workflow::createWorkflow($config, $registry);

        expect($workflow)->toBeInstanceOf(SymfonyWorkflow::class);
    });

    it('creates transitions correctly in symfony workflow', function () {
        $config = [
            'type' => 'state_machine',
            'supports' => [\CleaniqueCoders\Flowstone\Models\Workflow::class],
            'places' => [
                'draft' => null,
                'published' => null,
            ],
            'transitions' => [
                'publish' => [
                    'from' => ['draft'],
                    'to' => 'published',
                ],
            ],
            'marking_store' => [
                'property' => 'marking',
            ],
        ];

        $registry = new Registry;
        $workflow = Workflow::createWorkflow($config, $registry);

        $definition = $workflow->getDefinition();
        expect($definition->getPlaces())->toEqual(['draft', 'published']);

        $transitions = $definition->getTransitions();
        expect($transitions)->toHaveCount(1);
        expect($transitions[0]->getName())->toBe('publish');
        expect($transitions[0]->getFroms())->toEqual(['draft']);
        expect($transitions[0]->getTos())->toEqual(['published']);
    });

    it('validates all status transitions are properly connected', function () {
        $transitions = Workflow::getDefaultWorkflow()['transitions'];
        $statusValues = collect(Status::cases())->pluck('value')->toArray();

        // Collect all 'from' and 'to' values from transitions
        $fromValues = [];
        $toValues = [];

        foreach ($transitions as $transition) {
            $fromValues = array_merge($fromValues, $transition['from']);
            $toValues[] = $transition['to'];
        }

        $fromValues = array_unique($fromValues);
        $toValues = array_unique($toValues);

        // Check that most statuses can be reached (excluding initial states like DRAFT)
        $reachableStatuses = [
            Status::PENDING->value,
            Status::IN_PROGRESS->value,
            Status::COMPLETED->value,
            Status::CANCELLED->value,
            Status::FAILED->value,
            Status::APPROVED->value,
            Status::REJECTED->value,
        ];

        foreach ($reachableStatuses as $status) {
            expect($toValues)->toContain($status, "Status {$status} should be reachable via transitions");
        }
    });
});
