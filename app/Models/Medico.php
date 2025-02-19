<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Medico extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Validation rules for the model.
     *
     * @var array
     */
    public static $rules = [
        'nombre' => 'required|string|max:255',
        'especialidad' => 'required|string|max:100',
        'email' => 'required|email|unique:medicos,email',
        'telefono' => 'required|string|max:20',
        'estado' => 'required|in:active,inactive',
        'institucion_id' => 'required|exists:instituciones,id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'especialidad',
        'email',
        'telefono',
        'estado',
        'institucion_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get all visits associated with the doctor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    /**
     * Get all surgeries associated with the doctor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surgeries(): HasMany
    {
        return $this->hasMany(Surgery::class, 'medico_id');
    }

    /**
     * Get the institution associated with the doctor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * Scope a query to only include active doctors.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('estado', 'active');
    }

    /**
     * Scope a query to only include doctors with pending surgeries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPendingSurgeries($query)
    {
        return $query->whereHas('surgeries', function($q) {
            $q->where('status', 'pending');
        });
    }

    /**
     * Get the total number of surgeries performed by the doctor.
     *
     * @return int
     */
    public function getTotalSurgeries(): int
    {
        return $this->surgeries()->count();
    }

    /**
     * Get the number of surgeries performed this month.
     *
     * @return int
     */
    public function getSurgeriesThisMonth(): int
    {
        return $this->surgeries()
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();
    }

    /**
     * Get surgeries grouped by institution with eager loading optimization.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInstitutionSurgeries()
    {
        return $this->surgeries()
            ->select('institucion_id', \DB::raw('count(*) as total'))
            ->groupBy('institucion_id')
            ->with(['institucion' => function($query) {
                $query->select('id', 'nombre');
            }])
            ->get();
    }

    /**
     * Get the success rate of surgeries.
     *
     * @return float
     */
    public function getSurgerySuccessRate(): float
    {
        $total = $this->surgeries()->count();
        if ($total === 0) return 0;

        $successful = $this->surgeries()
            ->where('status', 'completed')
            ->count();

        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get upcoming surgeries for the doctor.
     *
     * @param int $days Number of days to look ahead
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingSurgeries(int $days = 7)
    {
        return $this->surgeries()
            ->where('fecha', '>=', Carbon::now())
            ->where('fecha', '<=', Carbon::now()->addDays($days))
            ->orderBy('fecha')
            ->with(['institucion:id,nombre', 'equipment:id,name'])
            ->get();
    }

    /**
     * Check if the doctor is available on a specific date.
     *
     * @param \Carbon\Carbon $date
     * @return bool
     */
    public function isAvailableOn(Carbon $date): bool
    {
        return !$this->surgeries()
            ->whereDate('fecha', $date)
            ->exists();
    }
}
