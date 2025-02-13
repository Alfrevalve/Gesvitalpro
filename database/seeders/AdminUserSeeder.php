<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear rol de administrador si no existe
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@gesvitalpro.com',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
            'settings' => [
                'notifications_enabled' => true,
                'theme' => 'light'
            ],
        ]);

        // Asignar rol de administrador
        $admin->assignRole($adminRole);

        // Crear rol de usuario si no existe
        Role::firstOrCreate(['name' => 'user']);
    }
}
