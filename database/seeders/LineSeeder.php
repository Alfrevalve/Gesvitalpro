<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Line;

class LineSeeder extends Seeder
{
    public function run(): void
    {
        $lines = [
            [
                'name' => 'Línea Ortopédica',
                'description' => 'Equipos y materiales para cirugía ortopédica',
            ],
            [
                'name' => 'Línea Neurológica',
                'description' => 'Equipos y materiales para neurocirugía',
            ],
            [
                'name' => 'Línea Cardiovascular',
                'description' => 'Equipos y materiales para cirugía cardiovascular',
            ],
            [
                'name' => 'Línea General',
                'description' => 'Equipos y materiales para cirugía general',
            ],
            [
                'name' => 'Línea Traumatología',
                'description' => 'Equipos y materiales para traumatología',
            ]
        ];

        foreach ($lines as $line) {
            Line::create($line);
        }
    }
}
