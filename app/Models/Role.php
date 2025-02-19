<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Guard;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Traits\HasPermissions;

class Role extends Model implements RoleContract
{
    use HasFactory,
        SoftDeletes,
        \Spatie\Permission\Traits\HasPermissions;

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
        'slug' => 'required|string|max:255|unique:roles,slug',
        'description' => 'nullable|string',
    ];

    /**
     * Get the users that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the permissions that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if the role has a specific permission.
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission(string|Permission $permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }

        return $this->permissions->contains($permission);
    }

    /**
     * Check if the role has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the role has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sync the given permissions with the role.
     *
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions()->sync($permissions);
    }

    /**
     * Get all management permissions for this role.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getManagementPermissions(): Collection
    {
        return $this->permissions()->where('slug', 'LIKE', 'manage_%')->get();
    }

    /**
     * Get all view permissions for this role.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getViewPermissions(): Collection
    {
        return $this->permissions()->where('slug', 'LIKE', 'view_%')->get();
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
     * Find a role by its name and guard name.
     *
     * @param string $name
     * @param string|null $guardName
     * @return self
     * @throws \Spatie\Permission\Exceptions\RoleDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName = null): self
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::named($name);
        }

        return $role;
    }

    /**
     * Find a role by its id and guard name.
     *
     * @param int|string $id
     * @param string|null $guardName
     * @return self
     * @throws \Spatie\Permission\Exceptions\RoleDoesNotExist
     */
    public static function findById(int|string $id, ?string $guardName = null): self
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    /**
     * Find or create a role by its name and guard name.
     *
     * @param string $name
     * @param string|null $guardName
     * @return self
     */
    public static function findOrCreate(string $name, ?string $guardName = null): self
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $role = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (! $role) {
            $role = static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $role;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|int|Permission|\BackedEnum $permission
     * @param string|null $guardName
     * @return bool
     */
    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (is_int($permission)) {
            $permission = app(Permission::class)->findById(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Get the default guard name for the model.
     *
     * @return string
     */
    protected function getDefaultGuardName(): string
    {
        return $this->guard_name ?? Guard::getDefaultName(static::class);
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
                'name' => 'jefe_linea',
                'description' => 'Jefe de línea responsable de la gestión de equipos y personal',
                'guard_name' => 'web'
            ],
            [
                'name' => 'instrumentista',
                'description' => 'Instrumentista encargado de la preparación y manejo de equipos',
                'guard_name' => 'web'
            ],
            [
                'name' => 'vendedor',
                'description' => 'Vendedor encargado de la gestión comercial',
                'guard_name' => 'web'
            ]
        ];
    }
}
