<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class CreateUsersWithRolesSeeder extends Seeder
{
    public function run()
    {
        // First ensure we have all roles
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Acceso total al sistema',
                'level' => 100,
            ],
            [
                'name' => 'Gerente',
                'slug' => 'gerente',
                'description' => 'Gestión general del sistema',
                'level' => 90,
            ],
            [
                'name' => 'Jefe de Línea',
                'slug' => 'jefe_linea',
                'description' => 'Gestión de una línea específica',
                'level' => 80,
            ],
            [
                'name' => 'Instrumentista',
                'slug' => 'instrumentista',
                'description' => 'Operaciones técnicas en una línea',
                'level' => 70,
            ],
            [
                'name' => 'Vendedor',
                'slug' => 'vendedor',
                'description' => 'Gestión de ventas en una línea',
                'level' => 70,
            ],
            [
                'name' => 'Almacén',
                'slug' => 'storage',
                'description' => 'Personal de almacén que prepara los materiales',
                'level' => 60,
            ],
            [
                'name' => 'Despacho',
                'slug' => 'dispatch',
                'description' => 'Personal de despacho que entrega los materiales',
                'level' => 60,
            ],
        ];

        // Create or update roles
        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Create users for each role
        $users = [
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@gesbio.com',
                'password' => 'admin123',
                'role' => 'admin'
            ],
            [
                'name' => 'Gerente General',
                'email' => 'gerente@gesbio.com',
                'password' => 'gerente123',
                'role' => 'gerente'
            ],
            [
                'name' => 'Jefe de Línea',
                'email' => 'jefe@gesbio.com',
                'password' => 'jefe123',
                'role' => 'jefe_linea'
            ],
            [
                'name' => 'Instrumentista Principal',
                'email' => 'instrumentista@gesbio.com',
                'password' => 'instrumentista123',
                'role' => 'instrumentista'
            ],
            [
                'name' => 'Vendedor Principal',
                'email' => 'vendedor@gesbio.com',
                'password' => 'vendedor123',
                'role' => 'vendedor'
            ],
            [
                'name' => 'Encargado Almacén',
                'email' => 'almacen@gesbio.com',
                'password' => 'almacen123',
                'role' => 'storage'
            ],
            [
                'name' => 'Encargado Despacho',
                'email' => 'despacho@gesbio.com',
                'password' => 'despacho123',
                'role' => 'dispatch'
            ]
        ];

        // Create users and assign roles
        foreach ($users as $userData) {
            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now()
                ]
            );

            // Get role
            $role = Role::where('slug', $userData['role'])->first();

            if ($role) {
                // Assign role to user
                DB::table('role_user')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'role_id' => $role->id
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
