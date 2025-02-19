<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

class OptimizeAssetsMiddleware
{
    /**
     * Assets que solo se cargarán en páginas específicas
     */
    protected $conditionalAssets = [
        'dashboard' => [
            'css' => [
                'assets/vendor/libs/apex-charts/apex-charts.css',
            ],
            'js' => [
                'assets/vendor/libs/apex-charts/apexcharts.js',
                'assets/js/dashboards-analytics.js',
            ],
        ],
        'surgery' => [
            'css' => [
                'assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css',
                'assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css',
            ],
            'js' => [
                'assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
            ],
        ],
    ];

    /**
     * Assets que siempre se cargarán
     */
    protected $coreAssets = [
        'css' => [
            'assets/vendor/css/core.css',
            'assets/vendor/css/theme-default.css',
        ],
        'js' => [
            'assets/vendor/libs/jquery/jquery.js',
            'assets/vendor/libs/popper/popper.js',
            'assets/vendor/js/bootstrap.js',
            'assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
            'assets/vendor/js/menu.js',
        ],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Detectar si estamos en el panel de Filament
        $isFilament = str_starts_with($request->path(), 'admin');

        if ($isFilament) {
            // En Filament, solo cargar los assets necesarios
            $this->loadFilamentAssets();
        } else {
            // En Sneat, cargar los assets según la página
            $this->loadSneatAssets($request);
        }

        return $next($request);
    }

    /**
     * Cargar assets específicos de Filament
     */
    protected function loadFilamentAssets()
    {
        // Evitar conflictos con Bootstrap de Sneat
        View::share('disableSneatBootstrap', true);

        // Compartir variables específicas de Filament
        View::share('filamentAssets', [
            'css' => [
                'resources/css/filament/admin.css',
            ],
            'js' => [
                'resources/js/filament/admin.js',
            ],
        ]);
    }

    /**
     * Cargar assets de Sneat según la página actual
     */
    protected function loadSneatAssets(Request $request)
    {
        $currentRoute = Route::currentRouteName();
        $pageAssets = [
            'css' => $this->coreAssets['css'],
            'js' => $this->coreAssets['js'],
        ];

        // Agregar assets condicionales según la página
        foreach ($this->conditionalAssets as $page => $assets) {
            if (str_contains($currentRoute, $page)) {
                $pageAssets['css'] = array_merge($pageAssets['css'], $assets['css'] ?? []);
                $pageAssets['js'] = array_merge($pageAssets['js'], $assets['js'] ?? []);
            }
        }

        // Compartir assets con las vistas
        View::share('pageAssets', $pageAssets);
    }

    /**
     * Determinar si un asset ya está cargado
     */
    protected function isAssetLoaded($asset, $type)
    {
        $loadedAssets = View::shared('loadedAssets', []);
        return in_array($asset, $loadedAssets[$type] ?? []);
    }

    /**
     * Marcar un asset como cargado
     */
    protected function markAssetAsLoaded($asset, $type)
    {
        $loadedAssets = View::shared('loadedAssets', []);
        $loadedAssets[$type][] = $asset;
        View::share('loadedAssets', $loadedAssets);
    }
}
