<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Desactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Eliminar todas las tablas existentes
        Schema::dropIfExists('surgery_externo');
        Schema::dropIfExists('visita_externo');
        Schema::dropIfExists('zona_institucion');
        Schema::dropIfExists('institucion_line');
        Schema::dropIfExists('line_staff');
        Schema::dropIfExists('surgery_staff');
        Schema::dropIfExists('surgery_equipment');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('dispatch_processes');
        Schema::dropIfExists('surgery_materials');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('visitas');
        Schema::dropIfExists('externos');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('surgery_requests');
        Schema::dropIfExists('storage_processes');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('medicos');
        Schema::dropIfExists('instituciones');
        Schema::dropIfExists('users');
        Schema::dropIfExists('lines');

        // Reactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Crear tabla de líneas
        Schema::create('lines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Crear tabla de instituciones
        Schema::create('instituciones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Crear tabla de médicos
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->string('name');
            $table->string('specialty');
            $table->string('license_number')->unique();
            $table->string('phone');
            $table->string('email');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Crear tabla de usuarios
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('line_id')->nullable()->constrained()->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Crear tabla de roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Crear tabla de permisos
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Crear tabla pivote roles_users
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->primary(['role_id', 'user_id']);
        });

        // Crear tabla pivote permission_role
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->primary(['permission_id', 'role_id']);
        });

        // Crear tabla de visitas
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained()->onDelete('cascade');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->datetime('fecha');
            $table->string('estado')->default('pendiente');
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['medico_id', 'fecha']);
            $table->index('estado');
        });

        // Crear tabla de equipamiento
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('serial_number')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'retired']);
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['line_id', 'status']);
        });

        // Crear tabla de logs de actividad
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action')->nullable();
            $table->nullableMorphs('loggable');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('changes')->nullable();
            $table->json('original')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index('user_id');
            $table->index('action');
            $table->index('event');
            $table->index('created_at');
        });

        // Crear tabla de cirugías
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained()->onDelete('cascade');
            $table->foreignId('institucion_id')->constrained('instituciones')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->string('patient_name');
            $table->string('surgery_type');
            $table->datetime('surgery_date');
            $table->datetime('admission_date');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'rescheduled']);
            $table->timestamps();
            $table->softDeletes();

            // Agregar índices
            $table->index('status');
            $table->index('surgery_date');
            $table->index(['status', 'surgery_date']);
        });

        // Crear tabla pivote surgery_equipment
        Schema::create('surgery_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Crear tabla pivote surgery_staff
        Schema::create('surgery_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // e.g., 'instrumentista', 'asistente'
            $table->timestamps();
        });

        // Crear tabla pivote line_staff
        Schema::create('line_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        // Desactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Eliminar tablas en orden inverso
        Schema::dropIfExists('surgery_externo');
        Schema::dropIfExists('visita_externo');
        Schema::dropIfExists('zona_institucion');
        Schema::dropIfExists('institucion_line');
        Schema::dropIfExists('line_staff');
        Schema::dropIfExists('surgery_staff');
        Schema::dropIfExists('surgery_equipment');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('dispatch_processes');
        Schema::dropIfExists('surgery_materials');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('visitas');
        Schema::dropIfExists('externos');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('surgery_requests');
        Schema::dropIfExists('storage_processes');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('medicos');
        Schema::dropIfExists('instituciones');
        Schema::dropIfExists('users');
        Schema::dropIfExists('lines');

        // Reactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
