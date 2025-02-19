<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visita;
use App\Models\Medico;
use App\Models\User;
use Carbon\Carbon;

class VisitaSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->get();
        $medicos = Medico::all();

        // Generar visitas para los últimos 30 días y próximos 30 días
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->addDays(30);

        $estados = ['programada', 'realizada', 'cancelada'];
        $tipos = ['primera_visita', 'seguimiento', 'capacitacion', 'demostracion'];

        // Para cada médico, crear entre 3-5 visitas
        foreach ($medicos as $medico) {
            $numVisitas = rand(3, 5);

            for ($i = 0; $i < $numVisitas; $i++) {
                // Generar fecha aleatoria entre el rango definido
                $fecha = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                )->format('Y-m-d H:i:s');

                // Determinar estado basado en la fecha
                $estado = 'programada';
                if (Carbon::parse($fecha)->isPast()) {
                    $estado = rand(0, 1) ? 'realizada' : 'cancelada';
                }

                // Crear la visita
                Visita::create([
                    'medico_id' => $medico->id,
                    'user_id' => $users->random()->id,
                    'fecha' => $fecha,
                    'tipo' => $tipos[array_rand($tipos)],
                    'estado' => $estado,
                    'notas' => 'Visita de prueba - ' . ucfirst($tipos[array_rand($tipos)]),
                    'duracion_estimada' => rand(30, 120), // Entre 30 y 120 minutos
                    'institucion_id' => $medico->instituciones->random()->id
                ]);
            }
        }

        // Crear algunas visitas específicas para pruebas
        $fechaHoy = Carbon::now();

        // Visita para hoy
        Visita::create([
            'medico_id' => $medicos->random()->id,
            'user_id' => $users->random()->id,
            'fecha' => $fechaHoy->format('Y-m-d H:i:s'),
            'tipo' => 'seguimiento',
            'estado' => 'programada',
            'notas' => 'Visita de prueba - Programada para hoy',
            'duracion_estimada' => 60,
            'institucion_id' => $medicos->random()->instituciones->random()->id
        ]);

        // Visita para mañana
        Visita::create([
            'medico_id' => $medicos->random()->id,
            'user_id' => $users->random()->id,
            'fecha' => $fechaHoy->addDay()->format('Y-m-d H:i:s'),
            'tipo' => 'capacitacion',
            'estado' => 'programada',
            'notas' => 'Visita de prueba - Programada para mañana',
            'duracion_estimada' => 90,
            'institucion_id' => $medicos->random()->instituciones->random()->id
        ]);

        // Visita realizada ayer
        Visita::create([
            'medico_id' => $medicos->random()->id,
            'user_id' => $users->random()->id,
            'fecha' => $fechaHoy->subDays(2)->format('Y-m-d H:i:s'),
            'tipo' => 'primera_visita',
            'estado' => 'realizada',
            'notas' => 'Visita de prueba - Realizada ayer',
            'duracion_estimada' => 45,
            'institucion_id' => $medicos->random()->instituciones->random()->id
        ]);
    }
}
