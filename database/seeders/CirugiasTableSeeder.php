<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cirugia;

class CirugiasTableSeeder extends Seeder
{
    public function run()
    {
        Cirugia::create([
            'fecha_hora' => '2023-10-02 14:00:00',
            'hospital' => 'Clínica Santa María',
            'equipo_requerido' => 'Bisturí, Monitor',
            'consumibles' => 'Gas anestésico',
            'personal_asignado' => 'Dr. Ana López',
            'tiempo_estimado' => 120,
        ]);

        // Agrega más registros según sea necesario
    }
}