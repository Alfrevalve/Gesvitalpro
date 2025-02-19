<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'line_id',
        'serial_number'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function surgeries()
    {
        return $this->belongsToMany(Surgery::class, 'surgery_equipment')
            ->withTimestamps();
    }

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isInUse()
    {
        return $this->status === 'in_use';
    }

    public function isUnderMaintenance()
    {
        return $this->status === 'maintenance';
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'available' => 'success',
            'in_use' => 'warning',
            'maintenance' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'available' => 'Disponible',
            'in_use' => 'En Uso',
            'maintenance' => 'En Mantenimiento',
            default => 'Desconocido',
        };
    }
}
