<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'action',
        'loggable_type',
        'loggable_id',
        'user_id',
        'changes',
        'original',
        'ip_address',
        'user_agent',
        'event',
        'properties',
        'old_values',
        'new_values'
    ];

    protected $casts = [
        'changes' => 'array',
        'original' => 'array',
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
