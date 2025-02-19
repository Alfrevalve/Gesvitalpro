<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class AddModuleRolesSeeder extends Seeder
{
    public function run()
    {
        // Crear rol de almacén
        $storageRole = Role::firstOrCreate(
            ['slug' => 'storage'],
            [
                'name' => 'Almacén',
                'description' => 'Personal de almacén'
            ]
        );

        // Crear rol de despacho
        $dispatchRole = Role::firstOrCreate(
            ['slug' => 'dispatch'],
            [
                'name' => 'Despacho',
                'description' => 'Personal de despacho'
            ]
        );

        // Crear permisos para almacén
        $storagePermissions = [
            'view_storage' => 'Ver módulo de almacén',
            'manage_storage' => 'Gestionar almacén',
            'update_storage_status' => 'Actualizar estado de solicitudes',
        ];

        foreach ($storagePermissions as $slug => $description) {
            $permission = Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $description,
                    'description' => $description
                ]
            );
            $storageRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Crear permisos para despacho
        $dispatchPermissions = [
            'view_dispatch' => 'Ver módulo de despacho',
            'manage_dispatch' => 'Gestionar despacho',
            'confirm_deliveries' => 'Confirmar entregas',
        ];

        foreach ($dispatchPermissions as $slug => $description) {
            $permission = Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $description,
                    'description' => $description
                ]
            );
            $dispatchRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Asignar roles al administrador
        $admin = User::where('email', 'admin@admin.com')->first();
        if ($admin) {
            $admin->roles()->syncWithoutDetaching([
                $storageRole->id,
                $dispatchRole->id
            ]);
        }

        // Crear usuarios de prueba para cada rol
        $storageUser = User::firstOrCreate(
            ['email' => 'storage@example.com'],
            [
                'name' => 'Usuario Almacén',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $storageUser->roles()->syncWithoutDetaching([$storageRole->id]);

        $dispatchUser = User::firstOrCreate(
            ['email' => 'dispatch@example.com'],
            [
                'name' => 'Usuario Despacho',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $dispatchUser->roles()->syncWithoutDetaching([$dispatchRole->id]);
    }
}
