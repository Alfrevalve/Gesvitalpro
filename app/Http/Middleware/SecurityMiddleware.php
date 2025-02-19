<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Services\SecurityMonitor;

class SecurityMiddleware
{
    protected $securityMonitor;
    protected $config;

    public function __construct(SecurityMonitor $securityMonitor)
    {
        $this->securityMonitor = $securityMonitor;
        $this->config = config('security');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar IP
        if (!$this->validateIpAccess($request)) {
            Log::warning('Intento de acceso desde IP no permitida: ' . $request->ip());
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }

        // Verificar patrones sospechosos
        if ($this->detectSuspiciousPatterns($request)) {
            $this->logSuspiciousActivity($request);
            return response()->json(['error' => 'Solicitud bloqueada por motivos de seguridad'], 403);
        }

        // Verificar rate limiting
        if (!$this->checkRateLimiting($request)) {
            return response()->json(['error' => 'Demasiadas solicitudes'], 429);
        }

        // Verificar 2FA si es necesario
        if ($this->requires2FA($request) && !$this->verify2FA($request)) {
            return redirect()->route('2fa.verify');
        }

        // Agregar headers de seguridad
        $response = $next($request);
        return $this->addSecurityHeaders($response);
    }

    /**
     * Validar acceso por IP
     */
    protected function validateIpAccess(Request $request): bool
    {
        if (!$this->config['ip_whitelist_enabled']) {
            return true;
        }

        $clientIp = $request->ip();
        $allowedIps = explode(',', $this->config['allowed_ips']);
        $blockedIps = explode(',', $this->config['blocked_ips']);

        if (in_array($clientIp, $blockedIps)) {
            return false;
        }

        return empty($allowedIps) || in_array($clientIp, $allowedIps);
    }

    /**
     * Detectar patrones sospechosos
     */
    protected function detectSuspiciousPatterns(Request $request): bool
    {
        $patterns = $this->config['suspicious_patterns'];
        $input = json_encode($request->all());

        foreach ($patterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                if (preg_match("/{$pattern}/i", $input)) {
                    $this->securityMonitor->reportSuspiciousActivity($type, $request);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar rate limiting
     */
    protected function checkRateLimiting(Request $request): bool
    {
        if (!$this->config['rate_limiting']['enabled']) {
            return true;
        }

        $key = 'rate_limit:' . $request->ip();
        $maxAttempts = $this->config['rate_limiting']['max_attempts']['api']['tries'];
        $decayMinutes = $this->config['rate_limiting']['max_attempts']['api']['minutes'];

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return false;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        return true;
    }

    /**
     * Verificar si se requiere 2FA
     */
    protected function requires2FA(Request $request): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        return in_array($user->role, $this->config['require_2fa_for_roles']);
    }

    /**
     * Verificar 2FA
     */
    protected function verify2FA(Request $request): bool
    {
        return session()->has('2fa_verified');
    }

    /**
     * Agregar headers de seguridad
     */
    protected function addSecurityHeaders($response)
    {
        $headers = $this->config['security_headers'];

        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        return $response;
    }

    /**
     * Registrar actividad sospechosa
     */
    protected function logSuspiciousActivity(Request $request): void
    {
        if ($this->config['security_monitoring']['log_suspicious_activities']) {
            Log::warning('Actividad sospechosa detectada', [
                'ip' => $request->ip(),
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->except(['password', 'password_confirmation']),
                'user_agent' => $request->userAgent(),
            ]);

            if ($this->config['security_monitoring']['notify_admins_on_suspicious_activity']) {
                $this->securityMonitor->notifyAdmins('suspicious_activity', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
            }

            if ($this->config['security_monitoring']['block_suspicious_ips']) {
                $this->securityMonitor->blockIp($request->ip());
            }
        }
    }
}
