<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispatchProcess extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'surgery_request_id',
        'status',
        'priority',
        'notes',
        'delivered_by',
        'recipient_name',
        'recipient_signature',
        'delivery_photo',
        'delivered_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function surgeryRequest()
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isUrgent()
    {
        return $this->priority === 'high';
    }

    public function getDeliveryPhotoUrlAttribute()
    {
        return $this->delivery_photo ? asset('storage/' . $this->delivery_photo) : null;
    }
}
