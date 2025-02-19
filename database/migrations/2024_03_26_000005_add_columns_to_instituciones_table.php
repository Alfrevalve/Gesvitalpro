<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('instituciones', function (Blueprint $table) {
            if (!Schema::hasColumn('instituciones', 'ciudad')) {
                $table->string('ciudad')->after('address')->nullable();
            }
            if (!Schema::hasColumn('instituciones', 'tipo_establecimiento')) {
                $table->string('tipo_establecimiento')->after('name')->nullable();
            }
            if (!Schema::hasColumn('instituciones', 'red_salud')) {
                $table->string('red_salud')->after('tipo_establecimiento')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropColumn(['ciudad', 'tipo_establecimiento', 'red_salud']);
        });
    }
};
