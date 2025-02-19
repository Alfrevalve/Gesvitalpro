<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'line_manager', 'instrumentist'])->after('password');
            $table->foreignId('line_id')->nullable()->after('role')->constrained();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['line_id']);
            $table->dropColumn(['role', 'line_id']);
        });
    }
};
