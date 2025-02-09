<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionTimeout
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
            $lastActivity = Session::get('last_activity');
            $sessionLifetime = config('session.lifetime') * 60; // Convertir minutos a segundos

            if ($lastActivity !== null) {
                if (time() - $lastActivity > $sessionLifetime) {
                    Auth::logout();
                    Session::flush();
                    Session::regenerate(true);

                    Log::warning('Sesión expirada por inactividad', [
                        'user_id' => Auth::id(),
                        'last_activity' => date('Y-m-d H:i:s', $lastActivity),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);

                    if ($request->ajax()) {
                        return response()->json([
                            'error' => 'Sesión expirada',
                            'message' => 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.',
                            'code' => 'SESSION_EXPIRED'
                        ], 401);
                    }

                    return redirect()->route('login')
                        ->with('error', 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.');
                }
            }

            // Actualizar timestamp de última actividad
            Session::put('last_activity', time());

            // Regenerar sesión periódicamente para prevenir ataques
            if (!Session::has('session_generated_at') || 
                time() - Session::get('session_generated_at') > (30 * 60)) { // Regenerar cada 30 minutos
                Session::put('session_generated_at', time());
                Session::regenerate(true);
            }
        }

        return $next($request);
    }
}
