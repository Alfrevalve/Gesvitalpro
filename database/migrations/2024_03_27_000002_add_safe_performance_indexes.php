<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Función auxiliar para verificar si existe un índice
        $hasIndex = function ($table, $index) {
            return collect(DB::select("SHOW INDEXES FROM {$table}"))
                ->pluck('Key_name')
                ->contains($index);
        };

        // Optimizar equipment
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('equipment', 'equipment_maintenance_status_index')) {
                    $table->index(['status', 'next_maintenance'], 'equipment_maintenance_status_index');
                }
            });
        }

        // Optimizar storage_processes
        if (Schema::hasTable('storage_processes')) {
            Schema::table('storage_processes', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('storage_processes', 'storage_priority_status_index')) {
                    $table->index(['priority', 'status'], 'storage_priority_status_index');
                }
            });
        }

        // Optimizar instituciones
        if (Schema::hasTable('instituciones')) {
            Schema::table('instituciones', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('instituciones', 'instituciones_tipo_red_index')) {
                    $table->index(['tipo_establecimiento', 'red_salud'], 'instituciones_tipo_red_index');
                }
            });
        }

        // Optimizar medicos
        if (Schema::hasTable('medicos')) {
            Schema::table('medicos', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('medicos', 'medicos_especialidad_index')) {
                    $table->index('especialidad', 'medicos_especialidad_index');
                }
            });
        }

        // Optimizar surgery_materials
        if (Schema::hasTable('surgery_materials')) {
            Schema::table('surgery_materials', function (Blueprint $table) use ($hasIndex) {
                if (!$hasIndex('surgery_materials', 'surgery_materials_status_index')) {
                    $table->index(['status', 'surgery_id'], 'surgery_materials_status_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices solo si existen
        if (Schema::hasTable('equipment')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropIndexIfExists('equipment_maintenance_status_index');
            });
        }

        if (Schema::hasTable('storage_processes')) {
            Schema::table('storage_processes', function (Blueprint $table) {
                $table->dropIndexIfExists('storage_priority_status_index');
            });
        }

        if (Schema::hasTable('instituciones')) {
            Schema::table('instituciones', function (Blueprint $table) {
                $table->dropIndexIfExists('instituciones_tipo_red_index');
            });
        }

        if (Schema::hasTable('medicos')) {
            Schema::table('medicos', function (Blueprint $table) {
                $table->dropIndexIfExists('medicos_especialidad_index');
            });
        }

        if (Schema::hasTable('surgery_materials')) {
            Schema::table('surgery_materials', function (Blueprint $table) {
                $table->dropIndexIfExists('surgery_materials_status_index');
            });
        }
    }
};
