<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\RateLimiting\Limit;

class EnhancedRateLimiter
{
    /**
     * Límites de tasa por rol
     */
    protected $roleLimits = [
        'admin' => 300,      // 300 solicitudes por minuto
        'manager' => 180,    // 180 solicitudes por minuto
        'staff' => 120,      // 120 solicitudes por minuto
        'user' => 60,        // 60 solicitudes por minuto
    ];

    /**
     * Rutas sensibles que requieren límites más estrictos
     */
    protected $sensitiveRoutes = [
        'login' => 5,        // 5 intentos por minuto
        'password.*' => 3,   // 3 intentos por minuto para rutas de contraseña
        'api.*' => 60,       // 60 solicitudes por minuto para API
        'storage.*' => 30,   // 30 solicitudes por minuto para almacén
        'dispatch.*' => 30,  // 30 solicitudes por minuto para despacho
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();

        // Obtener límite base según el rol del usuario
        $baseLimit = $this->getRoleLimit($user);

        // Verificar si es una ruta sensible
        $routeLimit = $this->getRouteLimit($routeName);

        // Usar el límite más restrictivo
        $finalLimit = min($baseLimit, $routeLimit);

        // Generar una clave única para el limitador
        $key = $this->getLimiterKey($request, $user, $routeName);

        // Aplicar el límite de tasa
        $limiter = RateLimiter::attempt(
            $key,
            $finalLimit,
            function() {
                return true;
            },
            60 // 1 minuto
        );

        if (! $limiter) {
            $this->logRateLimitExceeded($request, $user, $routeName);
            return $this->buildTooManyRequestsResponse($request);
        }

        // Verificar patrones de abuso
        if ($this->detectAbusePattern($request, $user)) {
            $this->logAbuseDetected($request, $user);
            return $this->buildAbuseDetectedResponse();
        }

        $response = $next($request);

        // Agregar headers de rate limit
        return $this->addRateLimitHeaders($response, $key);
    }

    /**
     * Obtener límite según el rol del usuario
     */
    protected function getRoleLimit($user): int
    {
        if (!$user) {
            return 30; // límite para usuarios no autenticados
        }

        return $this->roleLimits[$user->getRoleSlug()] ?? 60;
    }

    /**
     * Obtener límite según la ruta
     */
    protected function getRouteLimit(?string $routeName): int
    {
        if (!$routeName) {
            return 60;
        }

        foreach ($this->sensitiveRoutes as $pattern => $limit) {
            if (str_is($pattern, $routeName)) {
                return $limit;
            }
        }

        return 60;
    }

    /**
     * Generar clave única para el limitador
     */
    protected function getLimiterKey(Request $request, $user, ?string $routeName): string
    {
        return sprintf(
            '%s:%s:%s:%s',
            $routeName ?? 'default',
            $request->ip(),
            $user?->id ?? 'guest',
            $request->fingerprint()
        );
    }

    /**
     * Detectar patrones de abuso
     */
    protected function detectAbusePattern(Request $request, $user): bool
    {
        $key = "abuse:{$request->ip()}";
        $attempts = Cache::increment($key);

        if ($attempts === 1) {
            Cache::expire($key, now()->addHour());
        }

        // Detectar múltiples intentos fallidos
        if ($attempts > 100) { // 100 intentos en una hora
            return true;
        }

        // Detectar comportamiento sospechoso
        $suspiciousPatterns = [
            $this->detectRapidRequests($request),
            $this->detectUnusualUserAgent($request),
            $this->detectAnomalousTraffic($request, $user)
        ];

        return in_array(true, $suspiciousPatterns);
    }

    /**
     * Detectar solicitudes rápidas
     */
    protected function detectRapidRequests(Request $request): bool
    {
        $key = "requests:{$request->ip()}";
        $requests = Cache::get($key, []);
        $requests[] = now()->timestamp;

        // Mantener solo las últimas 10 solicitudes
        $requests = array_slice($requests, -10);
        Cache::put($key, $requests, now()->addMinute());

        // Verificar si hay más de 10 solicitudes en 1 segundo
        if (count($requests) === 10) {
            return (end($requests) - reset($requests)) < 1;
        }

        return false;
    }

    /**
     * Detectar User-Agent sospechoso
     */
    protected function detectUnusualUserAgent(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        $suspiciousPatterns = [
            'curl',
            'python',
            'wget',
            'bot',
            'script',
            'headless'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detectar tráfico anómalo
     */
    protected function detectAnomalousTraffic(Request $request, $user): bool
    {
        if (!$user) {
            return false;
        }

        $key = "traffic:{$user->id}";
        $traffic = Cache::get($key, []);
        $traffic[] = [
            'ip' => $request->ip(),
            'timestamp' => now()->timestamp
        ];

        // Mantener solo el tráfico de la última hora
        $traffic = array_filter($traffic, function($t) {
            return $t['timestamp'] > now()->subHour()->timestamp;
        });

        Cache::put($key, $traffic, now()->addHour());

        // Verificar múltiples IPs diferentes
        $uniqueIps = collect($traffic)->pluck('ip')->unique();
        return $uniqueIps->count() > 5; // Más de 5 IPs diferentes en una hora
    }

    /**
     * Registrar exceso de límite de tasa
     */
    protected function logRateLimitExceeded(Request $request, $user, ?string $routeName): void
    {
        Log::warning('Rate limit exceeded', [
            'ip' => $request->ip(),
            'user_id' => $user?->id,
            'route' => $routeName,
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Registrar detección de abuso
     */
    protected function logAbuseDetected(Request $request, $user): void
    {
        Log::alert('Abuse detected', [
            'ip' => $request->ip(),
            'user_id' => $user?->id,
            'user_agent' => $request->userAgent(),
            'request_data' => $request->except(['password', 'token'])
        ]);
    }

    /**
     * Construir respuesta de demasiadas solicitudes
     */
    protected function buildTooManyRequestsResponse(Request $request): Response
    {
        return response()->json([
            'message' => 'Too Many Requests',
            'error' => 'Has excedido el límite de solicitudes permitidas. Por favor, intenta más tarde.'
        ], 429);
    }

    /**
     * Construir respuesta de detección de abuso
     */
    protected function buildAbuseDetectedResponse(): Response
    {
        return response()->json([
            'message' => 'Access Denied',
            'error' => 'Se ha detectado comportamiento sospechoso. Tu acceso ha sido temporalmente restringido.'
        ], 403);
    }

    /**
     * Agregar headers de rate limit a la respuesta
     */
    protected function addRateLimitHeaders(Response $response, string $key): Response
    {
        $remaining = RateLimiter::remaining($key, 1);

        $response->headers->add([
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => now()->addMinute()->timestamp
        ]);

        return $response;
    }
}
