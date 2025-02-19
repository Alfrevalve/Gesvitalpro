<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Crear rol de administrador si no existe
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrador',
                'description' => 'Administrador del sistema'
            ]
        );

        // Crear rol de instrumentista si no existe
        $instrumentistaRole = Role::firstOrCreate(
            ['slug' => 'instrumentista'],
            [
                'name' => 'Instrumentista',
                'description' => 'Instrumentista quirúrgico'
            ]
        );

        // Crear usuario administrador de prueba
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol de administrador
        if (!$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole);
        }

        // Crear usuario instrumentista de prueba
        $instrumentista = User::firstOrCreate(
            ['email' => 'instrumentista@example.com'],
            [
                'name' => 'Instrumentista',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol de instrumentista
        if (!$instrumentista->hasRole('instrumentista')) {
            $instrumentista->roles()->attach($instrumentistaRole);
        }
    }
}
