<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the application's audit logging
    | and security features.
    |
    */

    'logging' => [
        /*
        |--------------------------------------------------------------------------
        | Activity Log Settings
        |--------------------------------------------------------------------------
        */
        'activity' => [
            // Número de días para mantener los logs
            'retention_days' => env('LOG_RETENTION_DAYS', 90),

            // Canales de log específicos
            'channels' => [
                'model_activity' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/model-activity.log'),
                    'level' => 'info',
                    'days' => 14,
                ],
                'audit' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/audit.log'),
                    'level' => 'warning',
                    'days' => 30,
                ],
            ],

            // Modelos que deben ser monitoreados
            'monitored_models' => [
                \App\Models\Surgery::class,
                \App\Models\Equipment::class,
                \App\Models\Line::class,
                \App\Models\User::class,
                \App\Models\Visita::class,
            ],
        ],
    ],

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Rate Limiting
        |--------------------------------------------------------------------------
        */
        'rate_limiting' => [
            'enabled' => true,
            
            // Límites por endpoint
            'limits' => [
                'auth' => [
                    'attempts' => 5,
                    'decay_minutes' => 10,
                ],
                'api' => [
                    'attempts' => 60,
                    'decay_minutes' => 1,
                ],
                'admin' => [
                    'attempts' => 30,
                    'decay_minutes' => 1,
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Critical Actions
        |--------------------------------------------------------------------------
        */
        'critical_actions' => [
            // Acciones que requieren verificación adicional
            'require_confirmation' => [
                'surgery.delete',
                'equipment.delete',
                'line.delete',
                'user.role_change',
            ],

            // Acciones que deben ser notificadas
            'notify_admins' => [
                'surgery.status_change',
                'equipment.maintenance',
                'user.created',
                'user.deleted',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Sensitive Data
        |--------------------------------------------------------------------------
        */
        'sensitive_fields' => [
            'patient_name',
            'doctor',
            'notes',
            'email',
            'password',
        ],
    ],

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        */
        'performance' => [
            'enabled' => true,
            
            // Umbrales de rendimiento
            'thresholds' => [
                'query_time_ms' => 100,
                'memory_mb' => 128,
                'cpu_percent' => 80,
            ],

            // Métricas a monitorear
            'metrics' => [
                'database_queries',
                'cache_hits',
                'response_time',
                'memory_usage',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Error Tracking
        |--------------------------------------------------------------------------
        */
        'error_tracking' => [
            'enabled' => true,
            
            // Niveles de error a trackear
            'levels' => [
                'emergency',
                'alert',
                'critical',
                'error',
            ],

            // Notificar errores críticos
            'notify_on' => [
                'emergency',
                'alert',
            ],
        ],
    ],

    'maintenance' => [
        /*
        |--------------------------------------------------------------------------
        | Database Maintenance
        |--------------------------------------------------------------------------
        */
        'database' => [
            // Limpieza automática de logs antiguos
            'auto_cleanup' => [
                'enabled' => true,
                'older_than_days' => 90,
            ],

            // Optimización automática
            'auto_optimize' => [
                'enabled' => true,
                'frequency_days' => 7,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Maintenance
        |--------------------------------------------------------------------------
        */
        'cache' => [
            // Limpieza automática de caché
            'auto_cleanup' => [
                'enabled' => true,
                'frequency_hours' => 24,
            ],

            // TTL por tipo de caché
            'ttl' => [
                'equipment_status' => 60, // 1 hora
                'surgery_schedule' => 30, // 30 minutos
                'dashboard_stats' => 300, // 5 horas
                'reports' => 3600, // 1 día
            ],
        ],
    ],
];
