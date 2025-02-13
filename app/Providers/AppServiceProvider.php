<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registro de servicios personalizados
        $this->app->singleton('audit', function ($app) {
            return new \App\Services\AuditService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configuración de la base de datos
        Schema::defaultStringLength(191);

        // Prevenir lazy loading en desarrollo
        Model::preventLazyLoading(!$this->app->isProduction());

        // Variables globales para las vistas
        View::share('appName', config('app.name'));

        // Caché global para configuraciones frecuentes
        if (!$this->app->runningInConsole()) {
            $this->cacheGlobalSettings();
        }
    }

    /**
     * Cachear configuraciones globales
     */
    protected function cacheGlobalSettings(): void
    {
        Cache::remember('navigation_menu', 86400, function () {
            return [
                [
                    'title' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                ],
                [
                    'title' => 'Cirugías',
                    'route' => 'cirugias.index',
                    'icon' => 'fas fa-procedures',
                ],
                [
                    'title' => 'Pacientes',
                    'route' => 'pacientes.index',
                    'icon' => 'fas fa-users',
                ],
                [
                    'title' => 'Reportes',
                    'route' => 'reportes.index',
                    'icon' => 'fas fa-chart-bar',
                ],
                [
                    'title' => 'Configuración',
                    'route' => 'configuraciones',
                    'icon' => 'fas fa-cogs',
                ]
            ];
        });
    }
}
