<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@gesbio.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ]);
        }
    }
}
