<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSurgeryStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Surgery extends Model
{
    use HasFactory, SoftDeletes, HasSurgeryStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'line_id',
        'institucion_id',
        'medico_id',
        'patient_name',
        'surgery_type',
        'surgery_date',
        'admission_date',
        'description',
        'notes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'surgery_date' => 'datetime',
        'admission_date' => 'datetime',
    ];

    /**
     * Validation rules for the model.
     *
     * @var array<string, string>
     */
    public static $rules = [
        'line_id' => 'required|exists:lines,id',
        'institucion_id' => 'required|exists:instituciones,id',
        'medico_id' => 'required|exists:medicos,id',
        'patient_name' => 'required|string|max:255',
        'surgery_type' => 'required|string|max:100',
        'surgery_date' => 'required|date',
        'admission_date' => 'required|date|before_or_equal:surgery_date',
        'description' => 'nullable|string',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,completed,cancelled,rescheduled',
    ];

    /**
     * The possible statuses for a surgery.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RESCHEDULED = 'rescheduled';

    /**
     * Get all possible surgery statuses.
     *
     * @return array<string>
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_RESCHEDULED,
        ];
    }

    /**
     * La línea a la que pertenece la cirugía
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    /**
     * La institución donde se realiza la cirugía
     */
    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    /**
     * El médico que realiza la cirugía
     */
    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    /**
     * Los equipos asignados a la cirugía
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'surgery_equipment')
            ->withTimestamps();
    }

    /**
     * El personal asignado a la cirugía
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'surgery_staff')
            ->withTimestamps();
    }

    /**
     * Get the surgery materials for this surgery.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(SurgeryMaterial::class);
    }

    /**
     * Get the surgery requests for this surgery.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(SurgeryRequest::class);
    }

    /**
     * Scope a query to only include surgeries within a date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('surgery_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    /**
     * Scope a query to only include upcoming surgeries.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('surgery_date', '>=', Carbon::now())
            ->where('surgery_date', '<=', Carbon::now()->addDays($days))
            ->orderBy('surgery_date');
    }

    /**
     * Scope a query to only include surgeries by institution.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $institucionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByInstitucion($query, int $institucionId)
    {
        return $query->where('institucion_id', $institucionId);
    }

    /**
     * Get all required equipment for this surgery.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRequiredEquipment(): Collection
    {
        return $this->equipment()
            ->with(['line:id,name'])
            ->get(['id', 'name', 'line_id', 'status']);
    }

    /**
     * Check if all required equipment is available.
     *
     * @return bool
     */
    public function hasAllRequiredEquipment(): bool
    {
        return $this->equipment()
            ->where('status', '!=', 'available')
            ->doesntExist();
    }

    /**
     * Get the duration of the surgery in minutes.
     *
     * @return int|null
     */
    public function getDurationInMinutes(): ?int
    {
        if ($this->isCompleted() && $this->started_at && $this->completed_at) {
            return Carbon::parse($this->completed_at)
                ->diffInMinutes(Carbon::parse($this->started_at));
        }

        return null;
    }

    /**
     * Check if the surgery can be started.
     *
     * @return bool
     */
    public function canBeStarted(): bool
    {
        return $this->isPending() &&
            $this->hasAllRequiredEquipment() &&
            $this->staff()->exists() &&
            Carbon::parse($this->surgery_date)->isToday();
    }

    /**
     * Start the surgery.
     *
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->canBeStarted()) {
            return false;
        }

        $this->status = self::STATUS_IN_PROGRESS;
        $this->started_at = now();
        return $this->save();
    }

    /**
     * Complete the surgery.
     *
     * @param string|null $notes
     * @return bool
     */
    public function complete(?string $notes = null): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        return $this->save();
    }

    /**
     * Cancel the surgery.
     *
     * @param string $reason
     * @return bool
     */
    public function cancel(string $reason): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->notes = $reason;
        return $this->save();
    }

    /**
     * Reschedule the surgery.
     *
     * @param Carbon $newDate
     * @param string|null $reason
     * @return bool
     */
    public function reschedule(Carbon $newDate, ?string $reason = null): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }

        $this->status = self::STATUS_RESCHEDULED;
        $this->surgery_date = $newDate;
        if ($reason) {
            $this->notes = $reason;
        }
        return $this->save();
    }

    /**
     * Check if the surgery can be edited.
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return !in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ]);
    }
}
