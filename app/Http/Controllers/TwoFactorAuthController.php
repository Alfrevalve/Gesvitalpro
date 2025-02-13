<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->middleware(['auth', 'password.confirm']);
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Mostrar vista de configuración de 2FA
     */
    public function show()
    {
        return view('auth.two-factor.configure');
    }

    /**
     * Habilitar 2FA para el usuario
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return redirect()->route('two-factor.show')
                           ->with('error', 'La autenticación de dos factores ya está habilitada.');
        }

        $result = $this->twoFactorService->enable($user);

        session(['two_factor_secret' => $result['secret']]);

        return view('auth.two-factor.enable', [
            'qrCodeUrl' => $this->twoFactorService->getQRCodeUrl($user),
            'recoveryCodes' => $result['recovery_codes']
        ]);
    }

    /**
     * Confirmar configuración de 2FA
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = $request->user();

        if (!$this->twoFactorService->verifyCode($user, $request->code)) {
            throw ValidationException::withMessages([
                'code' => ['El código proporcionado no es válido.']
            ]);
        }

        $this->twoFactorService->confirm($user);

        return redirect()->route('two-factor.show')
                        ->with('status', 'La autenticación de dos factores ha sido habilitada.');
    }

    /**
     * Deshabilitar 2FA para el usuario
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password'
        ]);

        $this->twoFactorService->disable($request->user());

        return redirect()->route('two-factor.show')
                        ->with('status', 'La autenticación de dos factores ha sido deshabilitada.');
    }

    /**
     * Mostrar vista de verificación de 2FA
     */
    public function verify()
    {
        return view('auth.two-factor.verify');
    }

    /**
     * Verificar código 2FA
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = Auth::user();

        // Verificar si la cuenta está bloqueada
        if (!$this->twoFactorService->checkLoginAttempts($user)) {
            throw ValidationException::withMessages([
                'code' => ['La cuenta está bloqueada temporalmente debido a múltiples intentos fallidos.']
            ]);
        }

        // Verificar el código
        if (!$this->twoFactorService->verifyCode($user, $request->code) &&
            !$this->twoFactorService->verifyRecoveryCode($user, $request->code)) {
            
            $this->twoFactorService->incrementLoginAttempts($user);
            
            throw ValidationException::withMessages([
                'code' => ['El código proporcionado no es válido.']
            ]);
        }

        // Resetear intentos fallidos
        $this->twoFactorService->resetLoginAttempts($user);

        $request->session()->put('auth.two_factor_confirmed', true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Regenerar códigos de recuperación
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();
        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();
        
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->save();

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes
        ]);
    }
}
