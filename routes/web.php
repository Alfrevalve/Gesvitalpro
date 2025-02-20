<?php

use Illuminate\Support\Facades\Route;

// Rutas de prueba para páginas de error
Route::get('/test-errors/{code}', function ($code) {
    abort($code);
})->where('code', '400|401|403|404|500|503');

// Rutas de administración
Route::prefix('admin')->name('admin.')->middleware(['web', 'auth'])->group(function () {
    // Gestión de usuarios
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
});

// Ruta para probar la optimización de respuesta
Route::get('/test-optimization', function () {
    return view('test-optimization');
});
