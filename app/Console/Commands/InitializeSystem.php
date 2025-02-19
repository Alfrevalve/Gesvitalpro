<?php

namespace App\Console\Commands;

use App\Models\Line;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitializeSystem extends Command
{
    protected $signature = 'system:init';
    protected $description = 'Inicializa el sistema con datos básicos necesarios';

    public function handle()
    {
        $this->info('Iniciando la configuración del sistema...');

        try {
            DB::beginTransaction();

            // Crear líneas quirúrgicas
            $this->info('Creando líneas quirúrgicas...');
            $lines = [
                ['name' => 'NX', 'description' => 'Línea de Neurocirugía'],
                ['name' => 'CR', 'description' => 'Línea de Cirugía Reconstructiva'],
                ['name' => 'SP', 'description' => 'Línea de Cirugía de Columna'],
                ['name' => 'CX', 'description' => 'Línea de Cirugía General'],
            ];

            foreach ($lines as $lineData) {
                Line::create($lineData);
                $this->info("- Línea {$lineData['name']} creada");
            }

            // Crear usuario administrador si no existe
            $this->info('Creando usuario administrador...');
            if (!User::where('email', 'admin@gesbio.com')->exists()) {
                User::create([
                    'name' => 'Administrador',
                    'email' => 'admin@gesbio.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                ]);
                $this->info("- Usuario administrador creado");
            } else {
                $this->info("- Usuario administrador ya existe");
            }

            // Crear jefes de línea y personal
            $this->info('Creando jefes de línea y personal...');
            $lines = Line::all();
            foreach ($lines as $line) {
                // Crear jefe de línea si no existe
                if (!User::where('email', strtolower("jefe.{$line->name}@gesbio.com"))->exists()) {
                    User::create([
                        'name' => "Jefe de {$line->name}",
                        'email' => strtolower("jefe.{$line->name}@gesbio.com"),
                        'password' => Hash::make('password'),
                        'role' => 'line_manager',
                        'line_id' => $line->id,
                    ]);
                    $this->info("- Jefe de línea {$line->name} creado");
                }

                // Crear instrumentistas
                for ($i = 1; $i <= 3; $i++) {
                    if (!User::where('email', strtolower("instrumentista{$i}.{$line->name}@gesbio.com"))->exists()) {
                        User::create([
                            'name' => "Instrumentista {$i} - {$line->name}",
                            'email' => strtolower("instrumentista{$i}.{$line->name}@gesbio.com"),
                            'password' => Hash::make('password'),
                            'role' => 'instrumentist',
                            'line_id' => $line->id,
                        ]);
                        $this->info("- Instrumentista {$i} de {$line->name} creado");
                    }
                }
            }

            // Crear equipos
            $this->info('Creando equipos...');
            
            // Equipos para NX
            $nxLine = Line::where('name', 'NX')->first();
            for ($i = 1; $i <= 5; $i++) {
                Equipment::create([
                    'line_id' => $nxLine->id,
                    'name' => "Calibrador Manual {$i}",
                    'type' => 'Calibrador Manual',
                    'serial_number' => "NX-CM-{$i}",
                    'status' => 'available',
                ]);
                $this->info("- Calibrador Manual {$i} creado");
            }
            Equipment::create([
                'line_id' => $nxLine->id,
                'name' => 'Calibrador Electrónico',
                'type' => 'Calibrador Electrónico',
                'serial_number' => 'NX-CE-1',
                'status' => 'available',
            ]);
            $this->info("- Calibrador Electrónico creado");

            // Equipos para CR
            $crLine = Line::where('name', 'CR')->first();
            for ($i = 1; $i <= 5; $i++) {
                Equipment::create([
                    'line_id' => $crLine->id,
                    'name' => "Craneotomo {$i}",
                    'type' => 'Craneotomo',
                    'serial_number' => "CR-CT-{$i}",
                    'status' => 'available',
                ]);
                $this->info("- Craneotomo {$i} creado");
            }

            // Equipo para SP
            $spLine = Line::where('name', 'SP')->first();
            Equipment::create([
                'line_id' => $spLine->id,
                'name' => 'Drill de Alta Velocidad',
                'type' => 'Drill',
                'serial_number' => 'SP-DRL-1',
                'status' => 'available',
            ]);
            $this->info("- Drill de Alta Velocidad creado");

            DB::commit();
            $this->info('Sistema inicializado exitosamente!');
            
            $this->info("\nCredenciales de acceso:");
            $this->info("Administrador: admin@gesbio.com / password");
            $this->info("Jefes de línea: jefe.[NX|CR|SP|CX]@gesbio.com / password");
            $this->info("Instrumentistas: instrumentista[1-3].[NX|CR|SP|CX]@gesbio.com / password");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error durante la inicialización del sistema:');
            $this->error($e->getMessage());
        }
    }
}
