<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Observers\SurgeryObserver;
use App\Observers\EquipmentObserver;

class SurgeryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios singleton que puedan ser necesarios
        $this->app->singleton('surgery.scheduler', function ($app) {
            return new \App\Services\SurgeryScheduler();
        });

        $this->app->singleton('equipment.maintenance', function ($app) {
            return new \App\Services\EquipmentMaintenanceService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observadores
        Surgery::observe(SurgeryObserver::class);
        Equipment::observe(EquipmentObserver::class);

        // Compartir datos comunes con todas las vistas
        View::composer('*', function ($view) {
            $view->with('surgeryStatuses', config('surgery.status'));
            $view->with('surgeryLines', config('surgery.lines'));
        });

        // Registrar macros personalizados
        Surgery::macro('getStatusBadge', function () {
            $status = config("surgery.status.{$this->status}");
            return sprintf(
                '<span class="badge badge-%s"><i class="fas fa-%s"></i> %s</span>',
                $status['color'],
                $status['icon'],
                $status['name']
            );
        });

        // Registrar directivas de blade personalizadas
        \Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->role === $role;
        });

        \Blade::if('canManageLine', function ($lineId) {
            return auth()->check() && auth()->user()->canManageLine($lineId);
        });

        // Registrar comandos personalizados
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\CheckEquipmentMaintenance::class,
                \App\Console\Commands\NotifyUpcomingSurgeries::class,
            ]);
        }

        // Registrar políticas de validación personalizadas
        \Validator::extend('available_equipment', function ($attribute, $value, $parameters, $validator) {
            return Equipment::whereIn('id', (array) $value)
                ->where('status', 'available')
                ->count() === count((array) $value);
        });

        \Validator::extend('valid_surgery_time', function ($attribute, $value, $parameters, $validator) {
            $time = \Carbon\Carbon::parse($value);
            $workingStart = \Carbon\Carbon::parse(config('surgery.scheduling.working_hours.start'));
            $workingEnd = \Carbon\Carbon::parse(config('surgery.scheduling.working_hours.end'));
            
            return $time->between($workingStart, $workingEnd);
        });
    }
}
