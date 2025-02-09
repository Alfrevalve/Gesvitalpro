<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol de administrador si no existe
        DB::table('roles')->updateOrInsert(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrador',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Crear rol de usuario si no existe
        DB::table('roles')->updateOrInsert(
            ['name' => 'user'],
            [
                'display_name' => 'Usuario',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Obtener el ID del rol admin
        $adminRoleId = DB::table('roles')->where('name', 'admin')->first()->id;

        // Crear o actualizar el usuario administrador
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@gesvitalpro.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRoleId,
                'avatar' => 'users/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Crear o actualizar el usuario administrador
        $user = DB::table('users')->updateOrInsert(
            ['email' => 'admin@gesvitalpro.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRoleId,
                'avatar' => 'users/default.png',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Obtener el ID del usuario
        $userId = DB::table('users')->where('email', 'admin@gesvitalpro.com')->first()->id;

        // Asignar rol al usuario en la tabla user_roles
        DB::table('user_roles')->updateOrInsert([
            'user_id' => $userId,
            'role_id' => $adminRoleId
        ]);
    }
}
