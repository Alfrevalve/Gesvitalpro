<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['throttle:login']);

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email')
        ->middleware(['throttle:password-reset']);

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware(['throttle:password-confirm']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout')
        ->middleware(['throttle:logout']);
});

// Rutas protegidas por autenticación y roles
Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas de administración
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard')
            ->middleware(['cache.response:300']);

        Route::get('performance', [PerformanceController::class, 'index'])
            ->name('admin.performance')
            ->middleware(['throttle:performance']);
    });

    // Rutas de cirugías
    Route::prefix('surgeries')->middleware(['role:doctor,admin'])->group(function () {
        Route::get('/', [SurgeryController::class, 'index'])
            ->name('surgeries.index')
            ->middleware(['cache.response:60']);

        Route::post('/', [SurgeryController::class, 'store'])
            ->name('surgeries.store')
            ->middleware(['throttle:surgery-create']);

        Route::get('/{surgery}', [SurgeryController::class, 'show'])
            ->name('surgeries.show')
            ->middleware(['cache.response:30']);

        Route::patch('/{surgery}/status', [SurgeryController::class, 'updateStatus'])
            ->name('surgeries.update-status')
            ->middleware(['throttle:surgery-update']);

        Route::delete('/{surgery}', [SurgeryController::class, 'destroy'])
            ->name('surgeries.destroy')
            ->middleware(['throttle:surgery-delete']);

        Route::post('/bulk-update', [SurgeryController::class, 'bulkUpdate'])
            ->name('surgeries.bulk-update')
            ->middleware(['throttle:surgery-bulk']);
    });
});
