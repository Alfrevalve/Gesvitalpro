<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Institucion;
use Illuminate\Support\Facades\Log;

class ActualizarUbicacionesInstituciones extends Command
{
    protected $signature = 'instituciones:actualizar-ubicaciones 
        {--force : Forzar actualización incluso para instituciones con ubicación}
        {--chunk=50 : Número de instituciones a procesar por lote}';

    protected $description = 'Actualiza las ubicaciones de las instituciones desde la API de MINSA';

    public function handle()
    {
        $this->info('Iniciando actualización de ubicaciones...');

        $query = Institucion::query();

        // Si no se fuerza la actualización, solo procesar instituciones sin ubicación
        if (!$this->option('force')) {
            $query->sinUbicacion();
        }

        $total = $query->count();
        $chunk = $this->option('chunk');
        $actualizadas = 0;
        $errores = 0;

        $this->output->progressStart($total);

        $query->chunk($chunk, function ($instituciones) use (&$actualizadas, &$errores) {
            foreach ($instituciones as $institucion) {
                try {
                    if ($institucion->actualizarUbicacion()) {
                        $actualizadas++;
                        
                        Log::info('Ubicación de institución actualizada', [
                            'id' => $institucion->id,
                            'nombre' => $institucion->nombre,
                            'codigo_renipress' => $institucion->codigo_renipress
                        ]);
                    } else {
                        $errores++;
                        
                        Log::warning('No se pudo actualizar ubicación de institución', [
                            'id' => $institucion->id,
                            'nombre' => $institucion->nombre,
                            'codigo_renipress' => $institucion->codigo_renipress
                        ]);
                    }
                } catch (\Exception $e) {
                    $errores++;
                    
                    Log::error('Error al actualizar ubicación de institución', [
                        'id' => $institucion->id,
                        'nombre' => $institucion->nombre,
                        'error' => $e->getMessage()
                    ]);
                }

                $this->output->progressAdvance();
                
                // Pequeña pausa para no sobrecargar la API
                usleep(200000); // 200ms
            }
        });

        $this->output->progressFinish();

        $this->info("\nActualización completada:");
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Instituciones procesadas', $total],
                ['Actualizadas exitosamente', $actualizadas],
                ['Errores', $errores],
            ]
        );

        // Registrar estadísticas en el log
        Log::info('Actualización de ubicaciones completada', [
            'total' => $total,
            'actualizadas' => $actualizadas,
            'errores' => $errores,
            'forzado' => $this->option('force')
        ]);

        return Command::SUCCESS;
    }
}
