<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * Roles predefinidos
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_GERENTE = 'gerente';
    public const ROLE_STAFF = 'staff';
    public const ROLE_INSTRUMENTIST = 'instrumentista';
    public const ROLE_SALES = 'vendedor';

    /**
     * Estados del usuario
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ON_LEAVE = 'on_leave';

    protected $fillable = [
        'name',
        'email',
        'password',
        'line_id',
        'status',
        'phone',
        'position',
        'employee_id',
        'last_login_at',
        'leave_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'leave_until' => 'datetime',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|max:255',
        'line_id' => 'nullable|exists:lines,id',
        'status' => 'required|in:active,inactive,on_leave',
        'phone' => 'nullable|string|max:20',
        'position' => 'nullable|string|max:100',
        'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
        'leave_until' => 'nullable|date|after:now',
    ];

    /**
     * Relaciones
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class);
    }

    public function lines(): BelongsToMany
    {
        return $this->belongsToMany(Line::class, 'line_staff')
            ->withTimestamps()
            ->withPivot(['role']);
    }

    /**
     * Métodos de verificación de roles
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isGerente(): bool
    {
        return $this->hasRole(self::ROLE_GERENTE);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(self::ROLE_STAFF);
    }

    public function isInstrumentist(): bool
    {
        return $this->hasRole(self::ROLE_INSTRUMENTIST);
    }

    public function isSales(): bool
    {
        return $this->hasRole(self::ROLE_SALES);
    }

    /**
     * Métodos de estado
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function isOnLeave(): bool
    {
        return $this->status === self::STATUS_ON_LEAVE;
    }

    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }

    public function setOnLeave(Carbon $until): bool
    {
        return $this->update([
            'status' => self::STATUS_ON_LEAVE,
            'leave_until' => $until,
        ]);
    }

    /**
     * Métodos de acceso y permisos
     */
    public function canAccessAdminPanel(): bool
    {
        return $this->isAdmin() || $this->isGerente();
    }

    public function canManageLine(?Line $line = null): bool
    {
        if ($this->isAdmin() || $this->isGerente()) {
            return true;
        }

        if ($line) {
            return $line->hasStaffMember($this);
        }

        return $this->lines()->exists();
    }

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

    /**
     * Métodos de utilidad
     */
    public function getAvatarUrl(): string
    {
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&color=7F9CF5&background=EBF4FF";
    }

    private function getModelName($model): string
    {
        return strtolower(class_basename($model));
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Métodos de estadísticas
     */
    public function getStats(Carbon $start = null, Carbon $end = null): array
    {
        $query = $this->visitas();

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        return [
            'visitas' => [
                'total' => $query->count(),
                'completadas' => $query->where('estado', 'realizada')->count(),
                'pendientes' => $query->where('estado', 'programada')->count(),
            ],
            'surgeries' => [
                'total' => $this->surgeries()->count(),
                'this_month' => $this->surgeries()
                    ->whereMonth('created_at', now()->month)
                    ->count(),
            ],
            'lines' => [
                'total' => $this->lines()->count(),
                'roles' => $this->lines()
                    ->pluck('pivot.role')
                    ->countBy()
                    ->toArray(),
            ],
        ];
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOnLeave($query)
    {
        return $query->where('status', self::STATUS_ON_LEAVE);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->role($role);
    }

    public function scopeByLine($query, Line $line)
    {
        return $query->whereHas('lines', function($q) use ($line) {
            $q->where('lines.id', $line->id);
        });
    }

    /**
     * Verificar disponibilidad
     */
    public function isAvailableOn(Carbon $date): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->isOnLeave() && $this->leave_until && $this->leave_until->gt($date)) {
            return false;
        }

        return !$this->surgeries()
            ->whereDate('surgery_date', $date)
            ->exists();
    }

    /**
     * Obtener próximas actividades
     */
    public function getUpcomingActivities(int $days = 7): array
    {
        $endDate = now()->addDays($days);

        return [
            'visitas' => $this->visitas()
                ->where('fecha_hora', '>=', now())
                ->where('fecha_hora', '<=', $endDate)
                ->orderBy('fecha_hora')
                ->get(),
            'surgeries' => $this->surgeries()
                ->where('surgery_date', '>=', now())
                ->where('surgery_date', '<=', $endDate)
                ->orderBy('surgery_date')
                ->get(),
        ];
    }
}
