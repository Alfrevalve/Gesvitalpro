<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Verificar equipos que necesitan mantenimiento (diariamente a las 7:00 AM)
        $schedule->command('equipment:check-maintenance')
                ->dailyAt('07:00')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/equipment-maintenance.log'));

        // Notificar sobre cirugías próximas (dos veces al día, 7:00 AM y 7:00 PM)
        $schedule->command('surgeries:notify-upcoming')
                ->twiceDaily(7, 19)
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/surgery-notifications.log'));

        // Ejecutar mantenimiento del sistema (diariamente a las 2:00 AM)
        $schedule->command('system:maintenance')
                ->dailyAt('02:00')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/system-maintenance.log'))
                ->onOneServer();

        // Optimizar base de datos (semanalmente los domingos a las 3:00 AM)
        $schedule->command('system:maintenance --only=optimize')
                ->weekly()
                ->sundays()
                ->at('03:00')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/database-optimization.log'))
                ->onOneServer();

        // Limpiar caché (cada 6 horas)
        $schedule->command('system:maintenance --only=cache')
                ->everySixHours()
                ->appendOutputTo(storage_path('logs/cache-maintenance.log'));

        // Optimización del sistema (diariamente a las 1:00 AM)
        $schedule->command('system:optimize --refresh-cache')
                ->dailyAt('01:00')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/system-optimization.log'))
                ->onOneServer();

        // Optimización completa del sistema (semanalmente los domingos a las 2:00 AM)
        $schedule->command('system:optimize --clear-all --optimize-db')
                ->weekly()
                ->sundays()
                ->at('02:00')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/full-system-optimization.log'))
                ->onOneServer();

        // Actualizar ubicaciones de instituciones (cada día a las 4:00 AM)
        $schedule->command('instituciones:actualizar-ubicaciones')
                ->dailyAt('04:00')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/ubicaciones-update.log'))
                ->onOneServer();

        // Actualización forzada de ubicaciones (semanalmente los domingos a las 4:30 AM)
        $schedule->command('instituciones:actualizar-ubicaciones --force')
                ->weekly()
                ->sundays()
                ->at('04:30')
                ->timezone('America/Lima')
                ->appendOutputTo(storage_path('logs/ubicaciones-force-update.log'))
                ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        $this->commands([
            Commands\ConvertLayoutsToComponents::class,
        ]);

        require base_path('routes/console.php');
    }

    /**
     * The application's command schedule.
     *
     * @var array
     */
    protected $commands = [
        Commands\InitializeSystem::class,
        Commands\CheckEquipmentMaintenance::class,
        Commands\NotifyUpcomingSurgeries::class,
        Commands\SystemMaintenance::class,
        Commands\ActualizarUbicacionesInstituciones::class,
        Commands\OptimizeSystemCommand::class,
    ];
}
