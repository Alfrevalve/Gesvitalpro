<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Closure;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login', // Temporarily exclude the login route for testing
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        try {
            if ($this->isReading($request) || $this->inExceptArray($request) || $this->tokensMatch($request)) {
                // Ensure the session is started
                if (!$request->session()->isStarted()) {
                    $request->session()->start();
                }

                // Check session expiration
                $lastActivity = $request->session()->get('last_activity');
                $sessionLifetime = config('session.lifetime') * 60;
                
                if ($lastActivity && (time() - $lastActivity) > $sessionLifetime) {
                    $this->handleExpiredSession($request);
                    return redirect()->route('login')
                        ->withErrors(['error' => 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.']);
                }

                // Update last activity timestamp
                $request->session()->put('last_activity', time());

                // Regenerate token periodically to prevent attacks
                if (mt_rand(1, 100) <= 2) { // 2% chance
                    $request->session()->regenerateToken();
                }

                return $next($request);
            }

            throw new TokenMismatchException('CSRF token mismatch.');
        } catch (TokenMismatchException $e) {
            return $this->handleTokenMismatch($request, $e);
        }
    }

    /**
     * Handle expired session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function handleExpiredSession($request)
    {
        $request->session()->flush();
        $request->session()->regenerate(true);
        
        Log::warning('Sesión Expirada', [
            'última_actividad' => date('Y-m-d H:i:s', $request->session()->get('last_activity')),
            'tiempo_actual' => date('Y-m-d H:i:s'),
            'uri' => $request->getRequestUri(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * Handle token mismatch exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    protected function handleTokenMismatch($request, $exception)
    {
        Log::error('Error de Token CSRF', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'uri' => $request->getRequestUri(),
            'método' => $request->method(),
            'headers' => $request->headers->all(),
            'última_actividad' => $request->session()->get('last_activity') 
                ? date('Y-m-d H:i:s', $request->session()->get('last_activity')) 
                : null
        ]);

        $request->session()->regenerateToken();

        return redirect()->back()
            ->withInput($request->except(['password', '_token', 'password_confirmation']))
            ->withErrors(['error' => 'Error de verificación de seguridad. Por favor, intente nuevamente.']);
    }

    /**
     * Determine if the HTTP request uses a 'read' verb.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }
}
