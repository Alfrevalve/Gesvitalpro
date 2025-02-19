<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('storage_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->string('priority')->default('normal');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
        });

        Schema::create('dispatch_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->timestamp('dispatched_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('dispatched_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dispatch_processes');
        Schema::dropIfExists('storage_processes');
    }
};
