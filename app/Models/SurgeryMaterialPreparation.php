<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class SurgeryMaterialPreparation extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'surgery_request_id',
        'prepared_by',
        'notes',
        'started_at',
        'completed_at',
        'status',
        'priority'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'surgery_request_id' => 'required|exists:surgery_requests,id',
        'prepared_by' => 'required|exists:users,id',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,completed',
        'priority' => 'required|in:low,medium,high'
    ];

    /**
     * Get the surgery request associated with the preparation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function surgeryRequest(): BelongsTo
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    /**
     * Get the user who prepared the materials.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Check if the preparation is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_at) && $this->status === 'completed';
    }

    /**
     * Check if the preparation is in progress.
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the preparation is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the preparation is high priority.
     *
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return $this->priority === 'high';
    }

    /**
     * Scope a query to only include completed preparations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at')->where('status', 'completed');
    }

    /**
     * Scope a query to only include in progress preparations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include pending preparations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include high priority preparations.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 'high');
    }

    /**
     * Start the preparation process.
     *
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = 'in_progress';
        $this->started_at = now();
        return $this->save();
    }

    /**
     * Complete the preparation process.
     *
     * @param string|null $notes
     * @return bool
     */
    public function complete(?string $notes = null): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $this->status = 'completed';
        $this->completed_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Get the preparation duration in minutes.
     *
     * @return int|null
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->isCompleted() && $this->started_at) {
            return Carbon::parse($this->completed_at)
                ->diffInMinutes($this->started_at);
        }

        return null;
    }

    /**
     * Get the status text for display.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Proceso',
            'completed' => 'Completado',
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
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get the priority text for display.
     *
     * @return string
     */
    public function getPriorityTextAttribute(): string
    {
        return match($this->priority) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            default => 'Desconocida'
        };
    }

    /**
     * Get the priority color for display.
     *
     * @return string
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            default => 'secondary'
        };
    }
}
