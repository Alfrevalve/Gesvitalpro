<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class SurgeryRequestItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'surgery_request_id',
        'name',
        'quantity',
        'prepared',
        'notes',
        'prepared_at',
        'prepared_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prepared' => 'boolean',
        'quantity' => 'integer',
        'prepared_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'surgery_request_id' => 'required|exists:surgery_requests,id',
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'prepared' => 'boolean',
        'notes' => 'nullable|string',
        'prepared_by' => 'nullable|exists:users,id'
    ];

    /**
     * Get the surgery request that owns the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function surgeryRequest(): BelongsTo
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    /**
     * Get the user who prepared the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Scope a query to only include prepared items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrepared(Builder $query): Builder
    {
        return $query->where('prepared', true);
    }

    /**
     * Scope a query to only include pending items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('prepared', false);
    }

    /**
     * Mark the item as prepared.
     *
     * @param int $userId
     * @param string|null $notes
     * @return bool
     */
    public function markAsPrepared(int $userId, ?string $notes = null): bool
    {
        $this->prepared = true;
        $this->prepared_at = now();
        $this->prepared_by = $userId;
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Mark the item as pending.
     *
     * @param string|null $notes
     * @return bool
     */
    public function markAsPending(?string $notes = null): bool
    {
        $this->prepared = false;
        $this->prepared_at = null;
        $this->prepared_by = null;
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Get the preparation time in minutes.
     *
     * @return int|null
     */
    public function getPreparationTimeAttribute(): ?int
    {
        if ($this->prepared && $this->prepared_at) {
            return Carbon::parse($this->prepared_at)
                ->diffInMinutes($this->created_at);
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
        return $this->prepared ? 'Preparado' : 'Pendiente';
    }

    /**
     * Get the status color for display.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return $this->prepared ? 'success' : 'warning';
    }

    /**
     * Check if the item can be prepared.
     *
     * @return bool
     */
    public function canBePrepared(): bool
    {
        return !$this->prepared &&
            $this->surgeryRequest &&
            $this->surgeryRequest->isInProgress();
    }

    /**
     * Get the formatted quantity with unit.
     *
     * @return string
     */
    public function getFormattedQuantityAttribute(): string
    {
        return "{$this->quantity} unidad" . ($this->quantity > 1 ? 'es' : '');
    }
}
