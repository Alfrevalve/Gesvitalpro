<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminSeeder extends Seeder
{
    public function run()
    {
        // Crear rol de administrador
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'description' => 'Administrador del sistema',
                'guard_name' => 'web'
            ]
        );

        // Crear usuario administrador
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol de administrador
        $admin->roles()->sync([$adminRole->id]);
    }
}
