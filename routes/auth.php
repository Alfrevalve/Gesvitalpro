<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación básica
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// Rutas de autenticación de dos factores
Route::prefix('two-factor')->name('two-factor.')->middleware(['auth'])->group(function () {
    // Configuración de 2FA
    Route::get('configure', [TwoFactorAuthController::class, 'show'])
        ->name('show');
    
    Route::post('enable', [TwoFactorAuthController::class, 'enable'])
        ->name('enable');
    
    Route::post('confirm', [TwoFactorAuthController::class, 'confirm'])
        ->name('confirm');
    
    Route::post('disable', [TwoFactorAuthController::class, 'disable'])
        ->middleware('password.confirm')
        ->name('disable');
    
    Route::post('regenerate-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])
        ->middleware('password.confirm')
        ->name('regenerate-codes');

    // Verificación de 2FA durante el login
    Route::get('verify', [TwoFactorAuthController::class, 'verify'])
        ->name('verify');
    
    Route::post('validate', [TwoFactorAuthController::class, 'validateCode'])
        ->name('validate');
});

// Ruta de cierre de sesión
Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Confirmación de contraseña
Route::get('confirm-password', function () {
    return view('auth.confirm-password');
})->middleware('auth')->name('password.confirm');

Route::post('confirm-password', function (Request $request) {
    if (! Hash::check($request->password, $request->user()->password)) {
        return back()->withErrors([
            'password' => ['La contraseña proporcionada no coincide con nuestros registros.']
        ]);
    }

    $request->session()->passwordConfirmed();

    return redirect()->intended();
})->middleware(['auth', 'throttle:6,1']);

// Rutas protegidas que requieren 2FA
Route::middleware(['auth', 'two-factor'])->group(function () {
    // Aquí irían las rutas que requieren autenticación de dos factores
});
