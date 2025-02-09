<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if (method_exists($model, 'auditable') && $model->auditable()) {
            $this->logAudit($model, 'created');
        }
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (method_exists($model, 'auditable') && $model->auditable()) {
            $this->logAudit($model, 'updated');
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (method_exists($model, 'auditable') && $model->auditable()) {
            $this->logAudit($model, 'deleted');
        }
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        if (method_exists($model, 'auditable') && $model->auditable()) {
            $this->logAudit($model, 'restored');
        }
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        if (method_exists($model, 'auditable') && $model->auditable()) {
            $this->logAudit($model, 'force deleted');
        }
    }

    /**
     * Log the audit event
     */
    private function logAudit(Model $model, string $action): void
    {
        $user = Auth::user();
        
        \DB::table('audit_logs')->insert([
            'user_id' => $user ? $user->id : null,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'action' => $action,
            'changes' => json_encode($model->getDirty()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
