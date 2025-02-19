<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('medicos', function (Blueprint $table) {
            if (!Schema::hasColumn('medicos', 'estado')) {
                $table->string('estado')->default('activo')->after('email');
            }
            if (!Schema::hasColumn('medicos', 'especialidad')) {
                $table->string('especialidad')->after('name')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropColumn(['estado', 'especialidad']);
        });
    }
};
