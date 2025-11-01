<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->string('group')->nullable()->after('description')->index();
            $table->string('category')->nullable()->after('group')->index();
            $table->json('tags')->nullable()->after('category');
        });
    }

    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropIndex(['group']);
            $table->dropIndex(['category']);
            $table->dropColumn(['group', 'category', 'tags']);
        });
    }
};
