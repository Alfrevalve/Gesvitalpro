<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'settings',
        'date_of_birth',
        'gender',
        'contact_info',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'settings' => 'array',
        'date_of_birth' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Log user creation
            SystemAlert::createAlert(
                'info',
                'user_created',
                "User created: {$user->name}",
                ['user_id' => $user->id]
            );
        });

        static::updated(function ($user) {
            // Log significant changes
            if ($user->isDirty(['email', 'name'])) {
                SystemAlert::createAlert(
                    'info',
                    'user_updated',
                    "User updated: {$user->name}",
                    [
                        'user_id' => $user->id,
                        'changes' => $user->getDirty()
                    ]
                );
            }
        });
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /**
     * Check if the user has completed their profile.
     */
    public function hasCompleteProfile(): bool
    {
        return !empty($this->date_of_birth) && 
               !empty($this->gender) && 
               !empty($this->contact_info);
    }

    /**
     * Get the user's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }
}
