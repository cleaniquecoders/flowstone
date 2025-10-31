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
            $table->enum('type', ['state_machine', 'workflow'])->default('state_machine');
            $table->string('initial_marking')->nullable();
            $table->string('marking')->nullable(); // Current workflow state
            $table->json('config')->nullable(); // Cached workflow configuration

            $table->boolean('is_enabled')->default(true);
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
    }

    public function down()
    {
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_places');
        Schema::dropIfExists('workflows');
    }
};
