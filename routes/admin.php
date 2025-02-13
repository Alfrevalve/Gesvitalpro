<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SystemHealthController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Rutas de estado del sistema
    Route::prefix('system-health')->name('system.')->group(function () {
        Route::get('/', [SystemHealthController::class, 'index'])->name('health');
        Route::get('/check', [SystemHealthController::class, 'check'])->name('health.check');
        Route::post('/maintenance', [SystemHealthController::class, 'maintenance'])->name('health.maintenance');
    });

    // Rutas de configuración
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function () {
            return view('admin.settings.index');
        })->name('index');
    });

    // Rutas de logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', function () {
            return view('admin.logs.index');
        })->name('index');
        Route::get('/activity', function () {
            return view('admin.logs.activity');
        })->name('activity');
        Route::get('/system', function () {
            return view('admin.logs.system');
        })->name('system');
    });

    // Rutas de respaldos
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', function () {
            return view('admin.backups.index');
        })->name('index');
        Route::post('/create', function () {
            // Lógica para crear respaldo
        })->name('create');
        Route::post('/restore', function () {
            // Lógica para restaurar respaldo
        })->name('restore');
    });

    // Rutas de monitoreo
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', function () {
            return view('admin.monitoring.index');
        })->name('index');
        Route::get('/performance', function () {
            return view('admin.monitoring.performance');
        })->name('performance');
        Route::get('/errors', function () {
            return view('admin.monitoring.errors');
        })->name('errors');
    });

    // Rutas de mantenimiento
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', function () {
            return view('admin.maintenance.index');
        })->name('index');
        Route::post('/optimize', function () {
            Artisan::call('optimize');
            return back()->with('status', 'Sistema optimizado.');
        })->name('optimize');
        Route::post('/cache/clear', function () {
            Artisan::call('cache:clear');
            return back()->with('status', 'Caché limpiada.');
        })->name('cache.clear');
    });
});
