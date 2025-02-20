<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Services\UiOptimizationService;
use App\Services\PerformanceMonitor;

class UiOptimizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UiOptimizationService::class, function ($app) {
            return new UiOptimizationService(
                $app->make(PerformanceMonitor::class)
            );
        });

        // Registrar configuración
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ui-optimization.php', 'ui-optimization'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Inicializar servicio de optimización UI
        $uiOptimizer = $this->app->make(UiOptimizationService::class);
        $uiOptimizer->initialize();

        // Compartir configuraciones globales con todas las vistas
        View::composer('*', function ($view) use ($uiOptimizer) {
            $view->with('uiConfig', [
                'darkMode' => session('dark_mode', false),
                'animations' => $uiOptimizer->getAnimationsConfig(),
                'accessibility' => config('ui-optimization.accessibility'),
            ]);
        });

        // Registrar directivas Blade personalizadas
        $this->registerBladeDirectives();

        // Registrar componentes de UI optimizados
        $this->registerUiComponents();

        // Configurar middleware para optimización de respuesta
        $this->configureResponseOptimization();
    }

    /**
     * Registrar directivas Blade personalizadas
     */
    protected function registerBladeDirectives(): void
    {
        // Directiva para modo oscuro
        Blade::if('darkMode', function () {
            return session('dark_mode', false);
        });

        // Directiva para características de accesibilidad
        Blade::if('accessibilityFeature', function ($feature) {
            return config("ui-optimization.accessibility.{$feature}", false);
        });

        // Directiva para optimización móvil
        Blade::if('mobile', function () {
            return $this->app['agent']->isMobile();
        });

        // Directiva para carga diferida
        Blade::directive('lazyLoad', function ($expression) {
            return "<?php if(config('ui-optimization.loading.lazy_load_images')): ?>
                loading=\"lazy\" <?php endif; ?>";
        });
    }

    /**
     * Registrar componentes de UI optimizados
     */
    protected function registerUiComponents(): void
    {
        // Registrar componentes Blade
        Blade::componentNamespace('App\\View\\Components', 'ui');

        // Cargar vistas de componentes
        $this->loadViewsFrom(__DIR__.'/../../resources/views/components', 'ui');

        // Publicar assets
        $this->publishes([
            __DIR__.'/../../resources/views/components' => resource_path('views/vendor/ui'),
        ], 'ui-components');

        $this->publishes([
            __DIR__.'/../../public/vendor/ui' => public_path('vendor/ui'),
        ], 'ui-assets');
    }

    /**
     * Configurar optimización de respuesta
     */
    protected function configureResponseOptimization(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\OptimizeResponse::class);

        if (config('ui-optimization.performance.minify_html')) {
            $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\MinifyHtml::class);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            UiOptimizationService::class,
        ];
    }
}
