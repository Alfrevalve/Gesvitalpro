<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LineController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\SurgeryController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas son accesibles a través de /api/v1/...
| Todas las rutas están protegidas por autenticación Sanctum y rate limiting
|
*/

// Rutas públicas (sin autenticación)
Route::prefix('v1')->group(function () {
    // Aquí podrían ir rutas públicas si se necesitan en el futuro
});

// Rutas protegidas por autenticación
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->group(function () {

    /*
    |----------------------------------------------------------------------
    | API Routes Documentation
    |----------------------------------------------------------------------
    |
    | Endpoints:
    |
    | 1. Lines
    |    - GET    /v1/lines                   : Listar líneas
    |    - GET    /v1/lines/{line}           : Obtener línea específica
    |    - GET    /v1/lines/{line}/schedule  : Obtener horario de línea
    |
    | 2. Equipment
    |    - GET    /v1/equipment              : Listar equipos
    |    - GET    /v1/equipment/maintenance  : Info. mantenimiento
    |    - GET    /v1/equipment/{equipment}  : Obtener equipo específico
    |
    | 3. Surgeries
    |    - GET    /v1/surgeries             : Listar cirugías
    |    - GET    /v1/surgeries/{surgery}   : Obtener cirugía específica
    |    - POST   /v1/surgeries/{surgery}/status : Actualizar estado
    |
    | 4. User
    |    - GET    /v1/user                  : Obtener usuario autenticado
    |    - GET    /v1/user/permissions      : Obtener permisos del usuario
    |
    */

    // Rutas de líneas
    Route::prefix('lines')->name('api.lines.')->group(function () {
        Route::get('/', [LineController::class, 'index'])->name('index');
        Route::get('/{line}', [LineController::class, 'show'])->name('show');
        Route::get('/{line}/schedule', [LineController::class, 'schedule'])->name('schedule');
    });

    // Rutas de equipos
    Route::prefix('equipment')->name('api.equipment.')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::get('/maintenance', [EquipmentController::class, 'maintenance'])->name('maintenance');
        Route::get('/{equipment}', [EquipmentController::class, 'show'])->name('show');
    });

    // Rutas de cirugías
    Route::prefix('surgeries')->name('api.surgeries.')->group(function () {
        Route::get('/', [SurgeryController::class, 'index'])->name('index');
        Route::get('/{surgery}', [SurgeryController::class, 'show'])->name('show');
        Route::post('/{surgery}/status', [SurgeryController::class, 'updateStatus'])
            ->name('update-status')
            ->middleware('can:update,surgery');
    });

    // Rutas de usuario
    Route::prefix('user')->name('api.user.')->group(function () {
        Route::get('/', [UserController::class, 'current'])->name('current');
        Route::get('/permissions', [UserController::class, 'permissions'])->name('permissions');
    });
});

// Manejo de errores de API
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint no encontrado. Verifique la URL y el método HTTP.',
        'status' => 404
    ], 404);
});
