<?php

use App\Http\Controllers\ConfiguracionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Rutas de configuraciones
    Route::get('/configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
    Route::post('/configuraciones/guardar', [ConfiguracionController::class, 'guardar'])->name('configuraciones.guardar');
    Route::post('/configuraciones/test-email', [ConfiguracionController::class, 'testEmail'])->name('configuraciones.test-email');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index'); // List all roles
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store'); // Create a new role
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update'); // Update role details
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy'); // Delete a role
}); // Close the middleware group
