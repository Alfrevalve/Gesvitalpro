<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignRolesToUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Asignar roles basados en el nombre del usuario
        $users = User::all();
        foreach ($users as $user) {
            $roles = [];

            // Administrador
            if (Str::contains(strtolower($user->name), 'admin')) {
                if ($role = Role::where('slug', 'admin')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Gerente
            if (Str::contains(strtolower($user->name), 'gerente')) {
                if ($role = Role::where('slug', 'gerente')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Jefe de Línea
            if (Str::contains(strtolower($user->name), 'jefe')) {
                if ($role = Role::where('slug', 'jefe_linea')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Instrumentista
            if (Str::contains(strtolower($user->name), 'instrumentista')) {
                if ($role = Role::where('slug', 'instrumentista')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Vendedor
            if (Str::contains(strtolower($user->name), 'vendedor')) {
                if ($role = Role::where('slug', 'vendedor')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Almacén
            if (Str::contains(strtolower($user->name), 'almacen')) {
                if ($role = Role::where('slug', 'storage')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Despacho
            if (Str::contains(strtolower($user->name), 'despacho')) {
                if ($role = Role::where('slug', 'dispatch')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Si no se asignó ningún rol específico, asignar rol de personal
            if (empty($roles)) {
                if ($role = Role::where('slug', 'staff')->first()) {
                    $roles[] = $role->id;
                }
            }

            // Eliminar roles existentes y asignar los nuevos
            DB::table('role_user')->where('user_id', $user->id)->delete();

            foreach ($roles as $roleId) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
