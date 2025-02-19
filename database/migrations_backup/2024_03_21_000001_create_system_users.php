<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asegurar que la columna role existe en la tabla users
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user');
            });
        }

        // Crear los usuarios del sistema
        $users = [
            // Administradores
            [
                'name' => 'Administrador Principal',
                'email' => 'admin.principal@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrador Secundario',
                'email' => 'admin.secundario@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Jefes de Línea
            [
                'name' => 'Jefe Línea Cx',
                'email' => 'jefe.cx@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'line_manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jefe Línea Nx',
                'email' => 'jefe.nx@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'line_manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jefe Línea Cr',
                'email' => 'jefe.cr@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'line_manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Instrumentistas Cx (Cirugía)
            [
                'name' => 'Instrumentista Cx 1',
                'email' => 'inst.cx1@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'instrumentist',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Instrumentista Cx 2',
                'email' => 'inst.cx2@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'instrumentist',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Instrumentistas Nx (Neurología)
            [
                'name' => 'Instrumentista Nx 1',
                'email' => 'inst.nx1@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'instrumentist',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Instrumentista Nx 2',
                'email' => 'inst.nx2@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'instrumentist',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Instrumentistas Cr (Cirugía Reconstructiva)
            [
                'name' => 'Instrumentista Cr 1',
                'email' => 'inst.cr1@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'instrumentist',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Instrumentista Cr 2',
                'email' => 'inst.cr2@gesbio.com',
                'password' => Hash::make('GesBio2024#'),
                'role' => 'instrumentist',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insertar los usuarios
        DB::table('users')->insert($users);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar los usuarios creados
        DB::table('users')->whereIn('email', [
            'admin.principal@gesbio.com',
            'admin.secundario@gesbio.com',
            'jefe.cx@gesbio.com',
            'jefe.nx@gesbio.com',
            'jefe.cr@gesbio.com',
            'inst.cx1@gesbio.com',
            'inst.cx2@gesbio.com',
            'inst.nx1@gesbio.com',
            'inst.nx2@gesbio.com',
            'inst.cr1@gesbio.com',
            'inst.cr2@gesbio.com',
        ])->delete();
    }
};
