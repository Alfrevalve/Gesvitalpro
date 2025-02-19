<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add soft deletes to tables that need it
        $tables = [
            'medicos',
            'instituciones',
            'equipment',
            'surgeries',
            'visitas',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }

        // Ensure activity_logs table has correct structure
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable();
                $table->string('description');
                $table->nullableMorphs('subject');
                $table->nullableMorphs('causer');
                $table->json('properties')->nullable();
                $table->timestamps();
                $table->index('log_name');
                $table->index(['subject_type', 'subject_id']);
                $table->index(['causer_type', 'causer_id']);
            });
        }

        // Ensure users table has required columns
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false);
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Remove soft deletes
        $tables = [
            'medicos',
            'instituciones',
            'equipment',
            'surgeries',
            'visitas',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }

        // Drop activity_logs table
        Schema::dropIfExists('activity_logs');

        // Remove added columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'avatar']);
        });
    }
};
