<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use App\Models\Configuracion;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar servicios adicionales si es necesario
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar longitud por defecto para strings en migraciones
        Schema::defaultStringLength(191);

        // Forzar HTTPS en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Usar Bootstrap para la paginación
        Paginator::useBootstrap();

        // Prevenir lazy loading en producción
        Model::preventLazyLoading(!$this->app->isProduction());

        // Habilitar protección contra asignación masiva por defecto
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());

        // Reglas de validación personalizadas
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            return preg_match('/^[\pL\s]+$/u', $value);
        }, 'El campo :attribute solo debe contener letras y espacios.');

        Validator::extend('phone', function ($attribute, $value) {
            return preg_match('/^([0-9\s\-\+\(\)]*)$/', $value);
        }, 'El campo :attribute debe ser un número de teléfono válido.');

        // Configurar manejo de errores personalizado
        if (!$this->app->isLocal()) {
            $this->app->singleton(
                \Illuminate\Contracts\Debug\ExceptionHandler::class,
                \App\Exceptions\Handler::class
            );
        }

        // Configurar observadores específicos para modelos auditables
        Configuracion::observe(\App\Observers\AuditObserver::class);
    }
}
