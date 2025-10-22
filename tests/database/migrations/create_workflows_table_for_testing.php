<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->string('marking')->default('draft');
            $table->json('workflow')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->json('created_by')->nullable();
            $table->json('updated_by')->nullable();
            $table->json('deleted_by')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflows');
    }
};
