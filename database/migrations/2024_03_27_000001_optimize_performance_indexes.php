<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Optimizar búsquedas en equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->index(['status', 'next_maintenance']);
            $table->index(['line_id', 'status', 'next_maintenance']);
        });

        // Optimizar búsquedas en surgery_materials
        Schema::table('surgery_materials', function (Blueprint $table) {
            $table->index(['surgery_id', 'status']);
        });

        // Optimizar búsquedas en storage_processes
        Schema::table('storage_processes', function (Blueprint $table) {
            $table->index(['status', 'priority', 'created_at']);
            $table->index(['prepared_by', 'status']);
        });

        // Optimizar búsquedas en instituciones
        Schema::table('instituciones', function (Blueprint $table) {
            $table->index(['tipo_establecimiento', 'ciudad']);
            $table->index(['red_salud']);
        });

        // Optimizar búsquedas en medicos
        Schema::table('medicos', function (Blueprint $table) {
            $table->index(['especialidad', 'estado']);
            $table->index(['institucion_id', 'especialidad']);
        });

        // Optimizar búsquedas en dispatch_processes
        Schema::table('dispatch_processes', function (Blueprint $table) {
            $table->index(['status', 'dispatched_at']);
            $table->index(['dispatched_by', 'status']);
        });
    }

    public function down()
    {
        // Revertir optimizaciones en equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex(['status', 'next_maintenance']);
            $table->dropIndex(['line_id', 'status', 'next_maintenance']);
        });

        // Revertir optimizaciones en surgery_materials
        Schema::table('surgery_materials', function (Blueprint $table) {
            $table->dropIndex(['surgery_id', 'status']);
        });

        // Revertir optimizaciones en storage_processes
        Schema::table('storage_processes', function (Blueprint $table) {
            $table->dropIndex(['status', 'priority', 'created_at']);
            $table->dropIndex(['prepared_by', 'status']);
        });

        // Revertir optimizaciones en instituciones
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropIndex(['tipo_establecimiento', 'ciudad']);
            $table->dropIndex(['red_salud']);
        });

        // Revertir optimizaciones en medicos
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropIndex(['especialidad', 'estado']);
            $table->dropIndex(['institucion_id', 'especialidad']);
        });

        // Revertir optimizaciones en dispatch_processes
        Schema::table('dispatch_processes', function (Blueprint $table) {
            $table->dropIndex(['status', 'dispatched_at']);
            $table->dropIndex(['dispatched_by', 'status']);
        });
    }
};
