<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('name', 'administrator')->first();

        User::create([
            'name' => 'Jesus Valera', // Cambiado a 'name'
            'email' => 'jesus.valera@biomedsac.com.pe',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id, // Asegurarse de que este campo coincida con la tabla 'users'
        ]);
    }
}