<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    protected $fillable = [
        'type',
        'level',
        'message',
        'metadata',
        'resolved',
        'resolved_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime'
    ];

    /**
     * Create a new system alert
     */
    public static function createAlert(string $level, string $type, string $message, ?array $metadata = null): self
    {
        return static::create([
            'level' => $level,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Mark the alert as resolved
     */
    public function resolve(): bool
    {
        return $this->update([
            'resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include unresolved alerts
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope a query to only include alerts of a specific level
     */
    public function scopeOfLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to only include alerts of a specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
