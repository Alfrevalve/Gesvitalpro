<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportePostCirugiaController;
use App\Http\Controllers\ReporteVisitaController;

Route::middleware(['auth'])->group(function () {
    // Rutas para reportes post cirugía
    Route::prefix('reportes/post-cirugia')->group(function () {
        Route::get('/', [ReportePostCirugiaController::class, 'index'])
            ->name('reportes.post-cirugia.index');
        
        Route::get('/create', [ReportePostCirugiaController::class, 'create'])
            ->name('reportes.post-cirugia.create');
        
        Route::post('/', [ReportePostCirugiaController::class, 'store'])
            ->name('reportes.post-cirugia.store');
        
        Route::get('/{reporte}', [ReportePostCirugiaController::class, 'show'])
            ->name('reportes.post-cirugia.show');
    });

    // Rutas para reportes de visita
    Route::prefix('reportes/visita')->group(function () {
        Route::get('/', [ReporteVisitaController::class, 'index'])
            ->name('reportes.visita.index');
        
        Route::get('/create', [ReporteVisitaController::class, 'create'])
            ->name('reportes.visita.create');
        
        Route::post('/', [ReporteVisitaController::class, 'store'])
            ->name('reportes.visita.store');
        
        Route::get('/{reporte}', [ReporteVisitaController::class, 'show'])
            ->name('reportes.visita.show');
        
        Route::get('/{reporte}/edit', [ReporteVisitaController::class, 'edit'])
            ->name('reportes.visita.edit');
        
        Route::put('/{reporte}', [ReporteVisitaController::class, 'update'])
            ->name('reportes.visita.update');
        
        Route::delete('/{reporte}', [ReporteVisitaController::class, 'destroy'])
            ->name('reportes.visita.destroy');
    });

    // API endpoint para obtener cirugías por fecha
    Route::get('/api/cirugias-por-fecha/{fecha}', [ReportePostCirugiaController::class, 'getCirugiasPorFecha']);
});
