<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome-custom');
});

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    // Rutas públicas de autenticación
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
        Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [AuthController::class, 'register']);
    });

    // Rutas protegidas de autenticación
    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])
            ->name('logout')
            ->middleware('verified');
    });
});

// Rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuraciones', function () {
        return view('configuraciones');
    })->middleware('role:admin')->name('configuraciones');
});
