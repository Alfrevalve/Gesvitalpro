<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')
                    ->after('password')
                    ->nullable();

            $table->text('two_factor_recovery_codes')
                    ->after('two_factor_secret')
                    ->nullable();
            
            $table->timestamp('two_factor_confirmed_at')
                    ->after('two_factor_recovery_codes')
                    ->nullable();

            $table->boolean('two_factor_enabled')
                    ->after('two_factor_confirmed_at')
                    ->default(false);

            $table->integer('login_attempts')
                    ->after('two_factor_enabled')
                    ->default(0);

            $table->timestamp('locked_at')
                    ->after('login_attempts')
                    ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'two_factor_enabled',
                'login_attempts',
                'locked_at'
            ]);
        });
    }
};
