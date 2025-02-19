<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class AssignUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los roles
        $adminRole = Role::where('slug', 'admin')->first();
        $gerenteRole = Role::where('slug', 'gerente')->first();
        $jefeLineaRole = Role::where('slug', 'jefe_linea')->first();
        $instrumentistaRole = Role::where('slug', 'instrumentista')->first();
        $vendedorRole = Role::where('slug', 'vendedor')->first();
        $storageRole = Role::where('slug', 'storage')->first();
        $dispatchRole = Role::where('slug', 'dispatch')->first();

        // Obtener usuarios por línea
        $users = User::with('line')->get();

        foreach ($users as $user) {
            // Determinar el rol basado en el correo y la línea
            $roles = [];

            // Usuarios de almacén
            if (str_contains($user->email, 'almacen')) {
                $roles[] = $storageRole->id;
            }
            // Usuarios de despacho
            elseif (str_contains($user->email, 'despacho')) {
                $roles[] = $dispatchRole->id;
            }
            // Usuarios vendedores
            elseif (str_contains($user->email, 'vendedor')) {
                $roles[] = $vendedorRole->id;
            }
            // Usuarios instrumentistas
            elseif (str_contains($user->email, 'instrumentista')) {
                $roles[] = $instrumentistaRole->id;
            }
            // Jefes de línea (usuarios con línea asignada)
            elseif ($user->line_id) {
                $roles[] = $jefeLineaRole->id;
            }
            // Gerentes
            elseif (str_contains($user->email, 'gerente')) {
                $roles[] = $gerenteRole->id;
            }
            // Administradores (por defecto si no coincide con otros roles)
            else {
                $roles[] = $adminRole->id;
            }

            // Asignar roles
            foreach ($roles as $roleId) {
                if (!DB::table('role_user')->where('user_id', $user->id)->where('role_id', $roleId)->exists()) {
                    DB::table('role_user')->insert([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
