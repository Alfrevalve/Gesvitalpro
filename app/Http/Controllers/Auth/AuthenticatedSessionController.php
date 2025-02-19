<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use App\Services\PerformanceMonitor;

class AuthenticatedSessionController extends Controller
{
    protected $monitor;

    public function __construct(PerformanceMonitor $monitor)
    {
        $this->monitor = $monitor;
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $startTime = microtime(true);

        try {
            $request->authenticate();
            $request->session()->regenerate();

            // Registrar actividad de login exitoso
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("login_successful");

            // Registrar métricas de rendimiento
            $this->monitor->recordAuthenticationMetrics([
                'action' => 'login',
                'status' => 'success',
                'duration' => microtime(true) - $startTime,
                'user_id' => Auth::id(),
            ]);

            return redirect()->intended(RouteServiceProvider::HOME);
        } catch (ValidationException $e) {
            // Registrar intento fallido
            activity()
                ->withProperties([
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("login_failed");

            // Registrar métricas de fallo
            $this->monitor->recordAuthenticationMetrics([
                'action' => 'login',
                'status' => 'failed',
                'duration' => microtime(true) - $startTime,
                'reason' => 'invalid_credentials',
            ]);

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $startTime = microtime(true);
        $userId = Auth::id();

        // Registrar logout
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("logout");
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Registrar métricas de logout
        $this->monitor->recordAuthenticationMetrics([
            'action' => 'logout',
            'status' => 'success',
            'duration' => microtime(true) - $startTime,
            'user_id' => $userId,
        ]);

        return redirect('/');
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function throttleKey(Request $request)
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Configure the rate limiting middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Cache\RateLimiter
     */
    protected function limiter()
    {
        return app(\Illuminate\Cache\RateLimiter::class);
    }

    /**
     * Get the maximum number of attempts for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function maxAttempts()
    {
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;
    }

    /**
     * Get the number of minutes to throttle for.
     *
     * @return int
     */
    protected function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1;
    }
}
