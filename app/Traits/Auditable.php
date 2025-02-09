<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            static::logChanges('created', $model);
        });

        static::updated(function ($model) {
            static::logChanges('updated', $model);
        });

        static::deleted(function ($model) {
            static::logChanges('deleted', $model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                static::logChanges('restored', $model);
            });
        }
    }

    protected static function logChanges($action, $model)
    {
        $changes = null;

        if ($action === 'updated') {
            $changes = [];
            foreach ($model->getDirty() as $key => $value) {
                $changes[$key] = [
                    'old' => $model->getOriginal($key),
                    'new' => $value
                ];
            }
        } elseif ($action === 'created') {
            $changes = $model->getAttributes();
        }

        AuditLog::log($action, $model, $changes);
    }

    /**
     * Obtener los registros de auditoría relacionados con este modelo
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'model');
    }
}
