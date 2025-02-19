<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Administrator role with full access'
        ]);

        $userRole = Role::firstOrCreate(['slug' => 'user'], [
            'name' => 'User',
            'slug' => 'user',
            'description' => 'Regular user role'
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gesbio.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->roles()->attach($adminRole);

        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@gesbio.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($userRole);
    }
}
