<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Line;
use App\Models\Equipment;
use App\Models\User;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Crear líneas de prueba
        $lines = [
            [
                'name' => 'Línea Ortopédica',
                'description' => 'Línea especializada en cirugía ortopédica'
            ],
            [
                'name' => 'Línea Cardiovascular',
                'description' => 'Línea especializada en cirugía cardiovascular'
            ],
            [
                'name' => 'Línea Neurológica',
                'description' => 'Línea especializada en neurocirugía'
            ]
        ];

        foreach ($lines as $line) {
            Line::firstOrCreate(
                ['name' => $line['name']],
                ['description' => $line['description']]
            );
        }

        // Crear equipamiento de prueba
        $equipment = [
            [
                'name' => 'Monitor Cardíaco',
                'description' => 'Monitor para signos vitales',
                'status' => 'available',
                'serial_number' => 'MC001'
            ],
            [
                'name' => 'Máquina de Anestesia',
                'description' => 'Equipo para administración de anestesia',
                'status' => 'available',
                'serial_number' => 'MA001'
            ],
            [
                'name' => 'Mesa Quirúrgica',
                'description' => 'Mesa especializada para cirugías',
                'status' => 'available',
                'serial_number' => 'MQ001'
            ],
            [
                'name' => 'Lámpara Quirúrgica',
                'description' => 'Sistema de iluminación para cirugías',
                'status' => 'available',
                'serial_number' => 'LQ001'
            ],
            [
                'name' => 'Electrobisturí',
                'description' => 'Equipo para corte y coagulación',
                'status' => 'available',
                'serial_number' => 'EB001'
            ]
        ];

        foreach ($equipment as $item) {
            Equipment::firstOrCreate(
                ['serial_number' => $item['serial_number']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'status' => $item['status']
                ]
            );
        }

        // Asignar líneas a usuarios existentes
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $lines = Line::all();
            foreach ($lines as $line) {
                $admin->line()->associate($line)->save();
            }
        }

        $instrumentista = User::where('email', 'instrumentista@example.com')->first();
        if ($instrumentista) {
            $line = Line::first();
            if ($line) {
                $instrumentista->line()->associate($line)->save();
            }
        }
    }
}
