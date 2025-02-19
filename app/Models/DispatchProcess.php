<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class DispatchProcess extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Estados del proceso
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Niveles de prioridad
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';

    /**
     * Tipos de entrega
     */
    public const DELIVERY_TYPE_PICKUP = 'pickup';
    public const DELIVERY_TYPE_DELIVERY = 'delivery';

    protected $fillable = [
        'surgery_request_id',
        'status',
        'priority',
        'notes',
        'delivered_by',
        'recipient_name',
        'recipient_signature',
        'delivery_photo',
        'delivered_at',
        'started_at',
        'delivery_type',
        'delivery_address',
        'delivery_time',
        'confirmation_code',
        'temperature_check',
        'package_condition'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'temperature_check' => 'float',
    ];

    /**
     * Reglas de validación
     */
    public static $rules = [
        'surgery_request_id' => 'required|exists:surgery_requests,id',
        'status' => 'required|in:pending,in_progress,completed,cancelled',
        'priority' => 'required|in:low,medium,high',
        'notes' => 'nullable|string',
        'delivered_by' => 'nullable|exists:users,id',
        'recipient_name' => 'required_if:status,completed|string|max:255',
        'recipient_signature' => 'required_if:status,completed|string',
        'delivery_photo' => 'nullable|string',
        'delivery_type' => 'required|in:pickup,delivery',
        'delivery_address' => 'required_if:delivery_type,delivery|string',
        'confirmation_code' => 'required|string|max:10',
        'temperature_check' => 'nullable|numeric|between:-50,50',
        'package_condition' => 'required|string|in:excellent,good,damaged',
    ];

    /**
     * Relaciones
     */
    public function surgeryRequest(): BelongsTo
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    /**
     * Verificadores de estado
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUrgent(): bool
    {
        return $this->priority === self::PRIORITY_HIGH;
    }

    public function isPickup(): bool
    {
        return $this->delivery_type === self::DELIVERY_TYPE_PICKUP;
    }

    /**
     * Métodos de proceso
     */
    public function start(User $user): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->confirmation_code = $this->generateConfirmationCode();

        return $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'delivered_by' => $user->id,
            'started_at' => now(),
        ]);
    }

    public function complete(array $details): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        if (!$this->validateDeliveryDetails($details)) {
            return false;
        }

        $now = now();
        $deliveryTime = $this->started_at
            ? $now->diffInMinutes($this->started_at)
            : null;

        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'delivered_at' => $now,
            'delivery_time' => $deliveryTime,
            'recipient_name' => $details['recipient_name'],
            'recipient_signature' => $details['recipient_signature'],
            'delivery_photo' => $details['delivery_photo'] ?? null,
            'temperature_check' => $details['temperature_check'] ?? null,
            'package_condition' => $details['package_condition'],
            'notes' => $details['notes'] ?? $this->notes,
        ]);
    }

    public function cancel(string $reason): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason,
        ]);
    }

    /**
     * Métodos de utilidad
     */
    private function generateConfirmationCode(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 6));
    }

    private function validateDeliveryDetails(array $details): bool
    {
        return !empty($details['recipient_name']) &&
               !empty($details['recipient_signature']) &&
               !empty($details['package_condition']);
    }

    public function getDeliveryPhotoUrlAttribute(): ?string
    {
        return $this->delivery_photo ? Storage::url($this->delivery_photo) : null;
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    public function scopeByDeliveryType($query, string $type)
    {
        return $query->where('delivery_type', $type);
    }

    /**
     * Métodos de estadísticas
     */
    public static function getStats(Carbon $start = null, Carbon $end = null): array
    {
        $query = static::query();

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $processes = $query->get();

        return [
            'total' => $processes->count(),
            'completed' => $processes->where('status', self::STATUS_COMPLETED)->count(),
            'pending' => $processes->where('status', self::STATUS_PENDING)->count(),
            'in_progress' => $processes->where('status', self::STATUS_IN_PROGRESS)->count(),
            'cancelled' => $processes->where('status', self::STATUS_CANCELLED)->count(),
            'avg_delivery_time' => $processes->whereNotNull('delivery_time')->avg('delivery_time'),
            'by_priority' => [
                'high' => $processes->where('priority', self::PRIORITY_HIGH)->count(),
                'medium' => $processes->where('priority', self::PRIORITY_MEDIUM)->count(),
                'low' => $processes->where('priority', self::PRIORITY_LOW)->count(),
            ],
            'by_delivery_type' => [
                'pickup' => $processes->where('delivery_type', self::DELIVERY_TYPE_PICKUP)->count(),
                'delivery' => $processes->where('delivery_type', self::DELIVERY_TYPE_DELIVERY)->count(),
            ],
            'package_conditions' => [
                'excellent' => $processes->where('package_condition', 'excellent')->count(),
                'good' => $processes->where('package_condition', 'good')->count(),
                'damaged' => $processes->where('package_condition', 'damaged')->count(),
            ],
        ];
    }

    /**
     * Obtener tiempo estimado de entrega
     */
    public function getEstimatedDeliveryTime(): ?int
    {
        if ($this->isCompleted() && $this->delivery_time) {
            return $this->delivery_time;
        }

        return static::completed()
            ->where('delivery_type', $this->delivery_type)
            ->whereNotNull('delivery_time')
            ->avg('delivery_time');
    }

    /**
     * Verificar si el proceso está retrasado
     */
    public function isDelayed(): bool
    {
        if (!$this->started_at || $this->isCompleted()) {
            return false;
        }

        $estimatedTime = $this->getEstimatedDeliveryTime() ?? 180; // 3 horas por defecto
        return now()->diffInMinutes($this->started_at) > $estimatedTime;
    }

    /**
     * Obtener el progreso del proceso
     */
    public function getProgress(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if ($this->isPending()) {
            return 0;
        }

        if (!$this->started_at) {
            return 0;
        }

        $estimatedTime = $this->getEstimatedDeliveryTime() ?? 180;
        $elapsed = now()->diffInMinutes($this->started_at);

        return min(round(($elapsed / $estimatedTime) * 100), 99);
    }

    /**
     * Verificar si la firma coincide con el código de confirmación
     */
    public function validateConfirmationCode(string $code): bool
    {
        return $this->confirmation_code === strtoupper($code);
    }
}
