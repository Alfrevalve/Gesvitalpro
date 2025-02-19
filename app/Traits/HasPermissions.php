<?php

namespace App\Traits;

trait HasPermissions
{
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isGerente()
    {
        return $this->hasRole('gerente');
    }

    public function isVendedor()
    {
        return $this->hasRole('vendedor');
    }

    public function hasPermission($permission)
    {
        return $this->roles()->whereHas('permissions', function($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    public function hasRole($role)
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function canAccessFilament()
    {
        return $this->isAdmin() || $this->isGerente();
    }

    public function canView($model)
    {
        $permission = 'view_' . strtolower(class_basename($model));
        return $this->hasPermission($permission);
    }

    public function canCreate($model)
    {
        $permission = 'create_' . strtolower(class_basename($model));
        return $this->hasPermission($permission);
    }

    public function canEdit($model)
    {
        $permission = 'edit_' . strtolower(class_basename($model));
        return $this->hasPermission($permission);
    }

    public function canDelete($model)
    {
        $permission = 'delete_' . strtolower(class_basename($model));
        return $this->hasPermission($permission);
    }
}
