<?php

namespace App\Traits;

use InvalidArgumentException;

trait HasSurgeryStatus
{
    /**
     * Transiciones válidas de estado
     */
    protected static $validTransitions = [
        'pending' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
        'rescheduled' => ['pending']
    ];

    /**
     * Actualiza el estado de la cirugía validando la transición
     */
    public function updateStatus(string $newStatus): bool
    {
        if (!$this->isValidTransition($newStatus)) {
            throw new InvalidArgumentException(
                "Transición de estado inválida: {$this->status} -> {$newStatus}"
            );
        }

        // Actualizar equipos si es necesario
        if (in_array($newStatus, ['completed', 'cancelled'])) {
            $this->equipment()->update(['status' => 'available']);
        }

        $this->status = $newStatus;
        return $this->save();
    }

    /**
     * Verifica si la transición de estado es válida
     */
    protected function isValidTransition(string $newStatus): bool
    {
        if (!array_key_exists($this->status, static::$validTransitions)) {
            return false;
        }

        return in_array($newStatus, static::$validTransitions[$this->status]);
    }

    /**
     * Obtiene los estados posibles para la siguiente transición
     */
    public function getValidNextStates(): array
    {
        return static::$validTransitions[$this->status] ?? [];
    }
}
