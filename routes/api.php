<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CirugiaController;
use App\Http\Controllers\PatientController; // Import PatientController

/*
|-------------------------------------------------------------------------- 
| API Routes 
|-------------------------------------------------------------------------- 
| 
| Here is where you can register API routes for your application. These 
| routes are loaded by the RouteServiceProvider and all of them will 
| be assigned to the "api" middleware group. Make something great! 
| 
*/

Route::middleware('jwt.auth')->group(function () {
    Route::get('/cirugias', [CirugiaController::class, 'index']);
    Route::post('/cirugias', [CirugiaController::class, 'store']);
    Route::put('/cirugias/{id}', [CirugiaController::class, 'update']);
    Route::delete('/cirugias/{id}', [CirugiaController::class, 'destroy']);
    
    Route::get('/users', [UserController::class, 'index']); // List all users
    Route::post('/users', [UserController::class, 'store']); // Create a new user
    Route::put('/users/{id}', [UserController::class, 'update']); // Update user details
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Delete a user

    // Patient routes
    Route::get('/pacientes', [PatientController::class, 'index'])->name('pacientes.index');
    Route::post('/pacientes', [PatientController::class, 'store'])->name('pacientes.store');
    Route::put('/pacientes/{id}', [PatientController::class, 'update'])->name('pacientes.update');
    Route::delete('/pacientes/{id}', [PatientController::class, 'destroy'])->name('pacientes.destroy');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
