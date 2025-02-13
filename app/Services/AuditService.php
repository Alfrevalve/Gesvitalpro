<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Registrar una acción en el log de auditoría
     */
    public function log(string $action, string $model, int $modelId, array $changes = [], string $description = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $model,
                'model_id' => $modelId,
                'changes' => $changes,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría', [
                'error' => $e->getMessage(),
                'action' => $action,
                'model' => $model,
                'model_id' => $modelId,
                'changes' => $changes
            ]);
        }
    }

    /**
     * Registrar un inicio de sesión
     */
    public function logLogin(int $userId, bool $success, string $reason = null): void
    {
        $this->log(
            $success ? 'login_success' : 'login_failed',
            'User',
            $userId,
            [],
            $reason
        );
    }

    /**
     * Registrar un cierre de sesión
     */
    public function logLogout(int $userId): void
    {
        $this->log(
            'logout',
            'User',
            $userId
        );
    }

    /**
     * Registrar un cambio de contraseña
     */
    public function logPasswordChange(int $userId): void
    {
        $this->log(
            'password_change',
            'User',
            $userId
        );
    }

    /**
     * Registrar una creación de registro
     */
    public function logCreated(string $model, int $modelId, array $attributes): void
    {
        $this->log(
            'created',
            $model,
            $modelId,
            ['attributes' => $attributes]
        );
    }

    /**
     * Registrar una actualización de registro
     */
    public function logUpdated(string $model, int $modelId, array $original, array $changes): void
    {
        $this->log(
            'updated',
            $model,
            $modelId,
            [
                'original' => $original,
                'changes' => $changes
            ]
        );
    }

    /**
     * Registrar una eliminación de registro
     */
    public function logDeleted(string $model, int $modelId, array $attributes): void
    {
        $this->log(
            'deleted',
            $model,
            $modelId,
            ['attributes' => $attributes]
        );
    }

    /**
     * Registrar un acceso a datos sensibles
     */
    public function logSensitiveAccess(string $model, int $modelId, string $field): void
    {
        $this->log(
            'sensitive_access',
            $model,
            $modelId,
            ['field' => $field]
        );
    }

    /**
     * Registrar un error de sistema
     */
    public function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
        
        if (Auth::check()) {
            $this->log(
                'system_error',
                'System',
                0,
                $context,
                $message
            );
        }
    }

    /**
     * Obtener el historial de auditoría de un modelo
     */
    public function getModelHistory(string $model, int $modelId): array
    {
        return AuditLog::where('model_type', $model)
            ->where('model_id', $modelId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'action' => $log->action,
                    'user' => $log->user->name,
                    'date' => $log->created_at->format('d/m/Y H:i:s'),
                    'changes' => $log->changes,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent
                ];
            })
            ->toArray();
    }
}
