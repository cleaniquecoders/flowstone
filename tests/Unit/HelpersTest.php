<?php

use CleaniqueCoders\Flowstone\Database\Factories\WorkflowFactory;
use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

describe('Helper Functions', function () {
    describe('create_workflow', function () {
        it('creates a symfony workflow from configuration', function () {
            $configuration = [
                'type' => 'state_machine',
                'supports' => [Workflow::class],
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

            $workflow = create_workflow($configuration);

            expect($workflow)->toBeInstanceOf(SymfonyWorkflow::class);
        });

        it('uses provided registry when given', function () {
            $configuration = [
                'type' => 'state_machine',
                'supports' => [Workflow::class],
                'places' => [Status::DRAFT->value => null],
                'transitions' => [],
                'marking_store' => ['property' => 'marking'],
            ];

            $registry = new Registry;
            $workflow = create_workflow($configuration, $registry);

            expect($workflow)->toBeInstanceOf(SymfonyWorkflow::class);
        });

        it('uses app registry when none provided', function () {
            $configuration = [
                'type' => 'state_machine',
                'supports' => [Workflow::class],
                'places' => [Status::DRAFT->value => null],
                'transitions' => [],
                'marking_store' => ['property' => 'marking'],
            ];

            $workflow = create_workflow($configuration);

            expect($workflow)->toBeInstanceOf(SymfonyWorkflow::class);
        });
    });

    describe('get_workflow_config', function () {
        it('returns workflow config from database when found', function () {
            $config = [
                'type' => 'state_machine',
                'places' => [Status::DRAFT->value => null, Status::PENDING->value => null],
                'transitions' => [
                    'submit' => ['from' => [Status::DRAFT->value], 'to' => Status::PENDING->value],
                ],
                'metadata' => [
                    'type' => ['value' => 'test-workflow'],
                ],
            ];

            WorkflowFactory::new()->create([
                'type' => 'test-workflow',
                'config' => $config,
                'is_enabled' => true,
            ]);

            $result = get_workflow_config('test-workflow', 'type');

            expect($result)->toEqual($config);
        });

        it('returns default workflow when no database config found', function () {
            // Clear any existing workflows
            Workflow::query()->delete();

            $result = get_workflow_config('non-existent-workflow', 'type');

            expect($result)->toBeArray();
            expect($result)->toHaveKey('type');
            expect($result)->toHaveKey('places');
            expect($result)->toHaveKey('transitions');
        });

        it('returns latest enabled workflow when multiple exist', function () {
            $olderConfig = ['type' => 'state_machine', 'version' => 1];
            $newerConfig = ['type' => 'state_machine', 'version' => 2];

            // Create older workflow
            WorkflowFactory::new()->create([
                'type' => 'test-workflow',
                'config' => array_merge($olderConfig, [
                    'metadata' => ['type' => ['value' => 'test-workflow']],
                ]),
                'is_enabled' => true,
                'created_at' => now()->subDay(),
            ]);

            // Create newer workflow
            WorkflowFactory::new()->create([
                'type' => 'test-workflow',
                'config' => array_merge($newerConfig, [
                    'metadata' => ['type' => ['value' => 'test-workflow']],
                ]),
                'is_enabled' => true,
                'created_at' => now(),
            ]);

            $result = get_workflow_config('test-workflow', 'type');

            expect($result['version'])->toBe(2);
        });

        it('ignores disabled workflows', function () {
            WorkflowFactory::new()->create([
                'type' => 'test-workflow',
                'config' => [
                    'metadata' => ['type' => ['value' => 'test-workflow']],
                ],
                'is_enabled' => false,
            ]);

            $result = get_workflow_config('test-workflow', 'type');

            // Should return default config since no enabled workflow exists
            expect($result)->toHaveKey('type');
            expect($result)->toHaveKey('places');
            expect($result)->toHaveKey('transitions');
        });
    });

    describe('get_roles_from_transition', function () {
        it('returns roles for to transitions', function () {
            $workflow = [
                'transitions' => [
                    'approve' => [
                        'from' => [Status::UNDER_REVIEW->value],
                        'to' => Status::APPROVED->value,
                        'metadata' => [
                            'role' => ['manager', 'supervisor'],
                        ],
                    ],
                    'reject' => [
                        'from' => [Status::UNDER_REVIEW->value],
                        'to' => Status::REJECTED->value,
                        'metadata' => [
                            'role' => ['manager'],
                        ],
                    ],
                ],
            ];

            $roles = get_roles_from_transition($workflow, Status::APPROVED->value, 'to');

            expect($roles)->toEqual(['manager', 'supervisor']);
        });

        it('returns roles for from transitions', function () {
            $workflow = [
                'transitions' => [
                    'submit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                        'metadata' => [
                            'role' => ['author', 'contributor'],
                        ],
                    ],
                    'edit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::DRAFT->value,
                        'metadata' => [
                            'role' => ['editor'],
                        ],
                    ],
                ],
            ];

            $roles = get_roles_from_transition($workflow, Status::DRAFT->value, 'from');

            expect($roles)->toEqual(['author', 'contributor', 'editor']);
        });

        it('returns empty array when no roles found', function () {
            $workflow = [
                'transitions' => [
                    'auto_complete' => [
                        'from' => [Status::IN_PROGRESS->value],
                        'to' => Status::COMPLETED->value,
                        // No metadata.role defined
                    ],
                ],
            ];

            $roles = get_roles_from_transition($workflow, Status::COMPLETED->value, 'to');

            expect($roles)->toEqual([]);
        });

        it('returns unique roles when duplicates exist', function () {
            $workflow = [
                'transitions' => [
                    'transition1' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                        'metadata' => [
                            'role' => ['admin', 'manager'],
                        ],
                    ],
                    'transition2' => [
                        'from' => [Status::DRAFT->value],
                        'to' => Status::PENDING->value,
                        'metadata' => [
                            'role' => ['manager', 'supervisor'],
                        ],
                    ],
                ],
            ];

            $roles = get_roles_from_transition($workflow, Status::PENDING->value, 'to');

            expect($roles)->toEqual(['admin', 'manager', 'supervisor']);
            expect(count($roles))->toBe(3); // No duplicates
        });

        it('handles array from values', function () {
            $workflow = [
                'transitions' => [
                    'multi_source' => [
                        'from' => [Status::DRAFT->value, Status::PENDING->value],
                        'to' => Status::IN_PROGRESS->value,
                        'metadata' => [
                            'role' => ['worker'],
                        ],
                    ],
                ],
            ];

            $rolesFromDraft = get_roles_from_transition($workflow, Status::DRAFT->value, 'from');
            $rolesFromPending = get_roles_from_transition($workflow, Status::PENDING->value, 'from');

            expect($rolesFromDraft)->toEqual(['worker']);
            expect($rolesFromPending)->toEqual(['worker']);
        });

        it('handles string from/to values', function () {
            $workflow = [
                'transitions' => [
                    'simple' => [
                        'from' => Status::DRAFT->value, // String instead of array
                        'to' => Status::PENDING->value,
                        'metadata' => [
                            'role' => ['author'],
                        ],
                    ],
                ],
            ];

            $roles = get_roles_from_transition($workflow, Status::DRAFT->value, 'from');

            expect($roles)->toEqual(['author']);
        });

        it('returns empty array for empty workflow', function () {
            $workflow = ['transitions' => []];

            $roles = get_roles_from_transition($workflow, Status::DRAFT->value, 'from');

            expect($roles)->toEqual([]);
        });
    });
});
