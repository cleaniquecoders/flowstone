<?php

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;

// config for CleaniqueCoders/Flowstone
return [
    /*
    |--------------------------------------------------------------------------
    | Default Workflow Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the default workflow behavior including
    | supported models, initial status, and available transitions.
    |
    */

    'default' => [
        /*
        |--------------------------------------------------------------------------
        | Workflow Type
        |--------------------------------------------------------------------------
        |
        | Supported types: 'state_machine', 'workflow'
        |
        */
        'type' => 'state_machine',

        /*
        |--------------------------------------------------------------------------
        | Supported Models
        |--------------------------------------------------------------------------
        |
        | Define which models should support the workflow functionality.
        | You can add multiple model classes here.
        |
        */
        'supports' => [
            Workflow::class,
            // Add more models here as needed
            // App\Models\YourModel::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Marking Store Configuration
        |--------------------------------------------------------------------------
        |
        | Configure how the workflow state is stored on your models.
        |
        */
        'marking_store' => [
            'type' => 'method',
            'property' => 'status', // The property/column that stores the current status
        ],

        /*
        |--------------------------------------------------------------------------
        | Initial Status
        |--------------------------------------------------------------------------
        |
        | The default status when a new workflow instance is created.
        |
        */
        'initial_marking' => Status::DRAFT->value,

        /*
        |--------------------------------------------------------------------------
        | Available Places (Statuses)
        |--------------------------------------------------------------------------
        |
        | Define all possible statuses in your workflow.
        | Set to null to use all Status enum values, or specify custom places.
        |
        */
        'places' => null, // null = auto-generate from Status enum

        /*
        |--------------------------------------------------------------------------
        | Workflow Transitions
        |--------------------------------------------------------------------------
        |
        | Define the allowed transitions between statuses.
        | Set to null to use the default transitions, or define custom ones.
        |
        | Format:
        | 'transition_name' => [
        |     'from' => ['status1', 'status2'],
        |     'to' => 'target_status',
        |     'metadata' => [...] // optional
        | ]
        |
        */
        'transitions' => null, // null = use default transitions

        /*
        |--------------------------------------------------------------------------
        | Event Configuration
        |--------------------------------------------------------------------------
        |
        | Configure which events are dispatched and which event listeners
        | are registered for this workflow.
        |
        */
        'event_listeners' => [
            // Add your custom event listener classes here
            // Example:
            // App\Listeners\Workflow\SendNotificationOnApproval::class,
            // App\Listeners\Workflow\LogWorkflowTransition::class,
            // App\Listeners\Workflow\UpdateRelatedModels::class,
        ],

        /*
        |--------------------------------------------------------------------------
        | Events to Dispatch
        |--------------------------------------------------------------------------
        |
        | Specify which workflow event types should be dispatched.
        | Leave empty to dispatch all events, or specify specific event names.
        |
        | Available event types:
        | - workflow.guard          (Before transition validation)
        | - workflow.leave          (Before leaving a place)
        | - workflow.transition     (During transition)
        | - workflow.enter          (Before entering a place, before marking update)
        | - workflow.entered        (After entering a place, after marking update)
        | - workflow.completed      (After transition completes)
        | - workflow.announce       (When new transitions become available)
        |
        | You can also use workflow-specific events:
        | - workflow.{workflow_name}.guard
        | - workflow.{workflow_name}.{transition_name}.completed
        |
        */
        'events_to_dispatch' => [
            // Leave empty to dispatch all events, or specify:
            // 'workflow.guard',
            // 'workflow.completed',
            // 'workflow.entered',
        ],

        /*
        |--------------------------------------------------------------------------
        | Event Dispatch Flags
        |--------------------------------------------------------------------------
        |
        | Fine-grained control over which event types are dispatched.
        | These boolean flags provide a simpler alternative to specifying
        | event names in the events_to_dispatch array.
        |
        | Set to false to disable specific event types for performance.
        | For example, if you don't need announce events (which can be frequent),
        | set dispatch_announce_events to false.
        |
        */
        'dispatch_guard_events' => true,
        'dispatch_leave_events' => true,
        'dispatch_transition_events' => true,
        'dispatch_enter_events' => true,
        'dispatch_entered_events' => true,
        'dispatch_completed_events' => true,
        'dispatch_announce_events' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Workflow Configurations
    |--------------------------------------------------------------------------
    |
    | You can define additional workflow configurations for specific use cases.
    | Each configuration can have different models, statuses, and transitions.
    |
    */
    'custom' => [
        // Example custom workflow
        // 'approval_process' => [
        //     'type' => 'state_machine',
        //     'supports' => [App\Models\Document::class],
        //     'marking_store' => [
        //         'type' => 'method',
        //         'property' => 'approval_status',
        //     ],
        //     'initial_marking' => Status::DRAFT->value,
        //     'places' => [
        //         Status::DRAFT->value => null,
        //         Status::UNDER_REVIEW->value => null,
        //         Status::APPROVED->value => null,
        //         Status::REJECTED->value => null,
        //     ],
        //     'transitions' => [
        //         'submit_for_review' => [
        //             'from' => [Status::DRAFT->value],
        //             'to' => Status::UNDER_REVIEW->value,
        //         ],
        //         'approve' => [
        //             'from' => [Status::UNDER_REVIEW->value],
        //             'to' => Status::APPROVED->value,
        //         ],
        //         'reject' => [
        //             'from' => [Status::UNDER_REVIEW->value],
        //             'to' => Status::REJECTED->value,
        //         ],
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic discovery of workflow-enabled models.
    |
    */
    'auto_discovery' => [
        'enabled' => false,
        'paths' => [
            app_path('Models'),
        ],
        'trait' => 'CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow',
    ],

    /*
        |--------------------------------------------------------------------------
        | UI (Admin) Settings
        |--------------------------------------------------------------------------
        |
        | Telescope-like admin UI to visualize and manage workflows.
        |
        */
    'ui' => [
        'enabled' => env('FLOWSTONE_UI_ENABLED', true),

        // UI base path and optional domain
        'path' => env('FLOWSTONE_UI_PATH', 'flowstone'),
        'domain' => env('FLOWSTONE_UI_DOMAIN', null),

        // Middleware stack for all UI routes
        'middleware' => [
            'web',
        ],

        // Gate used to authorize viewing the UI (like Telescope)
        'gate' => env('FLOWSTONE_UI_GATE', 'viewFlowstone'),

        // Allow-list fallback when not local (emails, ids, etc.)
        'allowed' => [
            // 'admin@example.com',
        ],

        // Asset base URL if you ship compiled JS/CSS
        'asset_url' => env('FLOWSTONE_UI_ASSET_URL', '/vendor/flowstone'),

        /*
        |--------------------------------------------------------------------------
        | Inline Assets
        |--------------------------------------------------------------------------
        |
        | Determines whether assets should be inlined in the HTML or served
        | as separate files. Inlining is useful for packages, while separate
        | files are better for caching and debugging.
        |
        */

        'inline_assets' => env('FLOWSTONE_INLINE_ASSETS', true),
    ],
];
