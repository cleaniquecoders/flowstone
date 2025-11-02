<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
    }
};
