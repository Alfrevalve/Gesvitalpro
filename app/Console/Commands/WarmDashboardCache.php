<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardOptimizer;
use Illuminate\Support\Facades\Log;

class WarmDashboardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:cache-warm
                          {--force : Forzar actualización incluso si el caché existe}
                          {--monitor : Monitorear rendimiento durante la actualización}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precalcula y almacena en caché las estadísticas del dashboard';

    /**
     * Execute the console command.
     */
    public function handle(DashboardOptimizer $optimizer): int
    {
        $startTime = microtime(true);

        try {
            $this->info('Iniciando precálculo de estadísticas del dashboard...');

            if ($this->option('monitor')) {
                $initialMemory = memory_get_usage();
            }

            // Forzar limpieza de caché si se especifica
            if ($this->option('force')) {
                $this->info('Forzando limpieza de caché existente...');
                $optimizer->clearDashboardCache();
            }

            // Precalcular estadísticas
            $this->info('Calculando estadísticas de cirugías...');
            $optimizer->getSurgeriesStats();

            $this->info('Calculando estadísticas de equipos...');
            $optimizer->getEquipmentStats();

            $this->info('Calculando estadísticas de visitas...');
            $optimizer->getVisitStats();

            $this->info('Calculando estadísticas de logística...');
            $optimizer->getLogisticsStats();

            // Calcular métricas de rendimiento
            $executionTime = microtime(true) - $startTime;

            if ($this->option('monitor')) {
                $memoryUsed = (memory_get_usage() - $initialMemory) / 1024 / 1024; // MB

                $this->table(
                    ['Métrica', 'Valor'],
                    [
                        ['Tiempo de ejecución', number_format($executionTime, 2) . ' segundos'],
                        ['Memoria utilizada', number_format($memoryUsed, 2) . ' MB'],
                    ]
                );

                // Registrar métricas
                Log::info('Dashboard cache warming completado', [
                    'execution_time' => $executionTime,
                    'memory_used' => $memoryUsed,
                    'timestamp' => now(),
                ]);
            }

            $this->info('✓ Caché del dashboard actualizado exitosamente.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al actualizar caché del dashboard:');
            $this->error($e->getMessage());

            Log::error('Error en dashboard cache warming', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time' => microtime(true) - $startTime,
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule($schedule)
    {
        // Programar actualización cada 5 minutos durante horario laboral
        $schedule->command('dashboard:cache-warm')
            ->weekdays()
            ->between('7:00', '20:00')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Actualización menos frecuente fuera de horario laboral
        $schedule->command('dashboard:cache-warm')
            ->weekdays()
            ->unlessBetween('7:00', '20:00')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Actualización en fin de semana
        $schedule->command('dashboard:cache-warm')
            ->weekends()
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Forzar actualización completa una vez al día
        $schedule->command('dashboard:cache-warm --force')
            ->dailyAt('00:01')
            ->withoutOverlapping()
            ->runInBackground();
    }
}
