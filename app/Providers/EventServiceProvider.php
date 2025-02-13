<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\LogThresholdExceeded;
use App\Listeners\HandleLogThresholdExceeded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        LogThresholdExceeded::class => [
            HandleLogThresholdExceeded::class,
        ],

        // Eventos del Sistema
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        'Illuminate\Auth\Events\Failed' => [
            'App\Listeners\LogFailedLogin',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'App\Listeners\LogSuccessfulLogout',
        ],
        'Illuminate\Auth\Events\PasswordReset' => [
            'App\Listeners\LogPasswordReset',
        ],

        // Eventos de Monitoreo
        'App\Events\SystemHealthCheckFailed' => [
            'App\Listeners\NotifySystemHealthFailure',
        ],
        'App\Events\DatabaseBackupCompleted' => [
            'App\Listeners\ProcessDatabaseBackupResult',
        ],
        'App\Events\HighSystemLoad' => [
            'App\Listeners\HandleHighSystemLoad',
        ],
        'App\Events\SecurityThreatDetected' => [
            'App\Listeners\HandleSecurityThreat',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Observadores de Modelos
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\SystemAlert::observe(\App\Observers\SystemAlertObserver::class);
        \App\Models\SystemMetric::observe(\App\Observers\SystemMetricObserver::class);
        \App\Models\PerformanceMetric::observe(\App\Observers\PerformanceMetricObserver::class);

        // Eventos de Sistema
        Event::listen('system.backup.completed', function ($event) {
            \Log::info('System backup completed', [
                'size' => $event->size,
                'path' => $event->path,
                'duration' => $event->duration,
            ]);
        });

        Event::listen('system.error', function ($message, $context = []) {
            \Log::error($message, $context);
            
            if (config('logging.monitoring.enabled')) {
                event(new \App\Events\SystemErrorDetected($message, $context));
            }
        });

        // Eventos de Rendimiento
        Event::listen('performance.threshold.exceeded', function ($metric, $value, $threshold) {
            \Log::warning("Performance threshold exceeded for {$metric}", [
                'value' => $value,
                'threshold' => $threshold,
            ]);
        });

        // Eventos de Seguridad
        Event::listen('security.*', function ($event, $data) {
            \Log::channel('security')->info($event, $data);
        });

        // Eventos de Auditoría
        Event::listen('audit.*', function ($event, $data) {
            \Log::channel('audit')->info($event, $data);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array<int, string>
     */
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Listeners'),
        ];
    }

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\SystemMonitoringSubscriber',
        'App\Listeners\SecurityMonitoringSubscriber',
        'App\Listeners\AuditLogSubscriber',
    ];
}
