<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class SurgicalNotification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Tipos de notificación
     */
    public const TYPE_PROCESS_START = 'process_start';
    public const TYPE_STAGE_READY = 'stage_ready';
    public const TYPE_STAGE_COMPLETE = 'stage_complete';
    public const TYPE_DELAY_WARNING = 'delay_warning';
    public const TYPE_URGENT_ACTION = 'urgent_action';
    public const TYPE_QUALITY_ISSUE = 'quality_issue';
    public const TYPE_SCHEDULE_CHANGE = 'schedule_change';
    public const TYPE_MATERIAL_READY = 'material_ready';
    public const TYPE_EQUIPMENT_ISSUE = 'equipment_issue';

    /**
     * Niveles de prioridad
     */
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    /**
     * Canales de notificación
     */
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_PUSH = 'push';
    public const CHANNEL_DASHBOARD = 'dashboard';
    public const CHANNEL_WHATSAPP = 'whatsapp';

    protected $fillable = [
        'user_id',
        'surgical_process_id',
        'type',
        'title',
        'message',
        'priority',
        'channels',
        'data',
        'read_at',
        'actioned_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'channels' => 'array',
        'read_at' => 'datetime',
        'actioned_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function surgicalProcess(): BelongsTo
    {
        return $this->belongsTo(SurgicalProcess::class);
    }

    /**
     * Notificaciones por etapa del proceso
     */
    public static function notifyStageStart(SurgicalProcess $process, User $user): self
    {
        $stageInfo = self::getStageInfo($process->estado);

        return self::create([
            'user_id' => $user->id,
            'surgical_process_id' => $process->id,
            'type' => self::TYPE_STAGE_READY,
            'title' => "Inicio de {$stageInfo['name']}",
            'message' => "Es tu turno de iniciar {$stageInfo['name']} para el proceso quirúrgico #{$process->id}",
            'priority' => $stageInfo['priority'],
            'channels' => self::getUserPreferredChannels($user),
            'data' => [
                'stage' => $process->estado,
                'expected_duration' => $stageInfo['expected_duration'],
                'checklist' => $stageInfo['checklist'],
                'previous_stage' => $process->statusLogs->last()?->old_state,
            ],
            'expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Notificaciones de retrasos y urgencias
     */
    public static function notifyDelay(SurgicalProcess $process): self
    {
        $responsible = $process->currentResponsible;
        $stageInfo = self::getStageInfo($process->estado);

        return self::create([
            'user_id' => $responsible->id,
            'surgical_process_id' => $process->id,
            'type' => self::TYPE_DELAY_WARNING,
            'title' => "Retraso en {$stageInfo['name']}",
            'message' => "El proceso #{$process->id} está retrasado en la etapa de {$stageInfo['name']}",
            'priority' => self::PRIORITY_HIGH,
            'channels' => array_merge(
                self::getUserPreferredChannels($responsible),
                [self::CHANNEL_SMS] // Forzar SMS para retrasos
            ),
            'data' => [
                'delay_time' => $process->getDelayTime(),
                'impact' => self::calculateDelayImpact($process),
                'suggested_actions' => self::getSuggestedActions($process),
            ],
            'expires_at' => now()->addHours(4),
        ]);
    }

    /**
     * Notificaciones de calidad
     */
    public static function notifyQualityIssue(
        SurgicalProcess $process,
        string $issue,
        array $details
    ): self {
        $responsible = $process->currentResponsible;
        $supervisor = User::role('supervisor')->first();

        return self::create([
            'user_id' => $supervisor->id,
            'surgical_process_id' => $process->id,
            'type' => self::TYPE_QUALITY_ISSUE,
            'title' => "Problema de Calidad Detectado",
            'message' => "Se ha detectado un problema de calidad en el proceso #{$process->id}: {$issue}",
            'priority' => self::PRIORITY_HIGH,
            'channels' => [
                self::CHANNEL_EMAIL,
                self::CHANNEL_DASHBOARD,
                self::CHANNEL_SMS
            ],
            'data' => [
                'issue_type' => $issue,
                'details' => $details,
                'reported_by' => $responsible->name,
                'stage' => $process->estado,
                'impact_assessment' => self::assessQualityImpact($details),
            ],
            'expires_at' => now()->addDays(2),
        ]);
    }

    /**
     * Notificaciones de equipamiento
     */
    public static function notifyEquipmentIssue(
        Equipment $equipment,
        string $issue,
        SurgicalProcess $process = null
    ): self {
        $maintainer = User::role('maintenance')->first();

        return self::create([
            'user_id' => $maintainer->id,
            'surgical_process_id' => $process?->id,
            'type' => self::TYPE_EQUIPMENT_ISSUE,
            'title' => "Problema con Equipamiento",
            'message' => "Se ha reportado un problema con el equipo {$equipment->name}: {$issue}",
            'priority' => self::PRIORITY_HIGH,
            'channels' => [
                self::CHANNEL_EMAIL,
                self::CHANNEL_DASHBOARD,
                self::CHANNEL_SMS
            ],
            'data' => [
                'equipment_id' => $equipment->id,
                'equipment_type' => $equipment->type,
                'issue_description' => $issue,
                'last_maintenance' => $equipment->last_maintenance,
                'impact' => self::assessEquipmentImpact($equipment, $process),
            ],
            'expires_at' => now()->addDay(),
        ]);
    }

    /**
     * Notificaciones de programación
     */
    public static function notifyScheduleChange(
        SurgicalProcess $process,
        string $reason,
        Carbon $newDate
    ): Collection {
        $notifications = collect();
        $affectedUsers = self::getAffectedUsers($process);

        foreach ($affectedUsers as $user) {
            $notifications->push(self::create([
                'user_id' => $user->id,
                'surgical_process_id' => $process->id,
                'type' => self::TYPE_SCHEDULE_CHANGE,
                'title' => "Cambio en Programación",
                'message' => "La programación del proceso #{$process->id} ha sido modificada",
                'priority' => self::PRIORITY_MEDIUM,
                'channels' => self::getUserPreferredChannels($user),
                'data' => [
                    'old_date' => $process->expected_completion_date,
                    'new_date' => $newDate,
                    'reason' => $reason,
                    'impact' => self::assessScheduleChangeImpact($process, $newDate),
                ],
                'expires_at' => now()->addDays(2),
            ]));
        }

        return $notifications;
    }

    /**
     * Métodos de utilidad
     */
    protected static function getStageInfo(string $stage): array
    {
        return [
            SurgicalProcess::STATUS_VISIT_PENDING => [
                'name' => 'Visita Inicial',
                'priority' => self::PRIORITY_MEDIUM,
                'expected_duration' => 60, // minutos
                'checklist' => [
                    'Confirmar disponibilidad del médico',
                    'Preparar documentación necesaria',
                    'Verificar datos del paciente',
                ],
            ],
            SurgicalProcess::STATUS_MATERIAL_PREPARATION => [
                'name' => 'Preparación de Material',
                'priority' => self::PRIORITY_HIGH,
                'expected_duration' => 120,
                'checklist' => [
                    'Verificar inventario',
                    'Preparar kit quirúrgico',
                    'Realizar control de calidad',
                ],
            ],
            // ... más etapas
        ][$stage] ?? [
            'name' => 'Etapa Desconocida',
            'priority' => self::PRIORITY_MEDIUM,
            'expected_duration' => 60,
            'checklist' => [],
        ];
    }

    protected static function getUserPreferredChannels(User $user): array
    {
        $preferences = $user->notification_preferences ?? [];

        return array_merge(
            [self::CHANNEL_DASHBOARD], // Canal por defecto
            $preferences
        );
    }

    protected static function calculateDelayImpact(SurgicalProcess $process): array
    {
        $delayTime = $process->getDelayTime();

        return [
            'severity' => $delayTime > 120 ? 'high' : ($delayTime > 60 ? 'medium' : 'low'),
            'affected_processes' => self::getAffectedProcesses($process),
            'resource_impact' => self::calculateResourceImpact($process),
        ];
    }

    protected static function getSuggestedActions(SurgicalProcess $process): array
    {
        $stage = $process->estado;
        $delayTime = $process->getDelayTime();

        return match($stage) {
            SurgicalProcess::STATUS_MATERIAL_PREPARATION => [
                'Verificar disponibilidad de personal adicional',
                'Priorizar elementos críticos',
                'Considerar kit alternativo',
            ],
            SurgicalProcess::STATUS_DISPATCHED => [
                'Verificar ruta de entrega',
                'Considerar ruta alternativa',
                'Contactar al transportista',
            ],
            default => [
                'Revisar recursos disponibles',
                'Evaluar prioridades',
                'Contactar al supervisor',
            ],
        };
    }

    /**
     * Métodos de estado
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    public function markAsActioned(): bool
    {
        return $this->update(['actioned_at' => now()]);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isActioned(): bool
    {
        return $this->actioned_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function requiresAction(): bool
    {
        return in_array($this->type, [
            self::TYPE_URGENT_ACTION,
            self::TYPE_QUALITY_ISSUE,
            self::TYPE_EQUIPMENT_ISSUE,
        ]);
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeRequiringAction($query)
    {
        return $query->whereNull('actioned_at')
            ->whereIn('type', [
                self::TYPE_URGENT_ACTION,
                self::TYPE_QUALITY_ISSUE,
                self::TYPE_EQUIPMENT_ISSUE,
            ]);
    }
}
