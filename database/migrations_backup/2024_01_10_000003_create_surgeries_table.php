<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained('lines')->onDelete('cascade');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->string('patient_name');
            $table->string('surgery_type');
            $table->dateTime('surgery_date');
            $table->dateTime('admission_date');
            $table->dateTime('fecha');
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled',
                'rescheduled'
            ])->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['line_id', 'status']);
            $table->index(['institucion_id', 'status']);
            $table->index(['medico_id', 'status']);
            $table->index('fecha');
            $table->index('created_at');
        });

        Schema::create('equipment_surgery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índice para la relación
            $table->index(['surgery_id', 'equipment_id']);
        });

        Schema::create('surgery_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role');
            $table->timestamps();

            // Índice para la relación
            $table->index(['surgery_id', 'user_id']);
            $table->index('role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('surgery_staff');
        Schema::dropIfExists('equipment_surgery');
        Schema::dropIfExists('surgeries');
    }
};
