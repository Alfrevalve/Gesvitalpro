<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Role extends SpatieRole
{
    use HasFactory, SoftDeletes;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
        'guard_name'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (!$role->guard_name) {
                $role->guard_name = 'web';
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    /**
     * Get the users that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            User::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.role_pivot_key'),
            config('permission.column_names.model_morph_key')
        );
    }

    /**
     * Check if this is an admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    /**
     * Check if this is a manager role.
     *
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->name === 'gerente';
    }

    /**
     * Get the number of users assigned to this role.
     *
     * @return int
     */
    public function getUserCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Scope a query to only include active roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Get the default roles for the application.
     *
     * @return array<string, array>
     */
    public static function defaultRoles(): array
    {
        return [
            [
                'name' => 'admin',
                'description' => 'Administrador del sistema con acceso total',
                'guard_name' => 'web'
            ],
            [
                'name' => 'gerente',
                'description' => 'Gerente con acceso a gestión y reportes generales',
                'guard_name' => 'web'
            ],
            [
                'name' => 'supervisor',
                'description' => 'Supervisor responsable de la gestión de equipos y personal',
                'guard_name' => 'web'
            ],
            [
                'name' => 'vendedor',
                'description' => 'Vendedor encargado de la gestión comercial',
                'guard_name' => 'web'
            ],
            [
                'name' => 'tecnico',
                'description' => 'Técnico encargado del mantenimiento y preparación de equipos',
                'guard_name' => 'web'
            ]
        ];
    }
}
