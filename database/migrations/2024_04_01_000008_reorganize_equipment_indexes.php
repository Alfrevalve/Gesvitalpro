<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Primero, asegurarnos de que existan todas las columnas necesarias
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'next_maintenance_date')) {
                $table->date('next_maintenance_date')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'last_maintenance_date')) {
                $table->date('last_maintenance_date')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'maintenance_interval')) {
                $table->integer('maintenance_interval')->default(30);
            }
            if (!Schema::hasColumn('equipment', 'status')) {
                $table->string('status')->default('available');
            }
        });

        // Luego, crear los índices verificando su existencia
        Schema::table('equipment', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEXES FROM equipment");
            $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

            if (!in_array('idx_equipment_status', $existingIndexes)) {
                $table->index('status', 'idx_equipment_status');
            }

            if (!in_array('idx_equipment_maintenance_date', $existingIndexes)) {
                $table->index('next_maintenance_date', 'idx_equipment_maintenance_date');
            }

            if (!in_array('idx_equipment_status_maintenance', $existingIndexes)) {
                $table->index(['status', 'next_maintenance_date'], 'idx_equipment_status_maintenance');
            }
        });

        // Crear y optimizar tabla de mantenimiento si no existe
        if (!Schema::hasTable('equipment_maintenance_records')) {
            Schema::create('equipment_maintenance_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
                $table->date('maintenance_date');
                $table->string('type')->default('regular');
                $table->text('description')->nullable();
                $table->string('technician')->nullable();
                $table->decimal('cost', 10, 2)->nullable();
                $table->string('status')->default('completed');
                $table->timestamps();
                $table->softDeletes();

                // Índices
                $table->index(['equipment_id', 'maintenance_date'], 'idx_maintenance_equipment_date');
                $table->index('status', 'idx_maintenance_status');
            });
        }

        // Optimizar tabla de relación con cirugías si existe
        if (Schema::hasTable('surgery_equipment')) {
            Schema::table('surgery_equipment', function (Blueprint $table) {
                $indexes = DB::select("SHOW INDEXES FROM surgery_equipment");
                $existingIndexes = collect($indexes)->pluck('Key_name')->toArray();

                if (!in_array('idx_surgery_equipment', $existingIndexes)) {
                    $table->index(['surgery_id', 'equipment_id'], 'idx_surgery_equipment');
                }

                if (!in_array('idx_equipment_surgery', $existingIndexes)) {
                    $table->index(['equipment_id', 'surgery_id'], 'idx_equipment_surgery');
                }
            });
        }
    }

    public function down()
    {
        // Eliminar índices de equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex('idx_equipment_status');
            $table->dropIndex('idx_equipment_maintenance_date');
            $table->dropIndex('idx_equipment_status_maintenance');
        });

        // Eliminar índices de maintenance_records
        if (Schema::hasTable('equipment_maintenance_records')) {
            Schema::table('equipment_maintenance_records', function (Blueprint $table) {
                $table->dropIndex('idx_maintenance_equipment_date');
                $table->dropIndex('idx_maintenance_status');
            });
        }

        // Eliminar índices de surgery_equipment
        if (Schema::hasTable('surgery_equipment')) {
            Schema::table('surgery_equipment', function (Blueprint $table) {
                $table->dropIndex('idx_surgery_equipment');
                $table->dropIndex('idx_equipment_surgery');
            });
        }

        // Eliminar columnas de equipment
        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'next_maintenance_date')) {
                $table->dropColumn('next_maintenance_date');
            }
            if (Schema::hasColumn('equipment', 'last_maintenance_date')) {
                $table->dropColumn('last_maintenance_date');
            }
            if (Schema::hasColumn('equipment', 'maintenance_interval')) {
                $table->dropColumn('maintenance_interval');
            }
        });

        // Eliminar tabla de mantenimiento
        Schema::dropIfExists('equipment_maintenance_records');
    }
};
