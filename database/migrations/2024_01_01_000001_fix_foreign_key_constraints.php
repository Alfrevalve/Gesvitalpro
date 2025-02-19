<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Desactivar restricciones de clave foránea temporalmente
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

        // Reactivar restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // No es necesario hacer nada en down() ya que este es un fix para las migraciones
    }
};
