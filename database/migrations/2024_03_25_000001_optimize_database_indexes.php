<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function hasIndex($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }

    public function up()
    {
        Schema::table('surgeries', function (Blueprint $table) {
            $table->index(['line_id', 'status', 'surgery_date']);
            $table->index(['medico_id', 'surgery_date']);
            $table->index(['institucion_id', 'status']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            if (!$this->hasIndex('activity_logs', 'activity_logs_created_at_index')) {
                $table->index(['created_at']);
            }
            if (!$this->hasIndex('activity_logs', 'activity_logs_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
            if (!$this->hasIndex('activity_logs', 'activity_logs_loggable_type_loggable_id_index')) {
                $table->index(['loggable_type', 'loggable_id']);
            }
        });
    }

    public function down()
    {
        Schema::table('surgeries', function (Blueprint $table) {
            $table->dropIndex(['line_id', 'status', 'surgery_date']);
            $table->dropIndex(['medico_id', 'surgery_date']);
            $table->dropIndex(['institucion_id', 'status']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['loggable_type', 'loggable_id']);
        });
    }
};
