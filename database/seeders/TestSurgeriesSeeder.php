<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Medico;
use App\Models\Institucion;
use App\Models\Line;
use Carbon\Carbon;

class TestSurgeriesSeeder extends Seeder
{
    public function run(): void
    {
        $medicos = Medico::all();
        $equipments = Equipment::all();
        $instituciones = Institucion::all();
        $lines = Line::all();

        $estados = ['programada', 'confirmada', 'en_preparacion', 'preparada', 'en_transito', 'entregada', 'en_proceso', 'completada', 'cancelada'];
        $tipos = ['electiva', 'urgencia', 'ambulatoria'];
        $prioridades = ['alta', 'media', 'baja'];

        // Generar cirugías para los próximos 30 días
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        // Para cada línea, crear algunas cirugías
        foreach ($lines as $line) {
            $numCirugias = rand(3, 5);

            for ($i = 0; $i < $numCirugias; $i++) {
                // Seleccionar médico y su institución asociada
                $medico = $medicos->random();
                $institucion = $medico->instituciones->random();

                // Generar fecha aleatoria
                $fecha = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );

                // Determinar estado basado en la fecha
                $estado = $estados[array_rand($estados)];
                if ($fecha->isPast()) {
                    $estado = rand(0, 1) ? 'completada' : 'cancelada';
                }

                // Crear la cirugía
                $surgery = Surgery::create([
                    'medico_id' => $medico->id,
                    'institucion_id' => $institucion->id,
                    'line_id' => $line->id,
                    'fecha_programada' => $fecha,
                    'tipo' => $tipos[array_rand($tipos)],
                    'prioridad' => $prioridades[array_rand($prioridades)],
                    'estado' => $estado,
                    'notas' => 'Cirugía de prueba - ' . $line->name,
                    'duracion_estimada' => rand(60, 240), // Entre 1 y 4 horas
                    'sala_operaciones' => 'Sala ' . rand(1, 5),
                    'codigo_procedimiento' => strtoupper(substr($line->code, 0, 3)) . '-' . rand(100, 999)
                ]);

                // Asignar equipos de la línea correspondiente
                $lineEquipments = $equipments->where('line_id', $line->id);
                $selectedEquipments = $lineEquipments->random(min(2, $lineEquipments->count()));

                foreach ($selectedEquipments as $equipment) {
                    $surgery->equipment()->attach($equipment->id, [
                        'estado' => 'asignado',
                        'notas' => 'Asignación de prueba'
                    ]);
                }
            }
        }

        // Crear algunas cirugías específicas para pruebas
        $fechaHoy = Carbon::now();

        // Cirugía para hoy
        $this->createSpecificSurgery($fechaHoy, 'programada', $medicos, $lines, $instituciones, $equipments);

        // Cirugía para mañana
        $this->createSpecificSurgery($fechaHoy->addDay(), 'confirmada', $medicos, $lines, $instituciones, $equipments);

        // Cirugía en preparación
        $this->createSpecificSurgery($fechaHoy->addDays(2), 'en_preparacion', $medicos, $lines, $instituciones, $equipments);

        // Cirugía completada ayer
        $this->createSpecificSurgery($fechaHoy->subDays(1), 'completada', $medicos, $lines, $instituciones, $equipments);
    }

    private function createSpecificSurgery($fecha, $estado, $medicos, $lines, $instituciones, $equipments)
    {
        $medico = $medicos->random();
        $line = $lines->random();
        $institucion = $medico->instituciones->random();

        $surgery = Surgery::create([
            'medico_id' => $medico->id,
            'institucion_id' => $institucion->id,
            'line_id' => $line->id,
            'fecha_programada' => $fecha,
            'tipo' => 'electiva',
            'prioridad' => 'alta',
            'estado' => $estado,
            'notas' => "Cirugía de prueba - Estado: {$estado}",
            'duracion_estimada' => 120,
            'sala_operaciones' => 'Sala Principal',
            'codigo_procedimiento' => strtoupper(substr($line->code, 0, 3)) . '-TEST'
        ]);

        // Asignar equipos
        $lineEquipments = $equipments->where('line_id', $line->id);
        $selectedEquipments = $lineEquipments->random(min(2, $lineEquipments->count()));

        foreach ($selectedEquipments as $equipment) {
            $surgery->equipment()->attach($equipment->id, [
                'estado' => 'asignado',
                'notas' => "Equipo asignado para cirugía en estado {$estado}"
            ]);
        }
    }
}
