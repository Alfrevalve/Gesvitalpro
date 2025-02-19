<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        try {
            // Optimizar búsquedas en cirugías
            Schema::table('surgeries', function (Blueprint $table) {
                // Solo agregar índices si no existen
                $indexes = $this->getTableIndexes('surgeries');
                
                if (!in_array('surgeries_surgery_date_index', $indexes)) {
                    $table->index('surgery_date');
                }
                if (!in_array('surgeries_status_index', $indexes)) {
                    $table->index('status');
                }
                if (!Schema::hasColumn('surgeries', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Optimizar búsquedas en equipos
            Schema::table('equipment', function (Blueprint $table) {
                $indexes = $this->getTableIndexes('equipment');
                
                if (!in_array('equipment_status_index', $indexes)) {
                    $table->index('status');
                }
                if (!in_array('equipment_line_id_status_index', $indexes)) {
                    $table->index(['line_id', 'status']);
                }
                if (!in_array('equipment_next_maintenance_index', $indexes)) {
                    $table->index('next_maintenance');
                }
                if (!Schema::hasColumn('equipment', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Optimizar búsquedas en visitas
            Schema::table('visitas', function (Blueprint $table) {
                $indexes = $this->getTableIndexes('visitas');
                
                if (!in_array('visitas_fecha_hora_index', $indexes)) {
                    $table->index('fecha_hora');
                }
                if (!in_array('visitas_estado_index', $indexes)) {
                    $table->index('estado');
                }
                if (!in_array('visitas_institucion_id_fecha_hora_index', $indexes)) {
                    $table->index(['institucion_id', 'fecha_hora']);
                }
                if (!Schema::hasColumn('visitas', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Optimizar búsquedas en líneas
            Schema::table('lines', function (Blueprint $table) {
                if (!Schema::hasColumn('lines', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        } catch (\Exception $e) {
            // Log error pero continuar con la migración
            \Log::error('Error adding performance indexes: ' . $e->getMessage());
        }
    }

    public function down()
    {
        try {
            // Remover índices de cirugías
            Schema::table('surgeries', function (Blueprint $table) {
                $indexes = $this->getTableIndexes('surgeries');
                
                if (in_array('surgeries_surgery_date_index', $indexes)) {
                    $table->dropIndex('surgeries_surgery_date_index');
                }
                if (in_array('surgeries_status_index', $indexes)) {
                    $table->dropIndex('surgeries_status_index');
                }
                if (Schema::hasColumn('surgeries', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });

            // Remover índices de equipos
            Schema::table('equipment', function (Blueprint $table) {
                $indexes = $this->getTableIndexes('equipment');
                
                if (in_array('equipment_status_index', $indexes)) {
                    $table->dropIndex('equipment_status_index');
                }
                if (in_array('equipment_line_id_status_index', $indexes)) {
                    $table->dropIndex('equipment_line_id_status_index');
                }
                if (in_array('equipment_next_maintenance_index', $indexes)) {
                    $table->dropIndex('equipment_next_maintenance_index');
                }
                if (Schema::hasColumn('equipment', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });

            // Remover índices de visitas
            Schema::table('visitas', function (Blueprint $table) {
                $indexes = $this->getTableIndexes('visitas');
                
                if (in_array('visitas_fecha_hora_index', $indexes)) {
                    $table->dropIndex('visitas_fecha_hora_index');
                }
                if (in_array('visitas_estado_index', $indexes)) {
                    $table->dropIndex('visitas_estado_index');
                }
                if (in_array('visitas_institucion_id_fecha_hora_index', $indexes)) {
                    $table->dropIndex('visitas_institucion_id_fecha_hora_index');
                }
                if (Schema::hasColumn('visitas', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });

            // Remover softDeletes de líneas
            Schema::table('lines', function (Blueprint $table) {
                if (Schema::hasColumn('lines', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        } catch (\Exception $e) {
            // Log error pero continuar con la migración
            \Log::error('Error removing performance indexes: ' . $e->getMessage());
        }
    }

    private function getTableIndexes($table)
    {
        return array_map(function($index) {
            return $index->name;
        }, DB::select("SHOW INDEXES FROM {$table}"));
    }
};
