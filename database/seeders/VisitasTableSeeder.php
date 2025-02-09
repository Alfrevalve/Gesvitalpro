<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visita;

class VisitasTableSeeder extends Seeder
{
    public function run()
    {
        Visita::create([
            'fecha_hora' => '2023-10-01 10:00:00',
            'institucion' => 'Hospital General',
            'persona_contactada' => 'Dr. Juan Pérez',
            'motivo' => 'Consulta médica',
            'seguimiento_requerido' => true,
        ]);

        // Agrega más registros según sea necesario
    }
}