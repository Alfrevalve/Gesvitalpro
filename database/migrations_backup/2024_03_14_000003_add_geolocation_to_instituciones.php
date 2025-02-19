<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('instituciones', function (Blueprint $table) {
            // Solo agregar columnas si no existen
            if (!Schema::hasColumn('instituciones', 'latitud')) {
                $table->decimal('latitud', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('instituciones', 'longitud')) {
                $table->decimal('longitud', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('instituciones', 'datos_ubicacion')) {
                $table->json('datos_ubicacion')->nullable();
            }

            // Agregar índices para búsquedas geográficas
            try {
                $table->index(['latitud', 'longitud'], 'instituciones_latitud_longitud_index');
            } catch (\Exception $e) {
                // El índice probablemente ya existe, ignorar el error
            }
        });
    }

    public function down()
    {
        Schema::table('instituciones', function (Blueprint $table) {
            // Eliminar índices
            try {
                $table->dropIndex('instituciones_latitud_longitud_index');
            } catch (\Exception $e) {
                // El índice probablemente no existe, ignorar el error
            }

            // Eliminar columnas si existen
            $columnsToRemove = [];
            
            if (Schema::hasColumn('instituciones', 'datos_ubicacion')) {
                $columnsToRemove[] = 'datos_ubicacion';
            }
            if (Schema::hasColumn('instituciones', 'longitud')) {
                $columnsToRemove[] = 'longitud';
            }
            if (Schema::hasColumn('instituciones', 'latitud')) {
                $columnsToRemove[] = 'latitud';
            }

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
