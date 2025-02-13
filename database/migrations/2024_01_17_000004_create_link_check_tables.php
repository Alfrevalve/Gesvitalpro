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
        Schema::create('link_check_exclusions', function (Blueprint $table) {
            $table->id();
            $table->string('pattern');
            $table->string('reason')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('broken_links', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('source')->nullable();
            $table->string('status');
            $table->integer('check_count')->default(0);
            $table->boolean('is_fixed')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('fixed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('link_check_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broken_link_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->json('response_data')->nullable();
            $table->float('duration')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_check_history');
        Schema::dropIfExists('broken_links');
        Schema::dropIfExists('link_check_exclusions');
    }
};
