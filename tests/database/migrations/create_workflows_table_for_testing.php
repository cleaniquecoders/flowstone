<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Main workflows table
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();

            $table->string('name');
            $table->text('description')->nullable();

            // Organization fields
            $table->string('group')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();

            $table->enum('type', ['state_machine', 'workflow'])->default('state_machine');

            // Marking store configuration
            $table->string('marking_store_type')->nullable();
            $table->string('marking_store_property')->nullable();

            $table->string('initial_marking')->nullable();
            $table->string('marking')->nullable(); // Current workflow state
            $table->json('config')->nullable(); // Cached workflow configuration
            $table->json('designer')->nullable(); // Visual designer layout

            $table->boolean('is_enabled')->default(true);
            $table->boolean('audit_trail_enabled')->default(false);

            // Event configuration
            $table->json('event_listeners')->nullable();
            $table->json('events_to_dispatch')->nullable();
            $table->boolean('dispatch_guard_events')->default(true);
            $table->boolean('dispatch_leave_events')->default(true);
            $table->boolean('dispatch_transition_events')->default(true);
            $table->boolean('dispatch_enter_events')->default(true);
            $table->boolean('dispatch_entered_events')->default(true);
            $table->boolean('dispatch_completed_events')->default(true);
            $table->boolean('dispatch_announce_events')->default(true);

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // Workflow places (states)
        Schema::create('workflow_places', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();

            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('name')->index(); // e.g., 'draft', 'pending', 'approved'
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable(); // Symfony meta only

            $table->timestamps();

            $table->unique(['workflow_id', 'name']);
            $table->index(['workflow_id', 'sort_order']);
        });

        // Workflow transitions
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();

            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('name')->index(); // e.g., 'submit', 'approve', 'reject'
            $table->string('from_place');
            $table->string('to_place');
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable(); // Symfony meta only

            $table->timestamps();

            $table->index(['workflow_id', 'from_place']);
            $table->index(['workflow_id', 'to_place']);
            $table->index(['workflow_id', 'name']);
        });

        // Test articles table for workflow integration testing
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('marking')->default('draft');
            $table->string('workflow_type')->default('article-workflow');
            $table->json('workflow')->nullable(); // Cached workflow configuration
            $table->timestamps();
        });

        // Workflow audit logs table
        Schema::create('workflow_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();

            // Workflow reference
            $table->foreignId('workflow_id')
                ->nullable()
                ->constrained('workflows')
                ->nullOnDelete();

            // Subject (the model that underwent the transition)
            $table->string('subject_type')->index();
            $table->unsignedBigInteger('subject_id')->index();

            // Transition details
            $table->string('from_place')->nullable()->index();
            $table->string('to_place')->index();
            $table->string('transition')->index();

            // User who performed the transition
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Additional context and metadata
            $table->json('context')->nullable();
            $table->json('metadata')->nullable();

            // Timestamp
            $table->timestamp('created_at')->index();

            // Composite indexes for common queries
            $table->index(['subject_type', 'subject_id']);
            $table->index(['workflow_id', 'created_at']);
            $table->index(['subject_type', 'subject_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_audit_logs');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_places');
        Schema::dropIfExists('workflows');
    }
};
