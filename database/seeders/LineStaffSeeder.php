<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Line;
use App\Models\Role;

class LineStaffSeeder extends Seeder
{
    public function run(): void
    {
        $lines = Line::all();

        // Obtener roles
        $lineManagerRole = Role::where('slug', 'jefe_linea')->first();
        $instrumentistRole = Role::where('slug', 'instrumentista')->first();

        if (!$lineManagerRole || !$instrumentistRole) {
            throw new \Exception('Roles necesarios no encontrados. Asegúrate de que las migraciones de roles se han ejecutado.');
        }

        // Crear personal para cada línea
        foreach ($lines as $line) {
            // Crear jefe de línea
            $lineManager = User::create([
                'name' => "Jefe {$line->name}",
                'email' => strtolower(str_replace(' ', '.', "jefe.{$line->name}@gesbio.com")),
                'password' => bcrypt('password123'),
                'role_id' => $lineManagerRole->id,
                'line_id' => $line->id,
            ]);

            // Crear instrumentista
            $instrumentist = User::create([
                'name' => "Instrumentista {$line->name}",
                'email' => strtolower(str_replace(' ', '.', "instrumentista.{$line->name}@gesbio.com")),
                'password' => bcrypt('password123'),
                'role_id' => $instrumentistRole->id,
                'line_id' => $line->id,
            ]);

            // Asignar personal a la línea con sus roles específicos
            $line->staff()->attach([
                $lineManager->id => ['role' => 'jefe_linea'],
                $instrumentist->id => ['role' => 'instrumentista'],
            ]);
        }
    }
}
