<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as BasePermission;

class CustomPermission extends BasePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name', // Asegúrate de que esta línea esté presente
    ];
}
