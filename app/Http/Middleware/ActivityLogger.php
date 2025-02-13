<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            
            // Registrar la actividad del usuario
            DB::table('activity_log')->insert([
                'log_name' => 'user_activity',
                'description' => $this->getActivityDescription($request),
                'subject_type' => get_class($user),
                'subject_id' => $user->id,
                'causer_type' => get_class($user),
                'causer_id' => $user->id,
                'properties' => json_encode([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route' => $request->route() ? $request->route()->getName() : null,
                    'status_code' => $response->getStatusCode(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualizar última actividad del usuario
            $user->update([
                'last_activity_at' => now(),
            ]);
        }

        return $response;
    }

    /**
     * Obtener una descripción legible de la actividad
     */
    private function getActivityDescription(Request $request): string
    {
        $route = $request->route();
        if (!$route) {
            return 'Acceso a URL no enrutada: ' . $request->fullUrl();
        }

        $routeName = $route->getName();
        if (!$routeName) {
            return 'Acceso a ruta sin nombre: ' . $request->fullUrl();
        }

        $descriptions = [
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'register' => 'Registro de usuario',
            'password.request' => 'Solicitud de restablecimiento de contraseña',
            'password.reset' => 'Restablecimiento de contraseña',
            'verification.notice' => 'Vista de aviso de verificación',
            'verification.verify' => 'Verificación de correo electrónico',
            'two-factor.enable' => 'Habilitación de autenticación de dos factores',
            'two-factor.disable' => 'Deshabilitación de autenticación de dos factores',
            'two-factor.verify' => 'Verificación de autenticación de dos factores',
            'configuraciones' => 'Acceso a configuraciones',
        ];

        return $descriptions[$routeName] ?? "Acceso a $routeName";
    }
}
