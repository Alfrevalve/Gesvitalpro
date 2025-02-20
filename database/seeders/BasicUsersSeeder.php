<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BasicUsersSeeder extends Seeder
{
    public function run()
    {
        // Crear usuarios básicos
        $users = [
            [
                'name' => 'Administrador',
                'email' => 'admin@gesbio.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ],
            [
                'name' => 'Gerente',
                'email' => 'gerente@gesbio.com',
                'password' => Hash::make('gerente123'),
                'role' => 'gerente'
            ],
            [
                'name' => 'Supervisor',
                'email' => 'supervisor@gesbio.com',
                'password' => Hash::make('supervisor123'),
                'role' => 'supervisor'
            ],
            [
                'name' => 'Vendedor',
                'email' => 'vendedor@gesbio.com',
                'password' => Hash::make('vendedor123'),
                'role' => 'vendedor'
            ],
            [
                'name' => 'Técnico',
                'email' => 'tecnico@gesbio.com',
                'password' => Hash::make('tecnico123'),
                'role' => 'tecnico'
            ]
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            // Crear usuario
            $user = User::create($userData);

            // Asignar rol
            $roleId = DB::table('roles')->where('name', $role)->value('id');
            if ($roleId) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id
                ]);
            }
        }
    }
}
