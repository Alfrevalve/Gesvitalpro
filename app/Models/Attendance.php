<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model  
{
    use HasFactory;

    protected $fillable = [
        'date',
        'user_id',
        'status',
        'check_in_time',
        'check_out_time',
        'notes',
        // Add other relevant fields as necessary
    ];

    // Define relationships if applicable
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
