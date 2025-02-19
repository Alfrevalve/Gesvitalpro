<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CacheService;
use Illuminate\Support\Facades\Event;
use App\Models\Surgery;
use App\Models\Equipment;
use App\Models\Medico;
use App\Models\Institucion;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Limpiar caché cuando se modifica una cirugía
        Event::listen(['eloquent.saved: ' . Surgery::class,
            'eloquent.deleted: ' . Surgery::class], function ($event) {
            $cacheService = app(CacheService::class);
            $cacheService->clearSurgeryCache();
        });

        // Limpiar caché cuando se modifica un equipo
        Event::listen(['eloquent.saved: ' . Equipment::class,
            'eloquent.deleted: ' . Equipment::class], function ($event) {
            $cacheService = app(CacheService::class);
            $cacheService->clearEquipmentCache();
        });

        // Limpiar caché cuando se modifica un médico
        Event::listen(['eloquent.saved: ' . Medico::class,
            'eloquent.deleted: ' . Medico::class], function ($event) {
            $cacheService = app(CacheService::class);
            $cacheService->clearCache('medicos_specialty_' . $event->especialidad);
        });

        // Limpiar caché cuando se modifica una institución
        Event::listen(['eloquent.saved: ' . Institucion::class,
            'eloquent.deleted: ' . Institucion::class], function ($event) {
            $cacheService = app(CacheService::class);
            $cacheService->clearCache('instituciones_type_' . $event->tipo_establecimiento);
        });

        // Programar limpieza de caché diaria
        if ($this->app->runningInConsole()) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->call(function () {
                $cacheService = app(CacheService::class);
                $cacheService->refreshAllCache();
            })->daily();
        }
    }
}
