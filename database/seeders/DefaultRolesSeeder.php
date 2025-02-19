<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class DefaultRolesSeeder extends Seeder
{
    public function run()
    {
        foreach (Role::defaultRoles() as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                [
                    'description' => $roleData['description'],
                    'guard_name' => $roleData['guard_name']
                ]
            );
        }
    }
}
