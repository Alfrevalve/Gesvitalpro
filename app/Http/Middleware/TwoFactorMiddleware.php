<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_enabled) {
            if (!$request->session()->has('auth.two_factor_confirmed') ||
                !$request->session()->get('auth.two_factor_confirmed')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
