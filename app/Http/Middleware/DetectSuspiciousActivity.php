<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectSuspiciousActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'suspicious_activity:' . $request->ip();
        $attempts = Cache::get($key, 0);

        if ($attempts > 10) {
            Log::warning('Actividad sospechosa detectada', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'attempts' => $attempts,
                'path' => $request->path(),
                'method' => $request->method(),
                'timestamp' => now()
            ]);

            return response()->json([
                'message' => 'Acceso temporalmente bloqueado por actividad sospechosa'
            ], 429);
        }

        // Incrementar contador de intentos
        Cache::put($key, $attempts + 1, now()->addMinutes(5));

        $response = $next($request);

        // Si la respuesta es un error 4xx o 5xx, mantener el contador
        // Si es exitosa (2xx), reducir el contador
        if ($response->getStatusCode() < 400) {
            Cache::put($key, max(0, $attempts - 1), now()->addMinutes(5));
        }

        return $response;
    }
}
