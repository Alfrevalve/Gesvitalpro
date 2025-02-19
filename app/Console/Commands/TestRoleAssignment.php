<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestRoleAssignment extends Command
{
    protected $signature = 'test:role-assignment';
    protected $description = 'Test role assignment functionality';

    public function handle()
    {
        try {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password')
            ]);

            $user->assignRole('tecnico');

            $this->info('User created and role assigned successfully');
            $this->info('User roles: ' . $user->roles->pluck('name')->join(', '));
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
