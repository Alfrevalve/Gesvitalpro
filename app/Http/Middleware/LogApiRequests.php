<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Illuminate\Support\Str;

class LogApiRequests
{
    /**
     * Rutas sensibles que requieren logging detallado
     *
     * @var array
     */
    protected $sensitiveRoutes = [
        'api/v1/equipment/*',
        'api/v1/surgeries/*',
        'api/v1/users/*',
        'api/v1/maintenance/*'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Capturar el tiempo de inicio
        $startTime = microtime(true);

        // Procesar la solicitud
        $response = $next($request);

        // Calcular el tiempo de respuesta
        $duration = microtime(true) - $startTime;

        // Preparar datos del log
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration' => round($duration * 1000, 2), // en milisegundos
            'status_code' => $response->status(),
            'request_id' => (string) Str::uuid(),
        ];

        // Añadir headers relevantes
        $logData['headers'] = $this->getRelevantHeaders($request);

        // Logging detallado para rutas sensibles
        if ($this->isSensitiveRoute($request->path())) {
            $logData['request_body'] = $this->sanitizeData($request->all());
            $logData['response_body'] = $this->sanitizeData($response->getContent());
        }

        // Registrar en la base de datos
        $this->logToDatabase($logData);

        // Logging adicional para errores
        if ($response->status() >= 400) {
            $this->logError($logData, $response);
        }

        // Añadir headers de tracking
        $response->headers->set('X-Request-ID', $logData['request_id']);
        $response->headers->set('X-Response-Time', $logData['duration'] . 'ms');

        return $response;
    }

    /**
     * Obtener headers relevantes para el logging
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getRelevantHeaders(Request $request)
    {
        $relevantHeaders = [
            'accept',
            'accept-language',
            'if-none-match',
            'if-modified-since',
            'x-requested-with',
            'x-forwarded-for',
        ];

        return collect($request->headers->all())
            ->only($relevantHeaders)
            ->map(function ($header) {
                return is_array($header) ? implode(', ', $header) : $header;
            })
            ->all();
    }

    /**
     * Verificar si la ruta es sensible
     *
     * @param  string  $path
     * @return bool
     */
    protected function isSensitiveRoute($path)
    {
        return Str::is($this->sensitiveRoutes, $path);
    }

    /**
     * Sanitizar datos sensibles
     *
     * @param  mixed  $data
     * @return mixed
     */
    protected function sanitizeData($data)
    {
        if (is_string($data)) {
            $data = json_decode($data, true) ?? $data;
        }

        if (is_array($data)) {
            array_walk_recursive($data, function (&$value, $key) {
                if (in_array(strtolower($key), ['password', 'token', 'secret', 'key'])) {
                    $value = '[REDACTED]';
                }
            });
        }

        return $data;
    }

    /**
     * Registrar en la base de datos
     *
     * @param  array  $logData
     * @return void
     */
    protected function logToDatabase($logData)
    {
        ActivityLog::create([
            'user_id' => $logData['user_id'],
            'action' => 'api_request',
            'description' => "{$logData['method']} {$logData['url']}",
            'ip_address' => $logData['ip'],
            'user_agent' => $logData['user_agent'],
            'metadata' => [
                'request_id' => $logData['request_id'],
                'duration' => $logData['duration'],
                'status_code' => $logData['status_code'],
                'headers' => $logData['headers'],
            ],
        ]);
    }

    /**
     * Logging especial para errores
     *
     * @param  array  $logData
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    protected function logError($logData, $response)
    {
        Log::error('API Error', [
            'request_id' => $logData['request_id'],
            'method' => $logData['method'],
            'url' => $logData['url'],
            'user_id' => $logData['user_id'],
            'status_code' => $logData['status_code'],
            'error' => $response->getContent(),
            'headers' => $logData['headers'],
        ]);
    }
}
