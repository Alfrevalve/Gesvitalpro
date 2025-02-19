<?php

namespace App\Policies;

use App\Models\Surgery;
use App\Models\User;

class SurgeryPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios pueden ver la lista de cirugías (filtrada por sus permisos)
    }

    public function view(User $user, Surgery $surgery): bool
    {
        return $user->canViewSurgery($surgery);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isLineManager();
    }

    public function update(User $user, Surgery $surgery): bool
    {
        if (!$surgery->isEditable()) {
            return false;
        }

        return $user->isAdmin() || 
               ($user->isLineManager() && $user->line_id === $surgery->line_id);
    }

    public function delete(User $user, Surgery $surgery): bool
    {
        if (!$surgery->isEditable()) {
            return false;
        }

        return $user->isAdmin() || 
               ($user->isLineManager() && $user->line_id === $surgery->line_id);
    }

    public function updateStatus(User $user, Surgery $surgery): bool
    {
        return $user->isAdmin() || 
               ($user->isLineManager() && $user->line_id === $surgery->line_id) ||
               $surgery->staff->contains($user);
    }
}
