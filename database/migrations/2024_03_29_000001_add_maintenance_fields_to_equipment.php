<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('type')->after('name')->nullable();
            $table->timestamp('last_maintenance')->after('status')->nullable();
            $table->timestamp('next_maintenance')->after('last_maintenance')->nullable();
            $table->unique('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['type', 'last_maintenance', 'next_maintenance']);
            $table->dropUnique(['serial_number']);
        });
    }
};
