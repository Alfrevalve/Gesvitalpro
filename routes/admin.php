<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EquipmentController;
use App\Http\Controllers\Admin\SurgeryController;
use App\Http\Controllers\Admin\InstitucionController;

Route::middleware(['web', 'auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

    // Usuarios
    Route::resource('users', UserController::class);
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

    // Equipamiento
    Route::resource('equipment', EquipmentController::class);
    Route::get('equipment/{equipment}/maintenance', [EquipmentController::class, 'maintenance'])->name('equipment.maintenance');

    // Cirugías
    Route::resource('surgeries', SurgeryController::class);
    Route::get('surgeries/pending', [SurgeryController::class, 'pending'])->name('surgeries.pending');
    Route::post('surgeries/{surgery}/status', [SurgeryController::class, 'updateStatus'])->name('surgeries.status');

    // Instituciones
    Route::resource('instituciones', InstitucionController::class);
    Route::get('instituciones/{institucion}/staff', [InstitucionController::class, 'staff'])->name('instituciones.staff');
});
