<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class LogDeniedAccess
{
    /**
     * Manejar la solicitud entrante.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Procesar la solicitud
        $response = $next($request);

        // Verificar si el acceso fue denegado (403) o no autorizado (401)
        if (in_array($response->getStatusCode(), [401, 403])) {
            $this->logDeniedAccess($request, $response);
        }

        return $response;
    }

    /**
     * Registrar el intento de acceso denegado
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function logDeniedAccess(Request $request, Response $response): void
    {
        try {
            $user = $request->user();
            $route = $request->route();
            $routeName = $route ? $route->getName() : 'undefined';

            // Crear registro en la tabla de actividad
            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => 'access_denied',
                'description' => "Acceso denegado a {$routeName}",
                'model_type' => null,
                'model_id' => null,
                'metadata' => [
                    'route' => $routeName,
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status_code' => $response->getStatusCode(),
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->getRoleSlug()
                    ] : null,
                    'request_data' => $this->sanitizeRequestData($request),
                ]
            ]);

            // Registrar en los logs del sistema
            Log::warning('Acceso denegado', [
                'user_id' => $user?->id,
                'route' => $routeName,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'status_code' => $response->getStatusCode()
            ]);

            // Si es un intento de acceso no autorizado repetido, considerar medidas adicionales
            $this->checkRepeatedAttempts($request);
        } catch (\Exception $e) {
            // Asegurar que cualquier error en el logging no afecte la respuesta
            Log::error('Error al registrar acceso denegado: ' . $e->getMessage());
        }
    }

    /**
     * Sanitizar los datos de la solicitud para el registro
     *
     * @param Request $request
     * @return array
     */
    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->except(['password', 'password_confirmation', 'token', '_token']);

        // Sanitizar datos sensibles adicionales si es necesario
        array_walk_recursive($data, function (&$value, $key) {
            if (in_array(strtolower($key), ['api_key', 'secret', 'key'])) {
                $value = '[REDACTED]';
            }
        });

        return $data;
    }

    /**
     * Verificar intentos repetidos de acceso denegado
     *
     * @param Request $request
     * @return void
     */
    protected function checkRepeatedAttempts(Request $request): void
    {
        $key = 'denied_access:' . $request->ip();
        $attempts = cache()->increment($key);

        // Si es el primer intento, establecer tiempo de expiración
        if ($attempts === 1) {
            cache()->expire($key, now()->addHour());
        }

        // Si hay demasiados intentos, considerar medidas adicionales
        if ($attempts >= 10) {
            Log::alert('Múltiples intentos de acceso denegado detectados', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'user_agent' => $request->userAgent()
            ]);

            // Aquí se podrían implementar medidas adicionales como:
            // - Bloquear la IP temporalmente
            // - Enviar notificación al administrador
            // - Agregar la IP a una lista negra
        }
    }
}
