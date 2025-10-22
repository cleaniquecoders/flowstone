<?php

return [
    'default' => [
        'type' => 'state_machine',
        'supports' => [
            \CleaniqueCoders\LaravelWorklfow\Models\Workflow::class,
        ],
        'places' => null, // Auto-generated from Status enum
        'transitions' => null, // Auto-generated default transitions
        'marking_store' => [
            'property' => 'marking',
        ],
        'metadata' => [
            'title' => 'Default Workflow',
            'description' => 'Default workflow configuration for testing',
        ],
    ],

    'custom' => [
        'test_workflow' => [
            'type' => 'state_machine',
            'supports' => [
                \CleaniqueCoders\LaravelWorklfow\Models\Workflow::class,
            ],
            'places' => [
                'draft' => null,
                'pending' => null,
                'published' => null,
            ],
            'transitions' => [
                'submit' => [
                    'from' => ['draft'],
                    'to' => 'pending',
                    'metadata' => [
                        'role' => ['author'],
                    ],
                ],
                'publish' => [
                    'from' => ['pending'],
                    'to' => 'published',
                    'metadata' => [
                        'role' => ['editor', 'admin'],
                    ],
                ],
            ],
            'marking_store' => [
                'property' => 'marking',
            ],
            'metadata' => [
                'title' => 'Test Workflow',
                'description' => 'Test workflow for unit testing',
            ],
        ],
    ],
];
