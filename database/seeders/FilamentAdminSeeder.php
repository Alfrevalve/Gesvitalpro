<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FilamentAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el rol de administrador
        $adminRole = Role::where('slug', 'admin')->first();

        if (!$adminRole) {
            // Si no existe el rol admin, lo creamos
            $adminRole = Role::create([
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Administrador del sistema',
                'level' => 100,
            ]);
        }

        // Crear usuario administrador si no existe
        if (!User::where('email', 'admin@gesbio.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@gesbio.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]);
        }
    }
}
