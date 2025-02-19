<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Obtener el usuario autenticado actual
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleSlug(),
                'lines' => $user->lines()->select('id', 'name')->get(),
            ]
        ]);
    }

    /**
     * Obtener los permisos del usuario autenticado
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function permissions(Request $request): JsonResponse
    {
        $user = $request->user();
        $permissions = [];

        if ($user->role) {
            $permissions = $user->role->permissions()
                ->select('slug', 'name', 'description')
                ->get();
        }

        return response()->json([
            'data' => [
                'role' => $user->getRoleSlug(),
                'permissions' => $permissions
            ]
        ]);
    }
}
