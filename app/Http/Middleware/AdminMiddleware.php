<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->canAccessAdminPanel()) {
            abort(403, 'No tienes permiso para acceder al panel administrativo.');
        }

        return $next($request);
    }
}
