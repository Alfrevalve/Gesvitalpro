<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monitoring_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('system_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->string('unit')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->decimal('value', 10, 2);
            $table->string('unit')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('level');
            $table->string('message');
            $table->json('metadata')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['type', 'level']);
            $table->index('resolved');
        });

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->string('message');
            $table->json('context')->nullable();
            $table->timestamps();
            
            // Index
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('system_metrics');
        Schema::dropIfExists('monitoring_configs');
    }
};
