<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'changes',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtener el usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el modelo auditado
     */
    public function auditable()
    {
        return $this->morphTo('model');
    }

    /**
     * Registrar una nueva entrada de auditoría
     */
    public static function log($action, $model, $changes = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'action' => $action,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Obtener una descripción legible de los cambios
     */
    public function getChangesDescriptionAttribute(): string
    {
        if (!$this->changes) {
            return 'No se registraron cambios';
        }

        $description = [];
        foreach ($this->changes as $field => $change) {
            if (is_array($change)) {
                $old = $change['old'] ?? 'no definido';
                $new = $change['new'] ?? 'no definido';
                $description[] = "$field cambió de '$old' a '$new'";
            } else {
                $description[] = "$field: $change";
            }
        }

        return implode(', ', $description);
    }

    /**
     * Obtener el nombre de la acción en español
     */
    public function getActionNameAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Creado',
            'updated' => 'Actualizado',
            'deleted' => 'Eliminado',
            'restored' => 'Restaurado',
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'failed_login' => 'Intento fallido de inicio de sesión',
            default => ucfirst($this->action)
        };
    }
}
