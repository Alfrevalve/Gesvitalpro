<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class SurgicalProcess extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados del proceso quirúrgico
     */
    public const STATUS_VISIT_PENDING = 'visit_pending';
    public const STATUS_VISIT_COMPLETED = 'visit_completed';
    public const STATUS_SURGERY_PLANNED = 'surgery_planned';
    public const STATUS_MATERIAL_PREPARATION = 'material_preparation';
    public const STATUS_MATERIAL_READY = 'material_ready';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_SURGERY_IN_PROGRESS = 'surgery_in_progress';
    public const STATUS_SURGERY_COMPLETED = 'surgery_completed';
    public const STATUS_CONSUMPTION_REGISTERED = 'consumption_registered';
    public const STATUS_PICKUP_SCHEDULED = 'pickup_scheduled';
    public const STATUS_MATERIAL_RETURNED = 'material_returned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'visita_id',
        'surgery_id',
        'storage_process_id',
        'dispatch_process_id',
        'estado',
        'current_responsible_id',
        'start_date',
        'expected_completion_date',
        'completion_date',
        'cancellation_reason',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'expected_completion_date' => 'datetime',
        'completion_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relaciones
     */
    public function visita(): BelongsTo
    {
        return $this->belongsTo(Visita::class);
    }

    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }

    public function storageProcess(): BelongsTo
    {
        return $this->belongsTo(StorageProcess::class);
    }

    public function dispatchProcess(): BelongsTo
    {
        return $this->belongsTo(DispatchProcess::class);
    }

    public function currentResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_responsible_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(SurgicalProcessLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Transiciones de estado permitidas
     */
    protected $allowedTransitions = [
        self::STATUS_VISIT_PENDING => [
            self::STATUS_VISIT_COMPLETED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_VISIT_COMPLETED => [
            self::STATUS_SURGERY_PLANNED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_SURGERY_PLANNED => [
            self::STATUS_MATERIAL_PREPARATION,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_MATERIAL_PREPARATION => [
            self::STATUS_MATERIAL_READY,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_MATERIAL_READY => [
            self::STATUS_DISPATCHED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_DISPATCHED => [
            self::STATUS_SURGERY_IN_PROGRESS,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_SURGERY_IN_PROGRESS => [
            self::STATUS_SURGERY_COMPLETED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_SURGERY_COMPLETED => [
            self::STATUS_CONSUMPTION_REGISTERED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_CONSUMPTION_REGISTERED => [
            self::STATUS_PICKUP_SCHEDULED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_PICKUP_SCHEDULED => [
            self::STATUS_MATERIAL_RETURNED,
            self::STATUS_CANCELLED,
        ],
        self::STATUS_MATERIAL_RETURNED => [
            self::STATUS_COMPLETED,
        ],
    ];

    /**
     * Responsables por estado
     */
    protected $stateResponsibilities = [
        self::STATUS_VISIT_PENDING => 'vendedor',
        self::STATUS_VISIT_COMPLETED => 'vendedor',
        self::STATUS_SURGERY_PLANNED => 'coordinador_cirugias',
        self::STATUS_MATERIAL_PREPARATION => 'almacen',
        self::STATUS_MATERIAL_READY => 'almacen',
        self::STATUS_DISPATCHED => 'despacho',
        self::STATUS_SURGERY_IN_PROGRESS => 'instrumentista',
        self::STATUS_SURGERY_COMPLETED => 'instrumentista',
        self::STATUS_CONSUMPTION_REGISTERED => 'vendedor',
        self::STATUS_PICKUP_SCHEDULED => 'despacho',
        self::STATUS_MATERIAL_RETURNED => 'almacen',
        self::STATUS_COMPLETED => 'almacen',
    ];

    /**
     * Métodos de transición de estado
     */
    public function transitionTo(string $newState, User $user, array $data = []): bool
    {
        if (!$this->canTransitionTo($newState)) {
            return false;
        }

        $oldState = $this->estado;
        $this->estado = $newState;
        $this->current_responsible_id = $this->getResponsibleForState($newState);

        if ($newState === self::STATUS_COMPLETED) {
            $this->completion_date = now();
        }

        if ($this->save()) {
            $this->logStateChange($oldState, $newState, $user, $data);
            return true;
        }

        return false;
    }

    protected function canTransitionTo(string $newState): bool
    {
        return isset($this->allowedTransitions[$this->estado]) &&
               in_array($newState, $this->allowedTransitions[$this->estado]);
    }

    protected function getResponsibleForState(string $state): ?int
    {
        $role = $this->stateResponsibilities[$state] ?? null;
        if (!$role) return null;

        return User::role($role)->first()?->id;
    }

    protected function logStateChange(string $oldState, string $newState, User $user, array $data = []): void
    {
        $this->statusLogs()->create([
            'old_state' => $oldState,
            'new_state' => $newState,
            'user_id' => $user->id,
            'data' => $data,
        ]);
    }

    /**
     * Métodos de verificación de estado
     */
    public function isInState(string $state): bool
    {
        return $this->estado === $state;
    }

    public function isCompleted(): bool
    {
        return $this->estado === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->estado === self::STATUS_CANCELLED;
    }

    /**
     * Métodos de utilidad
     */
    public function getNextStates(): array
    {
        return $this->allowedTransitions[$this->estado] ?? [];
    }

    public function getCurrentResponsibleRole(): ?string
    {
        return $this->stateResponsibilities[$this->estado] ?? null;
    }

    public function getDuration(): ?int
    {
        if (!$this->completion_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->completion_date);
    }

    public function isDelayed(): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }

        return $this->expected_completion_date && $this->expected_completion_date->isPast();
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
            'completed' => $processes->where('estado', self::STATUS_COMPLETED)->count(),
            'cancelled' => $processes->where('estado', self::STATUS_CANCELLED)->count(),
            'in_progress' => $processes->whereNotIn('estado', [self::STATUS_COMPLETED, self::STATUS_CANCELLED])->count(),
            'avg_duration' => $processes->whereNotNull('completion_date')->avg('completion_date'),
            'by_state' => $processes->groupBy('estado')->map->count(),
            'delayed' => $processes->filter->isDelayed()->count(),
        ];
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->whereNotIn('estado', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeDelayed($query)
    {
        return $query->where('expected_completion_date', '<', now())
            ->whereNotIn('estado', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeByResponsible($query, User $user)
    {
        return $query->where('current_responsible_id', $user->id);
    }

    public function scopeByState($query, string $state)
    {
        return $query->where('estado', $state);
    }

    /**
     * Métodos de proceso
     */
    public function cancel(string $reason, User $user): bool
    {
        return $this->transitionTo(self::STATUS_CANCELLED, $user, [
            'cancellation_reason' => $reason
        ]);
    }

    public function updateExpectedCompletion(Carbon $date): bool
    {
        return $this->update([
            'expected_completion_date' => $date
        ]);
    }

    public function addNote(string $note, User $user): void
    {
        $this->notes = $this->notes ? $this->notes . "\n\n" . $note : $note;
        $this->save();

        $this->statusLogs()->create([
            'old_state' => $this->estado,
            'new_state' => $this->estado,
            'user_id' => $user->id,
            'data' => ['note' => $note],
        ]);
    }

    /**
     * Obtener el progreso del proceso
     */
    public function getProgress(): int
    {
        $states = array_keys($this->stateResponsibilities);
        $currentIndex = array_search($this->estado, $states);

        if ($currentIndex === false) {
            return 0;
        }

        return round(($currentIndex / (count($states) - 1)) * 100);
    }

    /**
     * Obtener el tiempo estimado restante
     */
    public function getEstimatedTimeRemaining(): ?int
    {
        if ($this->isCompleted() || $this->isCancelled() || !$this->expected_completion_date) {
            return null;
        }

        return now()->diffInDays($this->expected_completion_date, false);
    }

    /**
     * Verificar si el proceso necesita atención
     */
    public function needsAttention(): bool
    {
        return $this->isDelayed() ||
               ($this->expected_completion_date && $this->expected_completion_date->diffInDays(now()) <= 2);
    }
}
