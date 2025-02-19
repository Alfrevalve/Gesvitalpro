<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Line;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create roles if they don't exist
        foreach (Role::defaultRoles() as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                ['description' => $roleData['description']]
            );
        }

        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@gesbio.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@gesbio.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ])->assignRole('admin');
        }

        // Create basic lines
        $lines = [
            ['name' => 'Línea de Cráneo', 'code' => 'craneo'],
            ['name' => 'Línea de Columna', 'code' => 'columna'],
            ['name' => 'Línea de Neurocirugía', 'code' => 'neurocirugia'],
            ['name' => 'Línea de Cirugía', 'code' => 'cirugia']
        ];

        foreach ($lines as $lineData) {
            Line::firstOrCreate(
                ['code' => $lineData['code']],
                [
                    'name' => $lineData['name'],
                    'description' => 'Descripción de ' . $lineData['name']
                ]
            );
        }
    }
}
