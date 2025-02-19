<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Line;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'line_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function surgeries()
    {
        return $this->hasMany(Surgery::class);
    }

    public function getAvatarUrl(): string
    {
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&color=7F9CF5&background=EBF4FF";
    }

    /**
     * Verifica si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica si el usuario es gerente
     */
    public function isGerente(): bool
    {
        return $this->hasRole('gerente');
    }

    /**
     * Verifica si el usuario puede acceder al panel administrativo
     */
    public function canAccessAdminPanel(): bool
    {
        return $this->isAdmin() || $this->isGerente();
    }

    /**
     * Check if user can manage a specific line
     *
     * @param \App\Models\Line|null $line
     * @return bool
     */
    public function canManageLine(?Line $line = null): bool
    {
        // Admins and Gerentes can manage all lines
        if ($this->isAdmin() || $this->isGerente()) {
            return true;
        }

        // For a specific line, check if user is part of the line's staff
        if ($line) {
            return $line->hasStaffMember($this);
        }

        // Check if user is part of any line's staff
        return $this->belongsToMany(Line::class, 'line_staff')->exists();
    }

    /**
     * Métodos de autorización genéricos usando el sistema de permisos de Spatie
     */
    public function canView($model): bool
    {
        return $this->hasPermissionTo('view_' . $this->getModelName($model));
    }

    public function canCreate($model): bool
    {
        return $this->hasPermissionTo('create_' . $this->getModelName($model));
    }

    public function canEdit($model): bool
    {
        return $this->hasPermissionTo('edit_' . $this->getModelName($model));
    }

    public function canDelete($model): bool
    {
        return $this->hasPermissionTo('delete_' . $this->getModelName($model));
    }

    private function getModelName($model): string
    {
        return strtolower(class_basename($model));
    }
}
