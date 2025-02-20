<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Verificar y crear índices solo si no existen
        $this->createSurgeryIndexes();
        $this->createMaterialIndexes();
        $this->createMetricsIndexes();
        $this->createLogIndexes();
    }

    protected function createSurgeryIndexes()
    {
        $indexes = DB::select("SHOW INDEXES FROM surgeries");
        $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

        Schema::table('surgeries', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('idx_surgeries_date_status', $existingIndexes)) {
                $table->index(['surgery_date', 'status'], 'idx_surgeries_date_status');
            }
            if (!in_array('idx_surgeries_medico_date', $existingIndexes)) {
                $table->index(['medico_id', 'surgery_date'], 'idx_surgeries_medico_date');
            }
            if (!in_array('idx_surgeries_institucion_date', $existingIndexes)) {
                $table->index(['institucion_id', 'surgery_date'], 'idx_surgeries_institucion_date');
            }
        });
    }

    protected function createMaterialIndexes()
    {
        if (Schema::hasTable('surgery_materials')) {
            $indexes = DB::select("SHOW INDEXES FROM surgery_materials");
            $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

            Schema::table('surgery_materials', function (Blueprint $table) use ($existingIndexes) {
                if (!in_array('idx_materials_status_date', $existingIndexes)) {
                    $table->index(['status', 'created_at'], 'idx_materials_status_date');
                }
                if (!in_array('idx_materials_surgery_status', $existingIndexes)) {
                    $table->index(['surgery_id', 'status'], 'idx_materials_surgery_status');
                }
            });
        }
    }

    protected function createMetricsIndexes()
    {
        if (Schema::hasTable('surgery_metrics')) {
            $indexes = DB::select("SHOW INDEXES FROM surgery_metrics");
            $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

            Schema::table('surgery_metrics', function (Blueprint $table) use ($existingIndexes) {
                if (!in_array('idx_metrics_date_type', $existingIndexes)) {
                    $table->index(['date', 'metric_type'], 'idx_metrics_date_type');
                }
                if (!in_array('idx_metrics_type_value', $existingIndexes)) {
                    $table->index(['metric_type', 'value'], 'idx_metrics_type_value');
                }
            });
        }
    }

    protected function createLogIndexes()
    {
        if (Schema::hasTable('surgical_process_logs')) {
            $indexes = DB::select("SHOW INDEXES FROM surgical_process_logs");
            $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

            Schema::table('surgical_process_logs', function (Blueprint $table) use ($existingIndexes) {
                if (!in_array('idx_process_logs_date_event', $existingIndexes)) {
                    $table->index(['created_at', 'event_type'], 'idx_process_logs_date_event');
                }
                if (!in_array('idx_process_logs_process_date', $existingIndexes)) {
                    $table->index(['process_id', 'created_at'], 'idx_process_logs_process_date');
                }
            });
        }
    }

    public function down()
    {
        // Eliminar todos los índices creados
        Schema::table('surgeries', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_surgeries_date_status');
            $table->dropIndexIfExists('idx_surgeries_medico_date');
            $table->dropIndexIfExists('idx_surgeries_institucion_date');
        });

        if (Schema::hasTable('surgery_materials')) {
            Schema::table('surgery_materials', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_materials_status_date');
                $table->dropIndexIfExists('idx_materials_surgery_status');
            });
        }

        if (Schema::hasTable('surgery_metrics')) {
            Schema::table('surgery_metrics', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_metrics_date_type');
                $table->dropIndexIfExists('idx_metrics_type_value');
            });
        }

        if (Schema::hasTable('surgical_process_logs')) {
            Schema::table('surgical_process_logs', function (Blueprint $table) {
                $table->dropIndexIfExists('idx_process_logs_date_event');
                $table->dropIndexIfExists('idx_process_logs_process_date');
            });
        }
    }
};
