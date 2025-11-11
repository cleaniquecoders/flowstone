<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds event configuration fields to the workflows table to control
     * which events are dispatched and which event listeners are registered.
     */
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            // Array of event listener class names to register for this workflow
            // Example: ['App\Listeners\SendNotificationOnApproval', 'App\Listeners\LogWorkflowTransition']
            $table->json('event_listeners')->nullable()->after('audit_trail_enabled');

            // Array of specific event types to dispatch (empty = all events)
            // Example: ['workflow.leave', 'workflow.completed', 'workflow.entered']
            // If null or empty, all 7 event types will be dispatched
            $table->json('events_to_dispatch')->nullable()->after('event_listeners');

            // Boolean flags to enable/disable specific event types globally for this workflow
            // These provide a simpler alternative to specifying event names in events_to_dispatch
            $table->boolean('dispatch_guard_events')->default(true)->after('events_to_dispatch')
                ->comment('Enable workflow.guard and workflow.*.guard events');

            $table->boolean('dispatch_leave_events')->default(true)->after('dispatch_guard_events')
                ->comment('Enable workflow.leave and workflow.*.leave events');

            $table->boolean('dispatch_transition_events')->default(true)->after('dispatch_leave_events')
                ->comment('Enable workflow.transition and workflow.*.transition events');

            $table->boolean('dispatch_enter_events')->default(true)->after('dispatch_transition_events')
                ->comment('Enable workflow.enter and workflow.*.enter events');

            $table->boolean('dispatch_entered_events')->default(true)->after('dispatch_enter_events')
                ->comment('Enable workflow.entered and workflow.*.entered events');

            $table->boolean('dispatch_completed_events')->default(true)->after('dispatch_entered_events')
                ->comment('Enable workflow.completed and workflow.*.completed events');

            $table->boolean('dispatch_announce_events')->default(true)->after('dispatch_completed_events')
                ->comment('Enable workflow.announce and workflow.*.announce events');

            // Index for performance when filtering workflows by event configuration
            $table->index(['dispatch_guard_events', 'dispatch_completed_events'], 'idx_event_flags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropIndex('idx_event_flags');
            $table->dropColumn([
                'event_listeners',
                'events_to_dispatch',
                'dispatch_guard_events',
                'dispatch_leave_events',
                'dispatch_transition_events',
                'dispatch_enter_events',
                'dispatch_entered_events',
                'dispatch_completed_events',
                'dispatch_announce_events',
            ]);
        });
    }
};
