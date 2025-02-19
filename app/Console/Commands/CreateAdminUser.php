<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create admin user if not exists';

    public function handle()
    {
        $admin = User::where('email', 'admin@gesbio.com')->first();

        if (!$admin) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@gesbio.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
            $this->info('Admin user created successfully.');
        } else {
            // Actualizar la contraseña del administrador
            $admin->update([
                'password' => Hash::make('admin123')
            ]);
            $this->info('Admin user password updated successfully.');
        }
    }
}
