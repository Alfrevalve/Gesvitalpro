<?php

namespace App\Policies;

use App\Models\Line;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden ver la lista
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Line $line): bool
    {
        // Admins y gerentes pueden ver todas las líneas
        if ($user->isAdmin() || $user->isGerente()) {
            return true;
        }

        // Otros usuarios solo pueden ver las líneas a las que están asignados
        return $user->lines->contains($line);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo admins y gerentes pueden crear líneas
        return $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Line $line): bool
    {
        // Solo admins y gerentes pueden actualizar líneas
        return $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Line $line): bool
    {
        // Solo admins pueden eliminar líneas
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage the line's schedule.
     */
    public function manageSchedule(User $user, Line $line): bool
    {
        // Admins, gerentes y jefes de línea pueden gestionar el calendario
        if ($user->isAdmin() || $user->isGerente()) {
            return true;
        }

        // Jefes de línea solo pueden gestionar sus líneas asignadas
        return $user->isJefeLinea() && $user->lines->contains($line);
    }

    /**
     * Determine whether the user can manage the line's equipment.
     */
    public function manageEquipment(User $user, Line $line): bool
    {
        // Admins, gerentes y jefes de línea pueden gestionar equipos
        if ($user->isAdmin() || $user->isGerente()) {
            return true;
        }

        // Jefes de línea solo pueden gestionar equipos de sus líneas
        return $user->isJefeLinea() && $user->lines->contains($line);
    }

    /**
     * Determine whether the user can manage the line's staff.
     */
    public function manageStaff(User $user, Line $line): bool
    {
        // Solo admins y gerentes pueden gestionar el personal
        return $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can view the line's dashboard.
     */
    public function viewDashboard(User $user, Line $line): bool
    {
        // Admins y gerentes pueden ver todos los dashboards
        if ($user->isAdmin() || $user->isGerente()) {
            return true;
        }

        // Otros usuarios solo pueden ver dashboards de sus líneas asignadas
        return $user->lines->contains($line);
    }
}
