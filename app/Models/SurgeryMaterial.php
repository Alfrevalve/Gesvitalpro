<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class SurgeryMaterial extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The possible statuses for surgery materials.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREPARED = 'prepared';
    public const STATUS_DISPATCHED = 'dispatched';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'surgery_id',
        'material_name',
        'quantity',
        'status',
        'notes',
        'prepared_at',
        'dispatched_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'prepared_at' => 'datetime',
        'dispatched_at' => 'datetime',
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
        'surgery_id' => 'required|exists:surgeries,id',
        'material_name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'status' => 'required|in:pending,prepared,dispatched',
        'notes' => 'nullable|string',
    ];

    /**
     * Get the surgery that owns the material.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }

    /**
     * Check if the material is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the material is prepared.
     *
     * @return bool
     */
    public function isPrepared(): bool
    {
        return $this->status === self::STATUS_PREPARED;
    }

    /**
     * Check if the material is dispatched.
     *
     * @return bool
     */
    public function isDispatched(): bool
    {
        return $this->status === self::STATUS_DISPATCHED;
    }

    /**
     * Scope a query to only include pending materials.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include prepared materials.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrepared(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PREPARED);
    }

    /**
     * Scope a query to only include dispatched materials.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDispatched(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DISPATCHED);
    }

    /**
     * Mark the material as prepared.
     *
     * @param string|null $notes
     * @return bool
     */
    public function markAsPrepared(?string $notes = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = self::STATUS_PREPARED;
        $this->prepared_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Mark the material as dispatched.
     *
     * @param string|null $notes
     * @return bool
     */
    public function markAsDispatched(?string $notes = null): bool
    {
        if (!$this->isPrepared()) {
            return false;
        }

        $this->status = self::STATUS_DISPATCHED;
        $this->dispatched_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
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
            self::STATUS_PREPARED => 'Preparado',
            self::STATUS_DISPATCHED => 'Despachado',
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
            self::STATUS_PREPARED => 'info',
            self::STATUS_DISPATCHED => 'success',
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
            self::STATUS_PREPARED,
            self::STATUS_DISPATCHED,
        ];
    }
}
