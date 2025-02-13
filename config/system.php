<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Sistema
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'enabled' => true,
        'retention_days' => 90,
        'log_sensitive_data' => true,
    ],

    'security' => [
        'force_https' => env('FORCE_HTTPS', true),
        'secure_headers' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
    ],

    'performance' => [
        'memory_limit' => '512M',
        'max_execution_time' => 300,
        'optimize_database' => true,
        'cache_duration' => 86400, // 24 hours
    ],

    'cleanup' => [
        'enabled' => true,
        'schedule' => '0 0 * * *', // Daily at midnight
        'batch_size' => 1000,
        'types' => [
            'logs' => [
                'enabled' => true,
                'days' => 30,
            ],
            'audit' => [
                'enabled' => true,
                'days' => 90,
            ],
            'sessions' => [
                'enabled' => true,
                'days' => 7,
            ],
            'cache' => [
                'enabled' => true,
                'days' => 1,
            ],
        ],
    ],

    'ui' => [
        'toast_notifications' => true,
        'loading_indicators' => true,
        'breadcrumbs' => true,
    ],

    'logging' => [
        'detailed_errors' => true,
        'query_logging' => env('APP_DEBUG', false),
        'audit_logging' => true,
        'security_logging' => true,
    ],

    'maintenance' => [
        'enabled' => env('MAINTENANCE_MODE', false),
        'message' => 'El sistema está en mantenimiento. Por favor, intente más tarde.',
        'allowed_ips' => explode(',', env('MAINTENANCE_ALLOWED_IPS', '')),
    ],
];
