<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para diferentes módulos
        $permissions = [
            // Permisos de usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            
            // Permisos de pacientes
            'ver pacientes',
            'crear pacientes',
            'editar pacientes',
            'eliminar pacientes',
            
            // Permisos de cirugías
            'ver cirugias',
            'crear cirugias',
            'editar cirugias',
            'eliminar cirugias',
            
            // Permisos de inventario
            'ver inventario',
            'crear inventario',
            'editar inventario',
            'eliminar inventario',
            
            // Permisos de reportes
            'ver reportes',
            'crear reportes',
            'exportar reportes',
            
            // Permisos de configuración
            'ver configuracion',
            'editar configuracion'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Rol de Administrador
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Rol de Usuario Regular
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'ver pacientes',
            'ver cirugias',
            'ver inventario',
            'ver reportes'
        ]);

        // Rol de Doctor
        $doctorRole = Role::create(['name' => 'doctor']);
        $doctorRole->givePermissionTo([
            'ver pacientes',
            'crear pacientes',
            'editar pacientes',
            'ver cirugias',
            'crear cirugias',
            'editar cirugias',
            'ver reportes',
            'crear reportes'
        ]);

        // Rol de Instrumentista
        $instrumentistaRole = Role::create(['name' => 'instrumentista']);
        $instrumentistaRole->givePermissionTo([
            'ver cirugias',
            'editar cirugias',
            'ver inventario',
            'editar inventario',
            'ver reportes'
        ]);
    }
}
