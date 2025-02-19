<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users';

    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            $this->info($user->name . ' - ' . $user->email . ' - ' . $user->role);
        }
    }
}
