<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:permissions,slug',
        'description' => 'nullable|string',
    ];

    /**
     * Get the roles that have this permission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the default permissions for the application.
     *
     * @return array<string, string>
     */
    public static function defaultPermissions(): array
    {
        return [
            'view_dashboard' => 'Ver dashboard',
            'manage_lines' => 'Gestionar líneas',
            'view_lines' => 'Ver líneas',
            'manage_equipment' => 'Gestionar equipos',
            'view_equipment' => 'Ver equipos',
            'manage_surgeries' => 'Gestionar cirugías',
            'view_surgeries' => 'Ver cirugías',
            'manage_visits' => 'Gestionar visitas',
            'view_visits' => 'Ver visitas',
            'generate_reports' => 'Generar reportes',
            'manage_storage' => 'Gestionar almacén',
            'view_storage' => 'Ver almacén',
            'manage_dispatch' => 'Gestionar despacho',
            'view_dispatch' => 'Ver despacho',
        ];
    }

    /**
     * Scope a query to only include permissions with a specific slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get all management permissions.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getManagementPermissions(): Collection
    {
        return static::where('slug', 'LIKE', 'manage_%')->get();
    }

    /**
     * Get all view permissions.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getViewPermissions(): Collection
    {
        return static::where('slug', 'LIKE', 'view_%')->get();
    }

    /**
     * Check if the permission is a management permission.
     *
     * @return bool
     */
    public function isManagementPermission(): bool
    {
        return str_starts_with($this->slug, 'manage_');
    }

    /**
     * Check if the permission is a view permission.
     *
     * @return bool
     */
    public function isViewPermission(): bool
    {
        return str_starts_with($this->slug, 'view_');
    }

    /**
     * Get the related view permission for a management permission.
     *
     * @return \App\Models\Permission|null
     */
    public function getRelatedViewPermission(): ?Permission
    {
        if (!$this->isManagementPermission()) {
            return null;
        }

        $viewSlug = str_replace('manage_', 'view_', $this->slug);
        return static::withSlug($viewSlug)->first();
    }

    /**
     * Get the related management permission for a view permission.
     *
     * @return \App\Models\Permission|null
     */
    public function getRelatedManagementPermission(): ?Permission
    {
        if (!$this->isViewPermission()) {
            return null;
        }

        $manageSlug = str_replace('view_', 'manage_', $this->slug);
        return static::withSlug($manageSlug)->first();
    }
}
