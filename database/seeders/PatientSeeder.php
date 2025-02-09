<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;

class PatientSeeder extends Seeder
{
    public function run()
    {
        Paciente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'institucion' => 'Hospital General',
            'email' => 'juan.perez@example.com', // Agregar email
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        Paciente::create([
            'nombre' => 'María',
            'apellido' => 'López',
            'institucion' => 'Clínica Santa María',
            'email' => 'maria.lopez@example.com', // Agregar email
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);

        Paciente::create([
            'nombre' => 'Carlos',
            'apellido' => 'García',
            'institucion' => 'Centro Médico',
            'email' => 'carlos.garcia@example.com', // Agregar email
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
    }
}
