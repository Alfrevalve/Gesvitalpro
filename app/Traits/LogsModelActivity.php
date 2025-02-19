<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

trait LogsModelActivity
{
    protected static function bootLogsModelActivity()
    {
        static::created(function (Model $model) {
            static::logActivity('created', $model);
        });

        static::updated(function (Model $model) {
            // Solo registrar si hubo cambios en atributos importantes
            if ($model->wasChanged(static::getImportantAttributes())) {
                static::logActivity('updated', $model, [
                    'changes' => $model->getChanges(),
                    'original' => array_intersect_key(
                        $model->getOriginal(), 
                        $model->getChanges()
                    )
                ]);
            }
        });

        static::deleted(function (Model $model) {
            static::logActivity('deleted', $model);
        });
    }

    protected static function getImportantAttributes(): array
    {
        return [
            'status',
            'surgery_date',
            'next_maintenance',
            'line_id',
            'equipment_id',
            'doctor_id',
            'institution_id'
        ];
    }

    protected static function logActivity(string $action, Model $model, array $additional = [])
    {
        $user = Auth::user();
        $modelName = class_basename($model);

        $logData = array_merge([
            'action' => $action,
            'model' => $modelName,
            'model_id' => $model->id,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ], $additional);

        // Registrar en el canal específico del modelo
        Log::channel('model_activity')->info("{$modelName} {$action}", $logData);

        // Si es una acción crítica, también registrar en el canal de auditoría
        if (static::isCriticalAction($action, $model)) {
            Log::channel('audit')->warning("Critical {$modelName} {$action}", $logData);
        }

        // Almacenar en la base de datos para consultas
        \App\Models\ActivityLog::create([
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'user_id' => $user?->id,
            'changes' => $additional['changes'] ?? null,
            'original' => $additional['original'] ?? null,
            'ip_address' => request()->ip(),
        ]);
    }

    protected static function isCriticalAction(string $action, Model $model): bool
    {
        // Definir qué acciones son críticas según el modelo
        $criticalActions = [
            'Surgery' => ['created', 'deleted'],
            'Equipment' => ['deleted', 'status_changed'],
            'User' => ['created', 'deleted', 'role_changed'],
            'Line' => ['created', 'deleted'],
        ];

        $modelName = class_basename($model);
        return in_array($action, $criticalActions[$modelName] ?? []);
    }

    public function getActivityLogs()
    {
        return \App\Models\ActivityLog::where([
            'model_type' => get_class($this),
            'model_id' => $this->id
        ])->latest()->get();
    }

    public function getRecentActivity(int $limit = 10)
    {
        return \App\Models\ActivityLog::where([
            'model_type' => get_class($this),
            'model_id' => $this->id
        ])->latest()->limit($limit)->get();
    }

    public function getActivityByUser($userId)
    {
        return \App\Models\ActivityLog::where([
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'user_id' => $userId
        ])->latest()->get();
    }

    public function getActivityBetweenDates($startDate, $endDate)
    {
        return \App\Models\ActivityLog::where([
            'model_type' => get_class($this),
            'model_id' => $this->id
        ])->whereBetween('created_at', [$startDate, $endDate])
          ->latest()
          ->get();
    }
}
