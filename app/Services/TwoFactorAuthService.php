<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TwoFactorAuthService
{
    protected $google2fa;
    protected $maxAttempts = 5;
    protected $decayMinutes = 30;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generar secreto para 2FA
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Generar códigos de recuperación
     */
    public function generateRecoveryCodes(): array
    {
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10);
        }
        return $recoveryCodes;
    }

    /**
     * Verificar código 2FA
     */
    public function verifyCode(User $user, string $code): bool
    {
        return $this->google2fa->verifyKey($user->two_factor_secret, $code);
    }

    /**
     * Verificar código de recuperación
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = json_decode($user->two_factor_recovery_codes, true);
        
        if (!is_array($recoveryCodes)) {
            return false;
        }

        $position = array_search($code, $recoveryCodes);

        if ($position !== false) {
            unset($recoveryCodes[$position]);
            $user->two_factor_recovery_codes = json_encode(array_values($recoveryCodes));
            $user->save();
            return true;
        }

        return false;
    }

    /**
     * Habilitar 2FA para un usuario
     */
    public function enable(User $user): array
    {
        $secret = $this->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = json_encode($recoveryCodes);
        $user->two_factor_enabled = true;
        $user->save();

        return [
            'secret' => $secret,
            'recovery_codes' => $recoveryCodes
        ];
    }

    /**
     * Deshabilitar 2FA para un usuario
     */
    public function disable(User $user): void
    {
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_enabled = false;
        $user->two_factor_confirmed_at = null;
        $user->save();
    }

    /**
     * Verificar intentos de inicio de sesión
     */
    public function checkLoginAttempts(User $user): bool
    {
        if ($user->locked_at && $user->locked_at > now()) {
            return false;
        }

        if ($user->login_attempts >= $this->maxAttempts) {
            $user->locked_at = now()->addMinutes($this->decayMinutes);
            $user->save();
            return false;
        }

        return true;
    }

    /**
     * Incrementar intentos de inicio de sesión
     */
    public function incrementLoginAttempts(User $user): void
    {
        $user->increment('login_attempts');
        $user->save();
    }

    /**
     * Resetear intentos de inicio de sesión
     */
    public function resetLoginAttempts(User $user): void
    {
        $user->login_attempts = 0;
        $user->locked_at = null;
        $user->save();
    }

    /**
     * Generar QR code URL
     */
    public function getQRCodeUrl(User $user): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );
    }

    /**
     * Confirmar configuración de 2FA
     */
    public function confirm(User $user): void
    {
        $user->two_factor_confirmed_at = now();
        $user->save();
    }
}
