<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Si el usuario es admin, permitir acceso a todo
        if ($request->user()->hasRole('admin')) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // Si no tiene ningún rol requerido, redirigir al dashboard con mensaje de error
        return redirect()
            ->route('dashboard')
            ->with('error', 'No tiene permiso para acceder a esta sección.');
    }
}
