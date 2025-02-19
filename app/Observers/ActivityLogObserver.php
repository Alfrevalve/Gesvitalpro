<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->logActivity('created', $model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Solo registrar si hubo cambios en atributos importantes
        if ($model->wasChanged($this->getImportantAttributes($model))) {
            $this->logActivity('updated', $model, [
                'changes' => $model->getChanges(),
                'original' => array_intersect_key(
                    $model->getOriginal(),
                    $model->getChanges()
                )
            ]);
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->logActivity('deleted', $model);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->logActivity('restored', $model);
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->logActivity('force_deleted', $model);
    }

    /**
     * Log the activity.
     */
    protected function logActivity(string $action, Model $model, array $additional = []): void
    {
        // Get authenticated user or use system user for seeding
        $user = Auth::user();
        $userId = $user?->id;

        // If no authenticated user (e.g. during seeding), try to get user from model
        if (!$userId && $model instanceof \App\Models\User) {
            $userId = $model->id;
        }

        // Set default changes based on action type
        $changes = $additional['changes'] ?? ($action === 'created' ? $model->getAttributes() : []);

        // Only create log if we have a user_id
        if ($userId) {
            // For create action, original is same as changes
            // For other actions, use provided original or empty array
            $original = $action === 'created'
                ? $changes
                : ($additional['original'] ?? []);

            ActivityLog::create([
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'user_id' => $userId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'changes' => $changes,
                'original' => $original,
            ]);
        }

        // Si es una acción crítica, registrar en el log de auditoría
        if ($this->isCriticalAction($action, $model)) {
            \Illuminate\Support\Facades\Log::channel('audit')->warning("Critical {$action} on " . class_basename($model), [
                'model_id' => $model->getKey(),
                'user_id' => $user?->id,
                'changes' => $additional['changes'] ?? null,
            ]);
        }
    }

    /**
     * Get important attributes for the model.
     */
    protected function getImportantAttributes(Model $model): array
    {
        // Atributos importantes por modelo
        $importantAttributes = [
            'App\Models\Surgery' => [
                'status',
                'surgery_date',
                'doctor',
                'patient_name',
            ],
            'App\Models\Equipment' => [
                'status',
                'next_maintenance',
                'line_id',
            ],
            'App\Models\User' => [
                'role',
                'email',
                'status',
            ],
            'App\Models\Line' => [
                'name',
                'status',
            ],
        ];

        return $importantAttributes[get_class($model)] ?? ['id'];
    }

    /**
     * Determine if the action is critical.
     */
    protected function isCriticalAction(string $action, Model $model): bool
    {
        $criticalActions = config('audit.security.critical_actions.notify_admins', []);
        $modelName = class_basename($model);

        foreach ($criticalActions as $criticalAction) {
            [$model, $actionType] = explode('.', $criticalAction);
            if (strtolower($modelName) === strtolower($model) && $action === $actionType) {
                return true;
            }
        }

        return false;
    }
}
