<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('surgery_requests', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('status');
        });

        // Set default priority for existing records
        DB::table('surgery_requests')->update(['priority' => 'normal']);
    }

    public function down()
    {
        Schema::table('surgery_requests', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
