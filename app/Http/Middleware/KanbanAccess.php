<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KanbanAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder al tablero Kanban.');
        }

        if (!$user->hasAnyRole(['admin', 'gerente', 'jefe_linea', 'instrumentista'])) {
            return redirect()->route('dashboard')
                ->with('error', 'No tiene permisos para acceder al tablero Kanban.');
        }

        return $next($request);
    }
}
