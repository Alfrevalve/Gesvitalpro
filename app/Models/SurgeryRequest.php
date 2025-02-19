<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class SurgeryRequest extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The possible statuses for surgery requests.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'surgery_id',
        'status',
        'notes',
        'priority',
        'requested_by',
        'completed_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'surgery_id' => 'required|exists:surgeries,id',
        'status' => 'required|in:pending,in_progress,completed,cancelled',
        'notes' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'requested_by' => 'required|exists:users,id',
    ];

    /**
     * Get the surgery that owns the request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }

    /**
     * Get the items for the request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(SurgeryRequestItem::class);
    }

    /**
     * Get the preparation for the request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function preparation(): HasOne
    {
        return $this->hasOne(SurgeryMaterialPreparation::class);
    }

    /**
     * Get the user who requested the materials.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Check if the request is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the request is in progress.
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if the request is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the request is cancelled.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if the request is high priority.
     *
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return $this->priority === 'high';
    }

    /**
     * Scope a query to only include pending requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include in progress requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope a query to only include completed requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include high priority requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 'high');
    }

    /**
     * Start processing the request.
     *
     * @param string|null $notes
     * @return bool
     */
    public function startProcessing(?string $notes = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_IN_PROGRESS;
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Complete the request.
     *
     * @param string|null $notes
     * @return bool
     */
    public function complete(?string $notes = null): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Cancel the request.
     *
     * @param string $reason
     * @return bool
     */
    public function cancel(string $reason): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->notes = $reason;
        return $this->save();
    }

    /**
     * Get the status text for display.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_IN_PROGRESS => 'En Proceso',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconocido'
        };
    }

    /**
     * Get the status color for display.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get all possible statuses.
     *
     * @return array<string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get the total number of items in the request.
     *
     * @return int
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Get the processing time in minutes.
     *
     * @return int|null
     */
    public function getProcessingTimeAttribute(): ?int
    {
        if ($this->isCompleted() && $this->completed_at) {
            return Carbon::parse($this->completed_at)
                ->diffInMinutes($this->created_at);
        }

        return null;
    }
}
