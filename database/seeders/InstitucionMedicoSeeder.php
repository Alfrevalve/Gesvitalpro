<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institucion;
use App\Models\Medico;

class InstitucionMedicoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear instituciones
        $instituciones = [
            [
                'nombre' => 'Hospital Central',
                'direccion' => 'Av. Principal 123',
                'telefono' => '555-0001',
                'email' => 'contacto@hospitalcentral.com',
                'tipo_establecimiento' => 'Hospital',
                'estado' => 'activo',
                'latitud' => -33.4569,
                'longitud' => -70.6483,
                'ciudad' => 'Santiago'
            ],
            [
                'nombre' => 'Clínica San José',
                'direccion' => 'Calle Médica 456',
                'telefono' => '555-0002',
                'email' => 'info@clinicasanjose.com',
                'tipo_establecimiento' => 'Clinica',
                'estado' => 'activo',
                'latitud' => -33.4500,
                'longitud' => -70.6400,
                'ciudad' => 'Santiago'
            ],
            [
                'nombre' => 'Centro Médico Norte',
                'direccion' => 'Av. Norte 789',
                'telefono' => '555-0003',
                'email' => 'contacto@centromediconorte.com',
                'tipo_establecimiento' => 'Centro Medico',
                'estado' => 'activo',
                'latitud' => -33.4400,
                'longitud' => -70.6350,
                'ciudad' => 'Santiago'
            ],
            [
                'nombre' => 'Hospital del Este',
                'direccion' => 'Calle Oriental 321',
                'telefono' => '555-0004',
                'email' => 'info@hospitaleste.com',
                'tipo_establecimiento' => 'Hospital',
                'estado' => 'activo',
                'latitud' => -33.4600,
                'longitud' => -70.6300,
                'ciudad' => 'Santiago'
            ]
        ];

        foreach ($instituciones as $institucion) {
            Institucion::create($institucion);
        }

        // Crear médicos asociados a instituciones
        $medicos = [
            [
                'nombre' => 'Dr. Juan Pérez',
                'especialidad' => 'Traumatología',
                'telefono' => '555-1001',
                'email' => 'juan.perez@medicos.com',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Dra. María González',
                'especialidad' => 'Neurocirugía',
                'telefono' => '555-1002',
                'email' => 'maria.gonzalez@medicos.com',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Dr. Carlos Rodríguez',
                'especialidad' => 'Cirugía Cardiovascular',
                'telefono' => '555-1003',
                'email' => 'carlos.rodriguez@medicos.com',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Dra. Ana Martínez',
                'especialidad' => 'Cirugía General',
                'telefono' => '555-1004',
                'email' => 'ana.martinez@medicos.com',
                'estado' => 'activo'
            ],
            [
                'nombre' => 'Dr. Luis Sánchez',
                'especialidad' => 'Ortopedia',
                'telefono' => '555-1005',
                'email' => 'luis.sanchez@medicos.com',
                'estado' => 'activo'
            ]
        ];

        $instituciones = Institucion::all();

        foreach ($medicos as $medico) {
            // Crear el médico
            $medicoCreado = Medico::create($medico);

            // Asignar aleatoriamente 1-2 instituciones a cada médico
            $institucionesAsignadas = $instituciones->random(rand(1, 2));
            foreach ($institucionesAsignadas as $institucion) {
                $medicoCreado->instituciones()->attach($institucion->id);
            }
        }
    }
}
