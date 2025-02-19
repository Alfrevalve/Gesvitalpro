<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgeryMaterialDelivery extends Model
{
    protected $fillable = [
        'surgery_request_id',
        'delivered_by',
        'delivered_at',
        'notes'
    ];

    protected $dates = [
        'delivered_at'
    ];

    public function surgeryRequest(): BelongsTo
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    public function deliveredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function getDeliveryTimeAttribute()
    {
        $preparation = $this->surgeryRequest->storageProcess;
        if ($preparation) {
            return $this->delivered_at->diffInMinutes($preparation->prepared_at);
        }
        return null;
    }

    public function scopeByDeliverer($query, $userId)
    {
        return $query->where('delivered_by', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('delivered_at', today());
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('delivered_at', [$startDate, $endDate]);
    }
}
