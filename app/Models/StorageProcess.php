<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StorageProcess extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados del proceso
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Niveles de prioridad
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'surgery_request_id',
        'status',
        'priority',
        'notes',
        'prepared_by',
        'started_at',
        'completed_at',
        'preparation_time',
        'quality_check_passed',
        'quality_check_notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'quality_check_passed' => 'boolean',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'surgery_request_id' => 'required|exists:surgery_requests,id',
        'status' => 'required|in:pending,in_progress,completed,cancelled',
        'priority' => 'required|in:low,medium,high',
        'notes' => 'nullable|string',
        'prepared_by' => 'nullable|exists:users,id',
        'started_at' => 'nullable|date',
        'completed_at' => 'nullable|date|after:started_at',
        'preparation_time' => 'nullable|integer|min:0',
        'quality_check_passed' => 'nullable|boolean',
        'quality_check_notes' => 'nullable|string',
    ];

    /**
     * Relaciones
     */
    public function surgeryRequest(): BelongsTo
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Verificadores de estado
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_HIGH;
    }

    /**
     * Métodos de proceso
     */
    public function start(User $user): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'prepared_by' => $user->id,
            'started_at' => now(),
        ]);
    }

    public function complete(array $details = []): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $now = now();
        $preparationTime = $this->started_at
            ? $now->diffInMinutes($this->started_at)
            : null;

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => $now,
            'preparation_time' => $preparationTime,
            'quality_check_passed' => $details['quality_check_passed'] ?? true,
            'quality_check_notes' => $details['quality_check_notes'] ?? null,
            'notes' => $details['notes'] ?? $this->notes,
        ]);
    }

    public function cancel(string $reason): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason,
        ]);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Métodos de estadísticas
     */
    public static function getStats(Carbon $start = null, Carbon $end = null): array
    {
        $query = static::query();

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $processes = $query->get();

        return [
            'total' => $processes->count(),
            'completed' => $processes->where('status', self::STATUS_COMPLETED)->count(),
            'pending' => $processes->where('status', self::STATUS_PENDING)->count(),
            'in_progress' => $processes->where('status', self::STATUS_IN_PROGRESS)->count(),
            'cancelled' => $processes->where('status', self::STATUS_CANCELLED)->count(),
            'avg_preparation_time' => $processes->whereNotNull('preparation_time')->avg('preparation_time'),
            'quality_check_passed' => $processes->where('quality_check_passed', true)->count(),
            'quality_check_failed' => $processes->where('quality_check_passed', false)->count(),
            'by_priority' => [
                'high' => $processes->where('priority', self::PRIORITY_HIGH)->count(),
                'medium' => $processes->where('priority', self::PRIORITY_MEDIUM)->count(),
                'low' => $processes->where('priority', self::PRIORITY_LOW)->count(),
            ],
        ];
    }

    /**
     * Obtener tiempo estimado de preparación
     */
    public function getEstimatedPreparationTime(): ?int
    {
        if ($this->isCompleted() && $this->preparation_time) {
            return $this->preparation_time;
        }

        // Calcula basado en histórico de solicitudes similares
        return static::completed()
            ->whereHas('surgeryRequest', function ($query) {
                $query->where('surgery_type', $this->surgeryRequest->surgery_type);
            })
            ->whereNotNull('preparation_time')
            ->avg('preparation_time');
    }

    /**
     * Verificar si el proceso está retrasado
     */
    public function isDelayed(): bool
    {
        if (!$this->started_at || $this->isCompleted()) {
            return false;
        }

        $estimatedTime = $this->getEstimatedPreparationTime() ?? 120; // 2 horas por defecto
        return now()->diffInMinutes($this->started_at) > $estimatedTime;
    }

    /**
     * Obtener el progreso del proceso
     */
    public function getProgress(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if ($this->isPending()) {
            return 0;
        }

        if (!$this->started_at) {
            return 0;
        }

        $estimatedTime = $this->getEstimatedPreparationTime() ?? 120;
        $elapsed = now()->diffInMinutes($this->started_at);

        return min(round(($elapsed / $estimatedTime) * 100), 99);
    }
}
