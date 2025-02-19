<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeDatabaseIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Optimizar índices de cirugías
        Schema::table('surgeries', function (Blueprint $table) {
            $table->index(['line_id', 'status', 'surgery_date'], 'surgeries_search_index');
        });

        // Optimizar índices de visitas
        Schema::table('visitas', function (Blueprint $table) {
            if (!Schema::hasColumn('visitas', 'estado')) {
                $table->string('estado')->default('pendiente')->after('fecha');
            }
            $table->index(['medico_id', 'fecha', 'estado'], 'visitas_search_index');
        });

        // Optimizar índices de médicos
        Schema::table('medicos', function (Blueprint $table) {
            if (!Schema::hasColumn('medicos', 'specialty')) {
                $table->string('specialty')->after('name')->nullable();
            }
            $table->index(['specialty'], 'medicos_search_index');
        });

        // Optimizar índices de instituciones
        Schema::table('instituciones', function (Blueprint $table) {
            if (!Schema::hasColumn('instituciones', 'tipo_establecimiento')) {
                $table->string('tipo_establecimiento')->after('name')->nullable();
            }
            $table->index(['tipo_establecimiento'], 'instituciones_search_index');
        });

        // Optimizar índices de equipamiento
        Schema::table('equipment', function (Blueprint $table) {
            $table->index(['line_id'], 'equipment_search_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Eliminar índices optimizados
        Schema::table('surgeries', function (Blueprint $table) {
            $table->dropIndex('surgeries_search_index');
        });

        Schema::table('visitas', function (Blueprint $table) {
            $table->dropIndex('visitas_search_index');
        });

        Schema::table('medicos', function (Blueprint $table) {
            $table->dropIndex('medicos_search_index');
        });

        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropIndex('instituciones_search_index');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropIndex('equipment_search_index');
        });
    }
}
