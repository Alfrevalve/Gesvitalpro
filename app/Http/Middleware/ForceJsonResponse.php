<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Forzar que todas las respuestas sean JSON
        $request->headers->set('Accept', 'application/json');

        // Verificar Content-Type para solicitudes POST/PUT
        if ($request->isMethod('POST') || $request->isMethod('PUT')) {
            if (!$request->isJson()) {
                return response()->json([
                    'error' => 'El Content-Type debe ser application/json',
                    'status' => 415
                ], 415);
            }
        }

        $response = $next($request);

        // Asegurar que la respuesta sea JSON
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        // Agregar headers de seguridad específicos para API
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Prevenir cache en respuestas de API
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
