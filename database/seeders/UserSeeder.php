<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create admin user
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@gesvitalpro.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $admin->assignRole($adminRole);

        // Create test user
        $user = User::create([
            'name' => 'Usuario de Prueba',
            'email' => 'usuario@gesvitalpro.com',
            'password' => Hash::make('usuario123'),
            'email_verified_at' => now(),
        ]);

        // Assign user role
        $user->assignRole($userRole);
    }
}
