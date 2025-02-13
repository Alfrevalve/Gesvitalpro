<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar todos los aspectos del sistema de monitoreo,
    | incluyendo umbrales, intervalos y canales de notificación.
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Monitoring Intervals
    |--------------------------------------------------------------------------
    */
    'intervals' => [
        'default' => env('MONITORING_INTERVAL', 300), // 5 minutos
        'critical' => env('MONITORING_CRITICAL_INTERVAL', 60), // 1 minuto
        'cleanup' => env('MONITORING_CLEANUP_INTERVAL', 86400), // 24 horas
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'email_enabled' => env('MONITORING_EMAIL_ALERTS', true),
        'slack_enabled' => env('MONITORING_SLACK_ALERTS', false),
        'slack_webhook' => env('MONITORING_SLACK_WEBHOOK'),
        'slack_channel' => env('MONITORING_SLACK_CHANNEL', '#monitoring'),
        
        'throttle' => [
            'enabled' => true,
            'attempts' => 3,
            'decay_minutes' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Configuration
    |--------------------------------------------------------------------------
    */
    'metrics' => [
        'retention_days' => env('METRICS_RETENTION_DAYS', 30),
        
        'cpu' => [
            'enabled' => true,
            'warning_threshold' => env('CPU_WARNING_THRESHOLD', 80),
            'critical_threshold' => env('CPU_CRITICAL_THRESHOLD', 90),
            'check_interval' => 60,
        ],

        'memory' => [
            'enabled' => true,
            'warning_threshold' => env('MEMORY_WARNING_THRESHOLD', 85),
            'critical_threshold' => env('MEMORY_CRITICAL_THRESHOLD', 95),
            'check_interval' => 60,
        ],

        'disk' => [
            'enabled' => true,
            'warning_threshold' => env('DISK_WARNING_THRESHOLD', 85),
            'critical_threshold' => env('DISK_CRITICAL_THRESHOLD', 95),
            'check_interval' => 3600,
            'monitored_paths' => [
                '/' => 'Sistema',
                '/home' => 'Home',
                storage_path() => 'Storage',
            ],
        ],

        'mysql' => [
            'enabled' => true,
            'warning_threshold' => env('MYSQL_WARNING_THRESHOLD', 80),
            'critical_threshold' => env('MYSQL_CRITICAL_THRESHOLD', 90),
            'check_interval' => 300,
            'metrics' => [
                'connections',
                'slow_queries',
                'threads',
                'questions',
            ],
        ],

        'redis' => [
            'enabled' => true,
            'warning_threshold' => env('REDIS_WARNING_THRESHOLD', 80),
            'critical_threshold' => env('REDIS_CRITICAL_THRESHOLD', 90),
            'check_interval' => 300,
        ],

        'queue' => [
            'enabled' => true,
            'warning_threshold' => env('QUEUE_WARNING_THRESHOLD', 100),
            'critical_threshold' => env('QUEUE_CRITICAL_THRESHOLD', 500),
            'check_interval' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        'sample_rate' => env('PERFORMANCE_SAMPLE_RATE', 0.1), // 10% de las peticiones
        
        'slow_threshold' => [
            'request' => env('SLOW_REQUEST_THRESHOLD', 1000), // ms
            'query' => env('SLOW_QUERY_THRESHOLD', 100), // ms
            'redis' => env('SLOW_REDIS_THRESHOLD', 10), // ms
        ],

        'tracking' => [
            'requests' => true,
            'queries' => true,
            'redis' => true,
            'jobs' => true,
            'events' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('MONITORING_LOGGING_ENABLED', true),
        'channel' => env('MONITORING_LOG_CHANNEL', 'monitoring'),
        'level' => env('MONITORING_LOG_LEVEL', 'info'),
        
        'channels' => [
            'monitoring' => [
                'driver' => 'daily',
                'path' => storage_path('logs/monitoring.log'),
                'level' => 'debug',
                'days' => 14,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30), // segundos
        'chart_points' => env('DASHBOARD_CHART_POINTS', 60),
        'default_view' => env('DASHBOARD_DEFAULT_VIEW', '24h'),
        
        'views' => [
            '1h' => '1 hora',
            '6h' => '6 horas',
            '24h' => '24 horas',
            '7d' => '7 días',
            '30d' => '30 días',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Checks
    |--------------------------------------------------------------------------
    */
    'health_checks' => [
        'enabled' => env('HEALTH_CHECKS_ENABLED', true),
        'secret' => env('HEALTH_CHECKS_SECRET'),
        'timeout' => env('HEALTH_CHECKS_TIMEOUT', 5),
        
        'services' => [
            'database' => true,
            'redis' => true,
            'storage' => true,
            'queue' => true,
        ],

        'notify_on' => [
            'failure' => true,
            'recovery' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Monitoring
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'monitor' => env('BACKUP_MONITORING_ENABLED', true),
        'threshold_days' => env('BACKUP_THRESHOLD_DAYS', 1),
        'size_threshold' => env('BACKUP_SIZE_THRESHOLD', 1024 * 1024 * 100), // 100 MB
    ],
];
