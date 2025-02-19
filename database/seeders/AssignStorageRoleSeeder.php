<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class AssignStorageRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el rol de almacén
        $storageRole = Role::where('slug', 'storage')->first();

        if (!$storageRole) {
            $this->command->error('El rol de almacén no existe!');
            return;
        }

        // Obtener todos los usuarios
        $users = User::all();

        foreach ($users as $user) {
            // Asignar rol de almacén a todos los usuarios
            if (!DB::table('role_user')->where([
                'user_id' => $user->id,
                'role_id' => $storageRole->id
            ])->exists()) {
                DB::table('role_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $storageRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
