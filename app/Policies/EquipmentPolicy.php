<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios pueden ver la lista de equipos (filtrada por sus permisos)
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $user->isAdmin() || $user->line_id === $equipment->line_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->isAdmin() || 
               ($user->isLineManager() && $user->line_id === $equipment->line_id);
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->isAdmin();
    }

    public function updateStatus(User $user, Equipment $equipment): bool
    {
        return $user->isAdmin() || 
               ($user->isLineManager() && $user->line_id === $equipment->line_id);
    }

    public function manageMaintenance(User $user, Equipment $equipment): bool
    {
        return $user->isAdmin() || 
               ($user->isLineManager() && $user->line_id === $equipment->line_id);
    }
}
