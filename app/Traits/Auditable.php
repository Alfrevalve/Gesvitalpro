<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the trait
     */
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            app(AuditService::class)->logCreated(
                class_basename($model),
                $model->id,
                $model->getAttributes()
            );
        });

        static::updated(function (Model $model) {
            $original = array_intersect_key(
                $model->getOriginal(),
                $model->getDirty()
            );

            app(AuditService::class)->logUpdated(
                class_basename($model),
                $model->id,
                $original,
                $model->getDirty()
            );
        });

        static::deleted(function (Model $model) {
            app(AuditService::class)->logDeleted(
                class_basename($model),
                $model->id,
                $model->getAttributes()
            );
        });
    }

    /**
     * Obtener el historial de auditoría del modelo
     */
    public function getAuditHistory(): array
    {
        return app(AuditService::class)->getModelHistory(
            class_basename($this),
            $this->id
        );
    }

    /**
     * Registrar acceso a campo sensible
     */
    public function logSensitiveAccess(string $field): void
    {
        app(AuditService::class)->logSensitiveAccess(
            class_basename($this),
            $this->id,
            $field
        );
    }

    /**
     * Definir los campos sensibles que deben ser auditados
     */
    public function getSensitiveFields(): array
    {
        return $this->sensitiveFields ?? [];
    }

    /**
     * Sobrescribir el método getAttribute para auditar accesos sensibles
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->getSensitiveFields())) {
            $this->logSensitiveAccess($key);
        }

        return $value;
    }

    /**
     * Obtener los cambios en formato legible
     */
    public function getReadableChanges(array $changes): array
    {
        $readable = [];
        $attributes = $this->getAttributes();

        foreach ($changes as $field => $value) {
            // Ignorar campos que no deberían mostrarse
            if (in_array($field, ['password', 'remember_token'])) {
                continue;
            }

            // Obtener el nombre legible del campo
            $fieldName = $this->getFieldDisplayName($field);

            // Formatear el valor según el tipo de campo
            $readable[$fieldName] = $this->formatFieldValue($field, $value);
        }

        return $readable;
    }

    /**
     * Obtener el nombre legible de un campo
     */
    protected function getFieldDisplayName(string $field): string
    {
        return $this->fieldDisplayNames[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Formatear el valor de un campo según su tipo
     */
    protected function formatFieldValue(string $field, $value)
    {
        // Si el campo es una fecha
        if (in_array($field, $this->getDates())) {
            return $value ? date('d/m/Y H:i:s', strtotime($value)) : null;
        }

        // Si el campo es booleano
        if (in_array($field, $this->getBooleanFields())) {
            return $value ? 'Sí' : 'No';
        }

        // Si el campo es una relación
        if (method_exists($this, $field) && $this->{$field}() instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
            $related = $this->{$field}()->getRelated();
            return $related::find($value)?->name ?? $value;
        }

        return $value;
    }

    /**
     * Obtener los campos booleanos del modelo
     */
    protected function getBooleanFields(): array
    {
        return $this->booleanFields ?? [];
    }
}
