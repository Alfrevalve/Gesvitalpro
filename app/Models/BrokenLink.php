<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokenLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'url',
        'source',
        'status',
        'is_fixed',
        'fixed_at',
        'fixed_by',
        'notes',
        'check_count',
        'last_checked_at',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'fixed_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who fixed the link.
     */
    public function fixedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fixed_by');
    }

    /**
     * Get the check history for the link.
     */
    public function checkHistory(): HasMany
    {
        return $this->hasMany(LinkCheckHistory::class);
    }

    /**
     * Scope a query to only include unfixed links.
     */
    public function scopeUnfixed($query)
    {
        return $query->where('is_fixed', false);
    }

    /**
     * Scope a query to only include links that haven't been checked recently.
     */
    public function scopeNeedsCheck($query, $hours = 24)
    {
        return $query->where('last_checked_at', '<', now()->subHours($hours));
    }

    /**
     * Mark the link as fixed.
     */
    public function markAsFixed(User $user, string $notes = null)
    {
        $this->update([
            'is_fixed' => true,
            'fixed_at' => now(),
            'fixed_by' => $user->id,
            'notes' => $notes,
        ]);
    }

    /**
     * Increment the check count.
     */
    public function incrementCheckCount()
    {
        $this->increment('check_count');
        $this->update(['last_checked_at' => now()]);
    }

    /**
     * Add a check history record.
     */
    public function addCheckHistory(string $status, ?string $responseData = null, ?string $duration = null)
    {
        return $this->checkHistory()->create([
            'status' => $status,
            'checked_at' => now(),
            'response_data' => $responseData,
            'check_duration' => $duration,
        ]);
    }

    /**
     * Check if the link should be rechecked.
     */
    public function shouldRecheck(): bool
    {
        if ($this->is_fixed) {
            return false;
        }

        return $this->last_checked_at->diffInHours(now()) >= 24;
    }

    /**
     * Get the latest check history.
     */
    public function getLatestCheck()
    {
        return $this->checkHistory()->latest('checked_at')->first();
    }

    /**
     * Get the check frequency based on the status.
     */
    public function getCheckFrequency(): int
    {
        // Retorna las horas entre cada verificación
        if ($this->check_count <= 3) {
            return 24; // Verificar diariamente para nuevos enlaces rotos
        } elseif ($this->check_count <= 7) {
            return 72; // Cada 3 días para enlaces verificados varias veces
        } else {
            return 168; // Semanalmente para enlaces verificados muchas veces
        }
    }
}
