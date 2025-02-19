<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    public function index()
    {
        $staffRole = Role::where('slug', 'staff')->first();

        if (!$staffRole) {
            return redirect()->route('dashboard')
                ->with('error', 'El rol de staff no está configurado en el sistema.');
        }

        $staff = User::where('role_id', $staffRole->id)
            ->with('role') // Eager loading de la relación role
            ->orderBy('name')
            ->paginate(10);

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $staffRole = Role::where('slug', 'staff')->first();

        if (!$staffRole) {
            return redirect()->route('staff.index')
                ->with('error', 'El rol de staff no está configurado en el sistema.');
        }

        return view('staff.create', compact('staffRole'));
    }

    public function store(Request $request)
    {
        $staffRole = Role::where('slug', 'staff')->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'position' => $validated['position'],
                'role_id' => $staffRole->id,
            ]);

            // Asignar permisos básicos del staff si es necesario
            if (method_exists($staffRole, 'permissions')) {
                $basicPermissions = ['view_dashboard', 'view_profile'];
                foreach ($basicPermissions as $permission) {
                    if ($staffRole->hasPermission($permission)) {
                        $user->givePermissionTo($permission);
                    }
                }
            }

            DB::commit();

            return redirect()->route('staff.index')
                ->with('success', 'Miembro del staff creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('staff.create')
                ->with('error', 'Error al crear el miembro del staff: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(User $staff)
    {
        // Verificar que el usuario es realmente un miembro del staff
        if ($staff->role?->slug !== 'staff') {
            return redirect()->route('staff.index')
                ->with('error', 'Usuario no encontrado en el staff.');
        }

        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        // Verificar que el usuario es realmente un miembro del staff
        if ($staff->role?->slug !== 'staff') {
            return redirect()->route('staff.index')
                ->with('error', 'Usuario no encontrado en el staff.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:100',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'position' => $validated['position'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $staff->update($updateData);

            DB::commit();

            return redirect()->route('staff.index')
                ->with('success', 'Información del staff actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('staff.edit', $staff)
                ->with('error', 'Error al actualizar el miembro del staff: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(User $staff)
    {
        // Verificar que el usuario es realmente un miembro del staff
        if ($staff->role?->slug !== 'staff') {
            return redirect()->route('staff.index')
                ->with('error', 'Usuario no encontrado en el staff.');
        }

        if ($staff->id === Auth::id()) {
            return redirect()->route('staff.index')
                ->with('error', 'No puede eliminar su propia cuenta.');
        }

        try {
            DB::beginTransaction();

            // Eliminar permisos asociados si existen
            if (method_exists($staff, 'permissions')) {
                $staff->permissions()->detach();
            }

            $staff->delete();

            DB::commit();

            return redirect()->route('staff.index')
                ->with('success', 'Miembro del staff eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('staff.index')
                ->with('error', 'Error al eliminar el miembro del staff: ' . $e->getMessage());
        }
    }
}
