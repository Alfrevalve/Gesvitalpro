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
        // Monitoreo del Sistema
        $schedule->command('system:monitor')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground()
                ->onFailure(function () {
                    \Log::error('System monitoring failed');
                });

        // Verificación de Enlaces
        $schedule->command('links:check --notify')
                ->hourly()
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Link check completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Link check failed');
                });

        // Limpieza de Registros
        $schedule->command('records:cleanup --days=30')
                ->daily()
                ->at('01:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Records cleanup completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Records cleanup failed');
                });

        // Limpieza de Métricas
        $schedule->command('metrics:cleanup --days=30')
                ->daily()
                ->at('02:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Metrics cleanup completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Metrics cleanup failed');
                });

        // Reporte de Estado Diario
        $schedule->command('system:status-report --type=daily --store --email=admin@gesvitalpro.com')
                ->dailyAt('23:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Daily status report generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Daily status report generation failed');
                });

        // Reporte de Estado Semanal
        $schedule->command('system:status-report --type=full --period=weekly --store --email=admin@gesvitalpro.com')
                ->weekly()
                ->sundays()
                ->at('23:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Weekly status report generated successfully');
                })
                ->onFailure(function () {
                    \Log::error('Weekly status report generation failed');
                });

        // Mantenimiento de Base de Datos
        $schedule->command('db:maintain')
                ->weekly()
                ->saturdays()
                ->at('02:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Database maintenance completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Database maintenance failed');
                });

        // Optimización del Sistema
        $schedule->command('optimize:clear')
                ->daily()
                ->at('00:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('System optimization completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('System optimization failed');
                });

        // Limpieza de Logs
        $schedule->command('log:clean --days=7')
                ->daily()
                ->at('03:00')
                ->withoutOverlapping()
                ->onSuccess(function () {
                    \Log::info('Log cleanup completed successfully');
                })
                ->onFailure(function () {
                    \Log::error('Log cleanup failed');
                });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     */
    protected function scheduleTimezone(): \DateTimeZone|string|null
    {
        return config('app.timezone', 'UTC');
    }

    /**
     * Define the application's command schedule.
     */
    protected function defineConsoleSchedule(): void
    {
        $this->app->singleton(Schedule::class, function ($app) {
            return tap(new Schedule($this->scheduleTimezone()), function ($schedule) {
                $this->schedule($schedule);
            });
        });
    }
}
