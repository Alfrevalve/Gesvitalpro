<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkCheckHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'broken_link_id',
        'status',
        'checked_at',
        'response_data',
        'check_duration',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'response_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the broken link that owns the check history.
     */
    public function brokenLink(): BelongsTo
    {
        return $this->belongsTo(BrokenLink::class);
    }

    /**
     * Scope a query to only include recent checks.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('checked_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to only include failed checks.
     */
    public function scopeFailed($query)
    {
        return $query->whereNotIn('status', ['200', '201', '301', '302', '307', '308']);
    }

    /**
     * Get the formatted check duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->check_duration) {
            return 'N/A';
        }

        $duration = (float) $this->check_duration;
        
        if ($duration < 1) {
            return round($duration * 1000) . 'ms';
        }
        
        return $duration . 's';
    }

    /**
     * Get the status description.
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match ($this->status) {
            '200' => 'OK',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '307' => 'Temporary Redirect',
            '308' => 'Permanent Redirect',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '500' => 'Internal Server Error',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
            default => 'Unknown Status',
        };
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        $status = (int) $this->status;
        
        return match (true) {
            $status >= 200 && $status < 300 => 'green',
            $status >= 300 && $status < 400 => 'yellow',
            $status >= 400 && $status < 500 => 'orange',
            $status >= 500 => 'red',
            default => 'gray',
        };
    }

    /**
     * Determine if the check was successful.
     */
    public function wasSuccessful(): bool
    {
        $status = (int) $this->status;
        return $status >= 200 && $status < 400;
    }

    /**
     * Get a summary of the check result.
     */
    public function getSummary(): array
    {
        return [
            'status' => $this->status,
            'description' => $this->status_description,
            'duration' => $this->formatted_duration,
            'checked_at' => $this->checked_at->diffForHumans(),
            'successful' => $this->wasSuccessful(),
            'color' => $this->status_color,
        ];
    }
}
