<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Facades\Cache;

describe('Workflow Integration', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('can create a complete workflow and perform transitions', function () {
        // Create a workflow with all necessary configuration
        $workflow = WorkflowFactory::new()->create([
            'type' => 'article',
            'marking' => Status::DRAFT->value,
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
                        'metadata' => [
                            'role' => ['author'],
                        ],
                    ],
                    'start_work' => [
                        'from' => [Status::PENDING->value],
                        'to' => Status::IN_PROGRESS->value,
                        'metadata' => [
                            'role' => ['editor'],
                        ],
                    ],
                    'complete' => [
                        'from' => [Status::IN_PROGRESS->value],
                        'to' => Status::COMPLETED->value,
                        'metadata' => [
                            'role' => ['editor', 'manager'],
                        ],
                    ],
                ],
                'marking_store' => [
                    'property' => 'marking',
                ],
                'metadata' => [
                    'type' => [
                        'value' => 'article',
                    ],
                ],
            ],
        ]);

        // Verify initial state
        expect($workflow->getMarking())->toBe(Status::DRAFT->value);

        // Set workflow configuration
        $workflow->setWorkflow();
        expect($workflow->workflow)->not->toBeNull();

        // Get the Symfony workflow instance
        $symphonyWorkflow = $workflow->getWorkflow();
        expect($symphonyWorkflow)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);

        // Check available transitions from DRAFT
        $enabledTransitions = $workflow->getEnabledToTransitions();
        expect($enabledTransitions)->toHaveKey(Status::PENDING->value);

        // Verify workflow has enabled transitions
        expect($workflow->hasEnabledToTransitions())->toBeTrue();

        // Check roles for transitions
        $roles = $workflow->getRolesFromTransition(Status::PENDING->value, 'to');
        expect($roles)->toEqual(['author']);
    });

    it('can handle workflow state transitions through different statuses', function () {
        $workflow = WorkflowFactory::new()->create([
            'marking' => Status::DRAFT->value,
        ]);

        // Test progression through states
        $testStates = [
            Status::DRAFT->value,
            Status::PENDING->value,
            Status::IN_PROGRESS->value,
            Status::COMPLETED->value,
        ];

        foreach ($testStates as $state) {
            $workflow->update(['marking' => $state]);
            expect($workflow->fresh()->getMarking())->toBe($state);
        }
    });

    it('caches workflow instances properly', function () {
        $workflow = WorkflowFactory::new()->create();

        // First call should cache the workflow
        $firstInstance = $workflow->getWorkflow();

        // Second call should use the cached version
        $secondInstance = $workflow->getWorkflow();

        expect($firstInstance)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
        expect($secondInstance)->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
    });

    it('can retrieve workflow configuration from database', function () {
        $config = [
            'type' => 'state_machine',
            'places' => [
                Status::DRAFT->value => null,
                Status::COMPLETED->value => null,
            ],
            'transitions' => [
                'publish' => [
                    'from' => [Status::DRAFT->value],
                    'to' => Status::COMPLETED->value,
                ],
            ],
            'metadata' => [
                'type' => ['value' => 'blog-post'],
            ],
        ];

        WorkflowFactory::new()->create([
            'type' => 'blog-post',
            'config' => $config,
        ]);

        $retrievedConfig = get_workflow_config('blog-post', 'type');

        expect($retrievedConfig)->toEqual($config);
    });

    it('falls back to default configuration when no database config exists', function () {
        $config = get_workflow_config('non-existent-type', 'type');

        expect($config)->toBeArray();
        expect($config)->toHaveKey('type');
        expect($config)->toHaveKey('places');
        expect($config)->toHaveKey('transitions');

        // Should contain all Status enum values as places
        foreach (Status::cases() as $status) {
            expect($config['places'])->toHaveKey($status->value);
        }
    });

    it('can work with multiple workflow types simultaneously', function () {
        // Create different workflow types
        $articleWorkflow = WorkflowFactory::new()->create([
            'type' => 'article',
            'name' => 'Article Workflow',
            'config' => [
                'metadata' => ['type' => ['value' => 'article']],
            ],
        ]);

        $taskWorkflow = WorkflowFactory::new()->create([
            'type' => 'task',
            'name' => 'Task Workflow',
            'config' => [
                'metadata' => ['type' => ['value' => 'task']],
            ],
        ]);

        expect($articleWorkflow->workflow_type)->toBe('article');
        expect($taskWorkflow->workflow_type)->toBe('task');

        // Both should be able to get their workflows
        expect($articleWorkflow->getWorkflow())->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);
        expect($taskWorkflow->getWorkflow())->toBeInstanceOf(\Symfony\Component\Workflow\Workflow::class);

        // They should have different cache keys
        expect($articleWorkflow->getWorkflowKey())->not->toBe($taskWorkflow->getWorkflowKey());
    });

    it('can handle enabled/disabled workflows correctly', function () {
        $enabledWorkflow = WorkflowFactory::new()->enabled()->create([
            'type' => 'enabled-type',
            'config' => [
                'metadata' => ['type' => ['value' => 'enabled-type']],
            ],
        ]);

        $disabledWorkflow = WorkflowFactory::new()->disabled()->create([
            'type' => 'disabled-type',
            'config' => [
                'metadata' => ['type' => ['value' => 'disabled-type']],
            ],
        ]);

        // Only enabled workflow should be found by get_workflow_config
        $enabledConfig = get_workflow_config('enabled-type', 'type');
        expect($enabledConfig)->toEqual($enabledWorkflow->config);

        // Disabled workflow should fall back to default
        $disabledConfig = get_workflow_config('disabled-type', 'type');
        expect($disabledConfig)->not->toEqual($disabledWorkflow->config);
    });

    it('can get all transition roles for current state', function () {
        $workflow = WorkflowFactory::new()->create([
            'marking' => Status::UNDER_REVIEW->value,
            'workflow' => [
                'transitions' => [
                    'approve' => [
                        'from' => [Status::UNDER_REVIEW->value],
                        'to' => Status::APPROVED->value,
                        'metadata' => ['role' => ['manager']],
                    ],
                    'reject' => [
                        'from' => [Status::UNDER_REVIEW->value],
                        'to' => Status::REJECTED->value,
                        'metadata' => ['role' => ['manager', 'supervisor']],
                    ],
                ],
            ],
        ]);

        // Mock the enabled transitions
        $mockApproveTransition = \Mockery::mock(\Symfony\Component\Workflow\Transition::class);
        $mockApproveTransition->shouldReceive('getTos')->andReturn([Status::APPROVED->value]);

        $mockRejectTransition = \Mockery::mock(\Symfony\Component\Workflow\Transition::class);
        $mockRejectTransition->shouldReceive('getTos')->andReturn([Status::REJECTED->value]);

        $mockWorkflow = \Mockery::mock(\Symfony\Component\Workflow\Workflow::class);
        $mockWorkflow->shouldReceive('getEnabledTransitions')
            ->andReturn([$mockApproveTransition, $mockRejectTransition]);

        Cache::shouldReceive('remember')->andReturn($mockWorkflow);

        $allRoles = $workflow->getAllEnabledTransitionRoles();

        expect($allRoles)->toHaveKey(Status::APPROVED->value);
        expect($allRoles)->toHaveKey(Status::REJECTED->value);
        expect($allRoles[Status::APPROVED->value])->toEqual(['manager']);
        expect($allRoles[Status::REJECTED->value])->toEqual(['manager', 'supervisor']);
    });
});
