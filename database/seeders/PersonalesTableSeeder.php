<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personal;

class PersonalesTableSeeder extends Seeder
{
    public function run()
    {
        Personal::create([
            'nombre' => 'Dr. Carlos Martínez',
            'cargo' => 'Cirujano',
            'especialidad' => 'Cirugía general',
            'contacto' => '555-1234',
            'cirugias_asignadas' => 'Cirugía de apendicitis',
        ]);

        // Agrega más registros según sea necesario
    }
}