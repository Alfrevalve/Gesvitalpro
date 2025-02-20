<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class UiOptimizationService
{
    protected $config;
    protected $performanceMonitor;

    public function __construct(PerformanceMonitor $performanceMonitor)
    {
        $this->config = config('ui-optimization');
        $this->performanceMonitor = $performanceMonitor;
    }

    /**
     * Inicializar optimizaciones de UI
     */
    public function initialize(): void
    {
        $this->setupTheme();
        $this->setupDashboardWidgets();
        $this->setupResponsiveConfig();
        $this->setupAccessibility();
        $this->optimizeAssets();
    }

    /**
     * Configurar tema y modo oscuro
     */
    public function setupTheme(): void
    {
        $darkMode = $this->shouldUseDarkMode();

        View::share('darkMode', $darkMode);
        View::share('theme', [
            'colors' => $this->config['theme']['colors'],
            'customCss' => $this->getCustomCss(),
        ]);
    }

    /**
     * Determinar si se debe usar modo oscuro
     */
    protected function shouldUseDarkMode(): bool
    {
        if (!$this->config['theme']['dark_mode']['enabled']) {
            return false;
        }

        if ($this->config['theme']['dark_mode']['auto_switch']) {
            $now = Carbon::now();
            $startTime = Carbon::createFromTimeString($this->config['theme']['dark_mode']['start_time']);
            $endTime = Carbon::createFromTimeString($this->config['theme']['dark_mode']['end_time']);

            return $now->between($startTime, $endTime);
        }

        return session('dark_mode', false);
    }

    /**
     * Configurar widgets del dashboard
     */
    protected function setupDashboardWidgets(): void
    {
        $widgets = collect($this->config['dashboard']['widgets'])
            ->filter(fn($widget) => $widget['enabled'])
            ->sortBy('position');

        View::share('dashboardWidgets', $widgets);
    }

    /**
     * Configurar responsive
     */
    protected function setupResponsiveConfig(): void
    {
        View::share('responsive', [
            'breakpoints' => $this->config['responsive']['breakpoints'],
            'sidebarBreakpoint' => $this->config['responsive']['sidebar_breakpoint'],
        ]);

        if ($this->config['responsive']['optimize_tables']) {
            $this->optimizeTablesForMobile();
        }
    }

    /**
     * Configurar accesibilidad
     */
    protected function setupAccessibility(): void
    {
        View::share('accessibility', $this->config['accessibility']);
    }

    /**
     * Optimizar assets
     */
    protected function optimizeAssets(): void
    {
        if ($this->config['performance']['minify_html']) {
            // Implementar minificación HTML
        }

        if ($this->config['performance']['optimize_images']) {
            // Implementar optimización de imágenes
        }
    }

    /**
     * Obtener CSS personalizado
     */
    protected function getCustomCss(): ?string
    {
        if (!$this->config['theme']['custom_css']['enabled']) {
            return null;
        }

        $path = public_path($this->config['theme']['custom_css']['path']);
        return File::exists($path) ? File::get($path) : null;
    }

    /**
     * Optimizar tablas para móvil
     */
    protected function optimizeTablesForMobile(): void
    {
        View::share('tableConfig', [
            'responsive' => true,
            'scrollX' => true,
            'pageLength' => $this->config['components']['datatables']['page_length'],
            'dom' => $this->config['components']['datatables']['dom'],
        ]);
    }

    /**
     * Generar configuración para DataTables
     */
    public function getDataTablesConfig(): array
    {
        return [
            'responsive' => $this->config['components']['datatables']['responsive'],
            'pageLength' => $this->config['components']['datatables']['page_length'],
            'dom' => $this->config['components']['datatables']['dom'],
            'buttons' => $this->config['components']['datatables']['buttons'],
            'language' => [
                'url' => '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            ],
        ];
    }

    /**
     * Generar configuración para gráficos
     */
    public function getChartsConfig(): array
    {
        return [
            'responsive' => $this->config['components']['charts']['responsive'],
            'maintainAspectRatio' => $this->config['components']['charts']['maintainAspectRatio'],
            'theme' => $this->shouldUseDarkMode() ? 'dark' : 'light',
        ];
    }

    /**
     * Obtener configuración de Select2
     */
    public function getSelect2Config(): array
    {
        return [
            'theme' => $this->config['components']['select2']['theme'],
            'responsive' => $this->config['components']['select2']['responsive'],
            'language' => 'es',
        ];
    }

    /**
     * Generar configuración de animaciones
     */
    public function getAnimationsConfig(): array
    {
        if (!$this->config['animations']['enabled']) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'reduceMotion' => $this->config['animations']['reduce_motion'],
            'types' => $this->config['animations']['types'],
        ];
    }

    /**
     * Obtener métricas de rendimiento UI
     */
    public function getUiPerformanceMetrics(): array
    {
        return [
            'loadTime' => $this->performanceMonitor->getMetric('page_load_time'),
            'firstContentfulPaint' => $this->performanceMonitor->getMetric('first_contentful_paint'),
            'domInteractive' => $this->performanceMonitor->getMetric('dom_interactive'),
        ];
    }

    /**
     * Verificar y optimizar el rendimiento de la UI
     */
    public function checkAndOptimizeUiPerformance(): array
    {
        $metrics = $this->getUiPerformanceMetrics();
        $recommendations = [];

        if ($metrics['loadTime'] > 2.0) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Tiempo de carga de página elevado',
                'action' => 'Considerar optimización de assets',
            ];
        }

        if ($metrics['firstContentfulPaint'] > 1.0) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'First Contentful Paint puede mejorarse',
                'action' => 'Revisar carga inicial de recursos',
            ];
        }

        return $recommendations;
    }
}
