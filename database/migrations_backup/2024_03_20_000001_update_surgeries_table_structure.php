<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Primero crear las columnas temporales
        Schema::table('surgeries', function (Blueprint $table) {
            $table->foreignId('institucion_id')->nullable()->after('line_id');
            $table->foreignId('medico_id')->nullable()->after('institucion_id');
            $table->dateTime('fecha')->nullable()->after('admission_date');
        });

        // Migrar datos existentes
        DB::table('surgeries')->orderBy('id')->chunk(100, function ($surgeries) {
            foreach ($surgeries as $surgery) {
                // Buscar o crear la institución
                $institucionId = DB::table('instituciones')
                    ->where('nombre', $surgery->institution)
                    ->value('id');
                
                if (!$institucionId) {
                    $institucionId = DB::table('instituciones')->insertGetId([
                        'nombre' => $surgery->institution,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Buscar o crear el médico
                $medicoId = DB::table('medicos')
                    ->where('nombre', $surgery->doctor)
                    ->value('id');
                
                if (!$medicoId) {
                    $medicoId = DB::table('medicos')->insertGetId([
                        'nombre' => $surgery->doctor,
                        'institucion_id' => $institucionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Actualizar la cirugía
                DB::table('surgeries')
                    ->where('id', $surgery->id)
                    ->update([
                        'institucion_id' => $institucionId,
                        'medico_id' => $medicoId,
                        'fecha' => $surgery->surgery_date,
                    ]);
            }
        });

        // Hacer las columnas obligatorias y eliminar las viejas
        Schema::table('surgeries', function (Blueprint $table) {
            // Hacer las nuevas columnas obligatorias
            $table->foreignId('institucion_id')->nullable(false)->change();
            $table->foreignId('medico_id')->nullable(false)->change();
            $table->dateTime('fecha')->nullable(false)->change();

            // Eliminar las columnas viejas
            $table->dropColumn(['institution', 'doctor']);

            // Actualizar el enum de status
            $table->dropColumn('status');
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled',
                'rescheduled'
            ])->default('pending')->after('fecha');

            // Renombrar notes a description si no existe
            if (!Schema::hasColumn('surgeries', 'description')) {
                $table->renameColumn('notes', 'description');
            }

            // Agregar índices para mejorar el rendimiento
            $table->index(['line_id', 'status']);
            $table->index(['institucion_id', 'status']);
            $table->index(['medico_id', 'status']);
            $table->index('fecha');
            $table->index('created_at');
        });

        // Asegurarse de que existan las tablas pivote
        if (!Schema::hasTable('equipment_surgery')) {
            Schema::create('equipment_surgery', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
                $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('surgery_staff')) {
            Schema::create('surgery_staff', function (Blueprint $table) {
                $table->id();
                $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('role');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::table('surgeries', function (Blueprint $table) {
            // Restaurar columnas originales
            $table->string('institution')->after('line_id');
            $table->string('doctor')->after('institution');
            
            // Eliminar nuevas columnas
            $table->dropColumn(['institucion_id', 'medico_id', 'fecha']);
            
            // Restaurar status original
            $table->dropColumn('status');
            $table->enum('status', [
                'programmed',
                'in_progress',
                'finished',
                'cancelled',
                'rescheduled'
            ])->default('programmed');

            // Restaurar notes si fue renombrado
            if (Schema::hasColumn('surgeries', 'description')) {
                $table->renameColumn('description', 'notes');
            }

            // Eliminar índices
            $table->dropIndex(['line_id', 'status']);
            $table->dropIndex(['institucion_id', 'status']);
            $table->dropIndex(['medico_id', 'status']);
            $table->dropIndex(['fecha']);
            $table->dropIndex(['created_at']);
        });
    }
};
