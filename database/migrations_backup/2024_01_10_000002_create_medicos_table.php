<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('especialidad')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('estado')->default('active');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('estado');
            $table->index('especialidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
