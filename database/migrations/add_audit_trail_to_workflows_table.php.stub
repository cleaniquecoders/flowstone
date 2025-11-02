<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->boolean('audit_trail_enabled')
                ->default(false)
                ->after('is_enabled')
                ->comment('Enable audit trail logging for this workflow');
        });
    }

    public function down()
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn('audit_trail_enabled');
        });
    }
};
