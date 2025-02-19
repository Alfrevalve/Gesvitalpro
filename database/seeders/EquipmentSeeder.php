<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Line;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $lines = Line::all();

        $equipmentByLine = [
            'ORTO' => [
                [
                    'name' => 'Set Instrumental Ortopédico Básico',
                    'description' => 'Set completo de instrumental para cirugías ortopédicas básicas',
                    'serial_number' => 'ORTO-001',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(6),
                ],
                [
                    'name' => 'Motor Quirúrgico Ortopédico',
                    'description' => 'Motor para procedimientos ortopédicos',
                    'serial_number' => 'ORTO-002',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(3),
                ]
            ],
            'NEURO' => [
                [
                    'name' => 'Microscopio Neuroquirúrgico',
                    'description' => 'Microscopio especializado para neurocirugía',
                    'serial_number' => 'NEURO-001',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(4),
                ],
                [
                    'name' => 'Set Instrumental Neurocirugía',
                    'description' => 'Set completo para procedimientos neuroquirúrgicos',
                    'serial_number' => 'NEURO-002',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(6),
                ]
            ],
            'CARDIO' => [
                [
                    'name' => 'Monitor Cardíaco Quirúrgico',
                    'description' => 'Monitor especializado para cirugía cardiovascular',
                    'serial_number' => 'CARDIO-001',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(2),
                ],
                [
                    'name' => 'Set Cirugía Cardiovascular',
                    'description' => 'Set completo para cirugías cardiovasculares',
                    'serial_number' => 'CARDIO-002',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(5),
                ]
            ],
            'GEN' => [
                [
                    'name' => 'Torre de Laparoscopía',
                    'description' => 'Torre completa para cirugía laparoscópica',
                    'serial_number' => 'GEN-001',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(3),
                ],
                [
                    'name' => 'Set Cirugía General',
                    'description' => 'Set básico para cirugía general',
                    'serial_number' => 'GEN-002',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(6),
                ]
            ],
            'TRAUMA' => [
                [
                    'name' => 'Set Trauma Básico',
                    'description' => 'Set para atención de trauma básico',
                    'serial_number' => 'TRAUMA-001',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(4),
                ],
                [
                    'name' => 'Fijadores Externos',
                    'description' => 'Set de fijadores externos para trauma',
                    'serial_number' => 'TRAUMA-002',
                    'status' => 'available',
                    'maintenance_date' => now()->addMonths(6),
                ]
            ]
        ];

        foreach ($lines as $line) {
            if (isset($equipmentByLine[$line->code])) {
                foreach ($equipmentByLine[$line->code] as $equipmentData) {
                    $equipment = new Equipment($equipmentData);
                    $equipment->line_id = $line->id;
                    $equipment->save();
                }
            }
        }
    }
}
