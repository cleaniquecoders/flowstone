<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->enum('marking_store_type', ['method', 'property', 'single_state', 'multiple_state'])
                ->default('method')
                ->after('type');

            $table->string('marking_store_property')
                ->default('marking')
                ->after('marking_store_type');
        });
    }

    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['marking_store_type', 'marking_store_property']);
        });
    }
};
