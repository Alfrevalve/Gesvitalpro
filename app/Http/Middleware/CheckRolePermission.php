<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Permission;

class CheckRolePermission
{
    /**
     * Mapeo de rutas a permisos requeridos
     */
    protected const ROUTE_PERMISSIONS = [
        'equipment.*' => [
            'GET' => 'equipment.view',
            'POST' => 'equipment.create',
            'PUT' => 'equipment.update',
            'DELETE' => 'equipment.delete'
        ],
        'surgeries.*' => [
            'GET' => 'surgeries.view',
            'POST' => 'surgeries.schedule',
            'PUT' => 'surgeries.update',
            'DELETE' => 'surgeries.cancel'
        ],
        'maintenance.*' => [
            'GET' => 'maintenance.view',
            'POST' => 'maintenance.schedule',
            'PUT' => 'maintenance.complete'
        ],
        'admin.*' => [
            '*' => 'admin.access'
        ]
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $this->handleUnauthorized($request);
        }

        $user = Auth::user();
        $route = $request->route()->getName();
        $method = $request->method();

        // Verificar permisos en caché
        $cacheKey = "user_permissions_{$user->id}";
        $userPermissions = Cache::remember($cacheKey, 3600, function () use ($user) {
            return $this->getUserPermissions($user);
        });

        // Obtener permisos requeridos para la ruta
        $requiredPermissions = $this->getRequiredPermissions($route, $method);

        // Si no hay permisos requeridos, permitir acceso
        if (empty($requiredPermissions)) {
            return $next($request);
        }

        // Verificar si el usuario tiene los permisos necesarios
        foreach ($requiredPermissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                return $this->handleUnauthorized($request, $permission);
            }
        }

        // Registrar el acceso autorizado
        $this->logAccess($request, $user, $route, true);

        return $next($request);
    }

    /**
     * Obtener permisos del usuario
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function getUserPermissions($user)
    {
        $permissions = [];

        // Permisos directos del rol
        if ($user->role) {
            $rolePermissions = Permission::where('role', $user->role)
                ->pluck('name')
                ->toArray();
            $permissions = array_merge($permissions, $rolePermissions);
        }

        // Permisos específicos del usuario
        $userPermissions = $user->permissions()
            ->pluck('name')
            ->toArray();
        $permissions = array_merge($permissions, $userPermissions);

        // Permisos heredados
        if ($user->role === 'admin') {
            $permissions[] = '*';
        }

        return array_unique($permissions);
    }

    /**
     * Obtener permisos requeridos para una ruta
     *
     * @param  string  $route
     * @param  string  $method
     * @return array
     */
    protected function getRequiredPermissions($route, $method)
    {
        $permissions = [];

        foreach (self::ROUTE_PERMISSIONS as $pattern => $methodPermissions) {
            if (fnmatch($pattern, $route)) {
                // Verificar permisos específicos del método
                if (isset($methodPermissions[$method])) {
                    $permissions[] = $methodPermissions[$method];
                }
                // Verificar permisos para cualquier método
                if (isset($methodPermissions['*'])) {
                    $permissions[] = $methodPermissions['*'];
                }
            }
        }

        return $permissions;
    }

    /**
     * Manejar acceso no autorizado
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $permission
     * @return mixed
     */
    protected function handleUnauthorized(Request $request, $permission = null)
    {
        // Registrar el intento de acceso no autorizado
        if (Auth::check()) {
            $this->logAccess($request, Auth::user(), $request->route()->getName(), false, $permission);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'No tiene permiso para realizar esta acción.',
                'required_permission' => $permission
            ], 403);
        }

        return redirect()
            ->route('home')
            ->with('error', 'No tiene permiso para acceder a esta sección.');
    }

    /**
     * Registrar acceso
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @param  string  $route
     * @param  bool  $authorized
     * @param  string|null  $permission
     * @return void
     */
    protected function logAccess($request, $user, $route, $authorized, $permission = null)
    {
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'permission_check',
            'description' => sprintf(
                'Acceso %s a %s. Permiso requerido: %s',
                $authorized ? 'autorizado' : 'denegado',
                $route,
                $permission ?? 'ninguno'
            ),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'route' => $route,
                'method' => $request->method(),
                'authorized' => $authorized,
                'permission' => $permission,
            ],
        ]);
    }
}
