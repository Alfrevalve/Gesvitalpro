<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Line;
use App\Models\User;
use App\Models\Institucion;
use App\Models\Medico;
use App\Models\Surgery;
use App\Models\Equipment;
use Carbon\Carbon;

class DashboardTestSeeder extends Seeder
{
    public function run()
    {
        // Obtener las líneas existentes
        $lineCx = Line::where('name', 'Línea Cx')->first();
        $lineNx = Line::where('name', 'Línea Nx')->first();
        $lineCr = Line::where('name', 'Línea Cr')->first();

        if (!$lineCx || !$lineNx || !$lineCr) {
            throw new \Exception('Las líneas necesarias no existen. Asegúrate de ejecutar LineSeeder primero.');
        }

        $lines = [$lineCx, $lineNx, $lineCr];
        $instituciones = Institucion::all();
        $medicos = Medico::all();

        // Crear cirugías de prueba para cada línea
        foreach ($lines as $line) {
            // Crear 10 cirugías por línea
            for ($i = 1; $i <= 10; $i++) {
                $status = ['pending', 'in_progress', 'completed', 'cancelled', 'rescheduled'][rand(0, 4)];
                $date = Carbon::now()->subDays(rand(0, 60));

                $surgery = Surgery::create([
                    'line_id' => $line->id,
                    'institucion_id' => $instituciones->random()->id,
                    'medico_id' => $medicos->random()->id,
                    'patient_name' => "Paciente Test {$i}",
                    'surgery_type' => $this->getSurgeryType($line->name),
                    'surgery_date' => $date,
                    'admission_date' => $date->copy()->subHours(2),
                    'fecha' => $date,
                    'status' => $status,
                    'description' => "Cirugía de prueba {$i} para {$line->name}"
                ]);

                // Asignar personal a la cirugía
                $instrumentist = User::where('role_id', function($query) {
                    $query->select('id')
                        ->from('roles')
                        ->where('slug', 'instrumentista')
                        ->first();
                })->where('line_id', $line->id)->first();

                if ($instrumentist) {
                    $surgery->staff()->attach($instrumentist->id, [
                        'role' => 'instrumentista',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Asignar equipos a la cirugía
                $equipment = Equipment::where('line_id', $line->id)
                    ->where('status', 'available')
                    ->inRandomOrder()
                    ->first();

                if ($equipment) {
                    $surgery->equipment()->attach($equipment->id, [
                        'notes' => 'Equipo asignado para pruebas',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    private function getSurgeryType($lineName)
    {
        $types = [
            'Línea Cx' => [
                'Colecistectomía',
                'Apendicectomía',
                'Hernioplastía',
                'Bypass Gástrico',
                'Tiroidectomía'
            ],
            'Línea Nx' => [
                'Craneotomía',
                'Microdiscectomía',
                'Laminectomía',
                'Derivación Ventrículo-Peritoneal',
                'Resección de Tumor Cerebral'
            ],
            'Línea Cr' => [
                'Reconstrucción Facial',
                'Cirugía de Mano',
                'Injerto de Piel',
                'Reconstrucción Post-mastectomía',
                'Cirugía de Quemados'
            ],
        ];

        return $types[$lineName][rand(0, 4)];
    }
}
