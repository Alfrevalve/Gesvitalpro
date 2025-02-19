<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Institucion;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitucionPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver el listado de instituciones.
     */
    public function viewAny(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden ver el listado
    }

    /**
     * Determinar si el usuario puede ver la institución.
     */
    public function view(User $user, Institucion $institucion): bool
    {
        return true; // Todos los usuarios autenticados pueden ver detalles
    }

    /**
     * Determinar si el usuario puede crear instituciones.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('coordinador');
    }

    /**
     * Determinar si el usuario puede actualizar la institución.
     */
    public function update(User $user, Institucion $institucion): bool
    {
        return $user->isAdmin() || $user->hasRole('coordinador');
    }

    /**
     * Determinar si el usuario puede eliminar la institución.
     */
    public function delete(User $user, Institucion $institucion): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede actualizar la ubicación de la institución.
     */
    public function updateLocation(User $user, Institucion $institucion): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasRole('coordinador')) {
            // Verificar si el usuario es coordinador de la región/departamento
            return $user->departamentos->contains($institucion->departamento);
        }

        return false;
    }

    /**
     * Determinar si el usuario puede exportar datos de instituciones.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('coordinador');
    }

    /**
     * Determinar si el usuario puede ver estadísticas.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('coordinador');
    }

    /**
     * Determinar si el usuario puede calcular rutas.
     */
    public function calculateRoute(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden calcular rutas
    }

    /**
     * Determinar si el usuario puede buscar instituciones cercanas.
     */
    public function searchNearby(User $user): bool
    {
        return true; // Todos los usuarios autenticados pueden buscar cercanas
    }

    /**
     * Determinar si el usuario puede sincronizar datos con MINSA.
     */
    public function syncWithMinsa(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede gestionar la configuración de geolocalización.
     */
    public function manageGeoSettings(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede ver el historial de actualizaciones.
     */
    public function viewUpdateHistory(User $user, Institucion $institucion): bool
    {
        return $user->isAdmin() || 
               ($user->hasRole('coordinador') && 
                $user->departamentos->contains($institucion->departamento));
    }

    /**
     * Determinar si el usuario puede marcar una ubicación como verificada.
     */
    public function verifyLocation(User $user, Institucion $institucion): bool
    {
        return $user->isAdmin() || 
               ($user->hasRole('coordinador') && 
                $user->departamentos->contains($institucion->departamento));
    }

    /**
     * Determinar si el usuario puede gestionar las relaciones entre instituciones.
     */
    public function manageRelations(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('coordinador');
    }

    /**
     * Determinar si el usuario puede ver métricas avanzadas.
     */
    public function viewAdvancedMetrics(User $user): bool
    {
        return $user->isAdmin();
    }
}
