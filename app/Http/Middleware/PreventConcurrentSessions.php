<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PreventConcurrentSessions
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
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = session()->getId();
            $cacheKey = "user_session_{$user->id}";

            // Verificar si existe una sesión anterior
            $existingSession = Cache::get($cacheKey);

            if ($existingSession && $existingSession !== $sessionId) {
                // Si la sesión actual es diferente a la almacenada,
                // invalidar la sesión actual
                Auth::logout();

                return redirect()->route('login')
                    ->with('error', 'Se ha detectado otra sesión activa. Por favor, inicie sesión nuevamente.');
            }

            // Almacenar el ID de sesión actual
            Cache::put($cacheKey, $sessionId, now()->addHours(2));

            // Registrar actividad del usuario
            $this->logUserActivity($request, $user);
        }

        return $next($request);
    }

    /**
     * Registrar la actividad del usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function logUserActivity(Request $request, $user)
    {
        $data = [
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_activity' => now(),
            'session_id' => session()->getId(),
            'route' => $request->route() ? $request->route()->getName() : $request->path(),
            'method' => $request->method(),
        ];

        Cache::put("user_activity_{$user->id}", $data, now()->addHours(24));

        // Si es una acción significativa, registrar en la base de datos
        if ($this->isSignificantAction($request)) {
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'action' => $request->route() ? $request->route()->getName() : $request->path(),
                'description' => "Acceso a {$request->method()} {$request->path()}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    /**
     * Determinar si la acción es significativa para registrar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isSignificantAction(Request $request)
    {
        $significantActions = [
            'POST', 'PUT', 'DELETE', 'PATCH',
            'password.update',
            'profile.update',
            'equipment.maintenance',
            'surgery.schedule',
            'admin.*'
        ];

        return in_array($request->method(), $significantActions) ||
               Str::is($significantActions, $request->route() ? $request->route()->getName() : '');
    }
}
