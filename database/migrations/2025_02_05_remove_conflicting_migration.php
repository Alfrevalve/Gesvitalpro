<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveConflictingMigration extends Migration
{
    public function up()
    {
        DB::table('migrations')->where('migration', '2025_01_01_create_all_tables_final')->delete();
    }

    public function down()
    {
        // This method can be left empty as we don't need to reverse this action
    }
}
