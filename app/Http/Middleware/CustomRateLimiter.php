<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cache\RateLimiting\Limit;
use Symfony\Component\HttpFoundation\Response;

class CustomRateLimiter
{
    /**
     * Configuración de límites por rol
     */
    protected const LIMITS_BY_ROLE = [
        'admin' => [
            'attempts' => 300,
            'decay_minutes' => 1
        ],
        'manager' => [
            'attempts' => 180,
            'decay_minutes' => 1
        ],
        'staff' => [
            'attempts' => 120,
            'decay_minutes' => 1
        ],
        'default' => [
            'attempts' => 60,
            'decay_minutes' => 1
        ]
    ];

    /**
     * Rutas críticas con límites especiales
     */
    protected const CRITICAL_ROUTES = [
        'login' => ['attempts' => 5, 'decay_minutes' => 15],
        'password.reset' => ['attempts' => 3, 'decay_minutes' => 60],
        'api/equipment/*' => ['attempts' => 30, 'decay_minutes' => 1],
        'api/surgeries/*' => ['attempts' => 30, 'decay_minutes' => 1],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $type
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $type = null)
    {
        // Obtener límites basados en el tipo de ruta y rol del usuario
        $limits = $this->getLimits($request, $type);

        // Generar clave única para el rate limiting
        $key = $this->generateRateLimitKey($request);

        // Aplicar rate limiting
        $limiter = RateLimiter::attempt(
            $key,
            $limits['attempts'],
            function() {
                return true;
            },
            $limits['decay_minutes'] * 60
        );

        if (! $limiter) {
            return $this->buildTooManyRequestsResponse($request, $key);
        }

        $response = $next($request);

        // Añadir headers de rate limiting
        return $this->addRateLimitHeaders($response, $key, $limits);
    }

    /**
     * Obtener límites basados en el tipo de ruta y rol del usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $type
     * @return array
     */
    protected function getLimits(Request $request, $type = null)
    {
        // Verificar si es una ruta crítica
        foreach (self::CRITICAL_ROUTES as $route => $limits) {
            if ($request->is($route)) {
                return $limits;
            }
        }

        // Si se especifica un tipo, usar esos límites
        if ($type && isset(self::LIMITS_BY_ROLE[$type])) {
            return self::LIMITS_BY_ROLE[$type];
        }

        // Usar límites basados en el rol del usuario
        if (Auth::check()) {
            $role = Auth::user()->role ?? 'default';
            return self::LIMITS_BY_ROLE[$role] ?? self::LIMITS_BY_ROLE['default'];
        }

        return self::LIMITS_BY_ROLE['default'];
    }

    /**
     * Generar clave única para el rate limiting
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function generateRateLimitKey(Request $request)
    {
        $identifier = Auth::id() ?? $request->ip();
        return sha1($identifier . '|' . $request->route()?->getName() ?? $request->path());
    }

    /**
     * Construir respuesta de demasiadas solicitudes
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return \Illuminate\Http\Response
     */
    protected function buildTooManyRequestsResponse(Request $request, $key)
    {
        $retryAfter = RateLimiter::availableIn($key);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Por favor, espere antes de realizar más solicitudes.',
                'retry_after' => $retryAfter
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return redirect()->back()->with('error',
            'Demasiadas solicitudes. Por favor, espere ' .
            ceil($retryAfter / 60) . ' minutos antes de intentar nuevamente.'
        );
    }

    /**
     * Añadir headers de rate limiting a la respuesta
     *
     * @param  \Illuminate\Http\Response  $response
     * @param  string  $key
     * @param  array  $limits
     * @return \Illuminate\Http\Response
     */
    protected function addRateLimitHeaders($response, $key, $limits)
    {
        $remaining = RateLimiter::remaining($key, $limits['attempts']);
        $headers = [
            'X-RateLimit-Limit' => $limits['attempts'],
            'X-RateLimit-Remaining' => max(0, $remaining),
        ];

        if ($remaining == 0) {
            $headers['Retry-After'] = RateLimiter::availableIn($key);
        }

        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        return $response;
    }
}
