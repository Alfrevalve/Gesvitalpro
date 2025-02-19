<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SessionSecurity
{
    /**
     * Tiempo máximo de inactividad en minutos
     */
    protected const INACTIVITY_TIMEOUT = 30;

    /**
     * Tiempo máximo de vida de la sesión en minutos
     */
    protected const SESSION_LIFETIME = 120;

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

            // Verificar tiempo de inactividad
            if ($this->checkInactivity($request)) {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')
                    ->with('error', 'Su sesión ha expirado por inactividad.');
            }

            // Verificar tiempo máximo de sesión
            if ($this->checkSessionLifetime()) {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')
                    ->with('error', 'Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
            }

            // Verificar si el dispositivo está autorizado
            if (!$this->isDeviceAuthorized($request)) {
                Auth::logout();
                Session::flush();
                return redirect()->route('login')
                    ->with('error', 'Dispositivo no autorizado. Por favor, verifique su identidad.');
            }

            // Actualizar última actividad
            $this->updateLastActivity();

            // Rotar identificador de sesión periódicamente
            if ($this->shouldRotateSession()) {
                Session::migrate(true);
            }

            // Actualizar información de seguridad de la sesión
            $this->updateSessionSecurityInfo($request);
        }

        return $next($request);
    }

    /**
     * Verificar tiempo de inactividad
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function checkInactivity(Request $request)
    {
        $lastActivity = Session::get('last_activity');

        return $lastActivity &&
               Carbon::createFromTimestamp($lastActivity)
                     ->addMinutes(self::INACTIVITY_TIMEOUT)
                     ->isPast();
    }

    /**
     * Verificar tiempo máximo de sesión
     *
     * @return bool
     */
    protected function checkSessionLifetime()
    {
        $sessionStart = Session::get('session_start');

        return $sessionStart &&
               Carbon::createFromTimestamp($sessionStart)
                     ->addMinutes(self::SESSION_LIFETIME)
                     ->isPast();
    }

    /**
     * Verificar si el dispositivo está autorizado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isDeviceAuthorized(Request $request)
    {
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $authorizedDevices = Session::get('authorized_devices', []);

        return in_array($deviceFingerprint, $authorizedDevices);
    }

    /**
     * Generar huella digital del dispositivo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function generateDeviceFingerprint(Request $request)
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent(),
            Session::getId()
        ]));
    }

    /**
     * Actualizar última actividad
     *
     * @return void
     */
    protected function updateLastActivity()
    {
        Session::put('last_activity', time());
    }

    /**
     * Verificar si se debe rotar el identificador de sesión
     *
     * @return bool
     */
    protected function shouldRotateSession()
    {
        $lastRotation = Session::get('last_rotation', 0);
        return (time() - $lastRotation) > 300; // Rotar cada 5 minutos
    }

    /**
     * Actualizar información de seguridad de la sesión
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function updateSessionSecurityInfo(Request $request)
    {
        if (!Session::has('session_start')) {
            Session::put('session_start', time());
        }

        if (!Session::has('authorized_devices')) {
            Session::put('authorized_devices', [
                $this->generateDeviceFingerprint($request)
            ]);
        }

        Session::put('last_rotation', time());
        Session::put('security_info', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_verified' => now()->toDateTimeString(),
        ]);
    }
}
