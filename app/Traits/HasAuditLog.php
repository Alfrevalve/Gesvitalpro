<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasAuditLog
{
    /**
     * Boot the trait
     */
    protected static function bootHasAuditLog()
    {
        static::created(function (Model $model) {
            static::logActivity('created', $model);
        });

        static::updated(function (Model $model) {
            static::logActivity('updated', $model);
        });

        static::deleted(function (Model $model) {
            static::logActivity('deleted', $model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                static::logActivity('restored', $model);
            });
        }
    }

    /**
     * Registrar actividad en el log
     */
    protected static function logActivity(string $action, Model $model): void
    {
        try {
            $user = Auth::user();
            $changes = [];
            $original = [];

            if ($action === 'updated') {
                $changes = $model->getChanges();
                $original = array_intersect_key($model->getOriginal(), $changes);
            } elseif ($action === 'created') {
                $changes = $model->getAttributes();
            }

            // Sanitizar datos sensibles
            $changes = static::sanitizeData($changes);
            $original = static::sanitizeData($original);

            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'changes' => $changes,
                'original' => $original,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'route' => request()->route()?->getName(),
                    'method' => request()->method(),
                    'url' => request()->fullUrl(),
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->getRoleSlug()
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al registrar actividad: ' . $e->getMessage(), [
                'model' => get_class($model),
                'id' => $model->id,
                'action' => $action
            ]);
        }
    }

    /**
     * Sanitizar datos sensibles
     */
    protected static function sanitizeData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            '_token',
            'api_key',
            'secret',
            'key',
            'remember_token',
            'email_verified_at'
        ];

        return collect($data)->map(function ($value, $key) use ($sensitiveFields) {
            // Sanitizar campos sensibles
            if (in_array($key, $sensitiveFields)) {
                return '[REDACTED]';
            }

            // Sanitizar campos que contienen palabras sensibles
            if (str_contains(strtolower($key), ['password', 'token', 'secret', 'key'])) {
                return '[REDACTED]';
            }

            return $value;
        })->toArray();
    }

    /**
     * Obtener el historial de actividad del modelo
     */
    public function activityLogs()
    {
        return ActivityLog::where('model_type', get_class($this))
            ->where('model_id', $this->id)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Obtener el último cambio registrado
     */
    public function getLastActivityAttribute()
    {
        return $this->activityLogs()->first();
    }

    /**
     * Obtener el usuario que realizó el último cambio
     */
    public function getLastModifiedByAttribute()
    {
        return $this->lastActivity?->user;
    }

    /**
     * Verificar si el modelo ha sido modificado en las últimas X horas
     */
    public function hasBeenModifiedInLastHours(int $hours): bool
    {
        return $this->activityLogs()
            ->where('created_at', '>=', now()->subHours($hours))
            ->exists();
    }

    /**
     * Obtener resumen de cambios para el período especificado
     */
    public function getActivitySummary(string $period = 'day'): array
    {
        $query = $this->activityLogs();

        switch ($period) {
            case 'day':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
        }

        return $query->get()->groupBy('action')
            ->map(fn($group) => $group->count())
            ->toArray();
    }
}
