<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    // Ruta principal del dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Rutas para datos de gráficos
    Route::prefix('dashboard')->group(function () {
        // Datos para gráficos
        Route::get('/chart-data', [DashboardController::class, 'getChartData'])
            ->name('dashboard.chart-data');

        // Exportación de datos
        Route::get('/exportar', [DashboardController::class, 'exportar'])
            ->name('dashboard.exportar');

        // Estadísticas específicas
        Route::get('/estadisticas/cirugias', [DashboardController::class, 'getEstadisticasCirugias'])
            ->name('dashboard.estadisticas.cirugias');

        Route::get('/estadisticas/inventario', [DashboardController::class, 'getAnalisisInventario'])
            ->name('dashboard.estadisticas.inventario');

        Route::get('/estadisticas/pacientes', [DashboardController::class, 'getEstadisticasPacientes'])
            ->name('dashboard.estadisticas.pacientes');

        // Predicciones
        Route::get('/predicciones', [DashboardController::class, 'getPredicionesDemanda'])
            ->name('dashboard.predicciones');

        // Reportes personalizados
        Route::post('/reportes/generar', [DashboardController::class, 'generarReportePersonalizado'])
            ->name('dashboard.reportes.generar');

        // API endpoints para actualizaciones en tiempo real
        Route::prefix('api')->group(function () {
            Route::get('/stats/realtime', [DashboardController::class, 'getRealTimeStats'])
                ->name('dashboard.api.stats.realtime');

            Route::get('/inventory/alerts', [DashboardController::class, 'getInventoryAlerts'])
                ->name('dashboard.api.inventory.alerts');

            Route::get('/surgery/schedule', [DashboardController::class, 'getSurgerySchedule'])
                ->name('dashboard.api.surgery.schedule');
        });
    });

    // Rutas para configuración del dashboard
    Route::prefix('dashboard/config')->group(function () {
        Route::get('/', [DashboardController::class, 'showConfig'])
            ->name('dashboard.config');

        Route::post('/update', [DashboardController::class, 'updateConfig'])
            ->name('dashboard.config.update');

        Route::post('/widgets/order', [DashboardController::class, 'updateWidgetOrder'])
            ->name('dashboard.config.widgets.order');

        Route::post('/widgets/visibility', [DashboardController::class, 'updateWidgetVisibility'])
            ->name('dashboard.config.widgets.visibility');
    });
});

// Rutas para notificaciones del dashboard
Route::middleware(['auth'])->prefix('dashboard/notifications')->group(function () {
    Route::get('/', [DashboardController::class, 'getNotifications'])
        ->name('dashboard.notifications');

    Route::post('/mark-read', [DashboardController::class, 'markNotificationsAsRead'])
        ->name('dashboard.notifications.mark-read');

    Route::post('/clear', [DashboardController::class, 'clearNotifications'])
        ->name('dashboard.notifications.clear');
});

// Rutas para acciones rápidas
Route::middleware(['auth'])->prefix('dashboard/actions')->group(function () {
    Route::post('/inventory/reorder', [DashboardController::class, 'reorderInventory'])
        ->name('dashboard.actions.inventory.reorder');

    Route::post('/surgery/reschedule', [DashboardController::class, 'rescheduleSurgery'])
        ->name('dashboard.actions.surgery.reschedule');

    Route::post('/patient/update', [DashboardController::class, 'updatePatientStatus'])
        ->name('dashboard.actions.patient.update');
});
