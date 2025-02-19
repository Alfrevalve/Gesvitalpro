<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Line;

class CheckSeededData extends Command
{
    protected $signature = 'check:seeded-data';
    protected $description = 'Check the seeded data in the database';

    public function handle()
    {
        $this->info('Checking seeded data...');

        // Check Roles
        $this->info("\nRoles:");
        foreach (Role::all() as $role) {
            $this->line("- {$role->name}");
        }

        // Check Lines
        $this->info("\nLines:");
        foreach (Line::all() as $line) {
            $this->line("- {$line->name} (Code: {$line->code})");
        }

        // Check Admin User
        $this->info("\nAdmin User:");
        $admin = User::with('roles')->where('email', 'admin@gesbio.com')->first();
        if ($admin) {
            $this->line("Name: {$admin->name}");
            $this->line("Email: {$admin->email}");
            $this->line("Roles: " . $admin->roles->pluck('name')->join(', '));
        } else {
            $this->error("Admin user not found!");
        }
    }
}
