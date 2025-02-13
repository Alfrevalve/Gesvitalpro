<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Link Checking Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar todos los aspectos relacionados con la verificación
    | de enlaces en la aplicación.
    |
    */

    // Configuración general
    'enabled' => env('LINK_CHECKING_ENABLED', true),
    'check_frequency' => env('LINK_CHECK_FREQUENCY', 24), // horas
    'batch_size' => env('LINK_CHECK_BATCH_SIZE', 50),
    'concurrent_checks' => env('LINK_CHECK_CONCURRENT', 10),

    // Timeouts y límites
    'check_timeout' => env('LINK_CHECK_TIMEOUT', 10), // segundos
    'max_redirects' => env('LINK_CHECK_MAX_REDIRECTS', 5),
    'verify_ssl' => env('LINK_CHECK_VERIFY_SSL', true),

    // Configuración de notificaciones
    'notifications' => [
        'enabled' => env('LINK_CHECK_NOTIFICATIONS_ENABLED', true),
        'email' => [
            'enabled' => env('LINK_CHECK_EMAIL_NOTIFICATIONS', true),
            'threshold' => env('LINK_CHECK_EMAIL_THRESHOLD', 10), // número mínimo de enlaces rotos para notificar
            'frequency' => env('LINK_CHECK_EMAIL_FREQUENCY', 24), // horas entre notificaciones
        ],
        'slack' => [
            'enabled' => env('LINK_CHECK_SLACK_NOTIFICATIONS', false),
            'webhook_url' => env('LINK_CHECK_SLACK_WEBHOOK'),
            'channel' => env('LINK_CHECK_SLACK_CHANNEL', '#monitoring'),
        ],
    ],

    // Configuración de limpieza
    'cleanup' => [
        'enabled' => env('LINK_CHECK_CLEANUP_ENABLED', true),
        'history_days' => env('LINK_CHECK_HISTORY_DAYS', 30), // días a mantener el historial
        'fixed_days' => env('LINK_CHECK_FIXED_DAYS', 90), // días a mantener enlaces arreglados
    ],

    // Patrones de exclusión predeterminados
    'default_exclusions' => [
        '*.local',
        'localhost*',
        '127.0.0.1*',
        'example.com*',
        'test.*',
    ],

    // Códigos de estado HTTP a ignorar
    'ignore_status_codes' => [
        301, // Moved Permanently
        302, // Found
        307, // Temporary Redirect
        308, // Permanent Redirect
    ],

    // Headers personalizados para las peticiones
    'request_headers' => [
        'User-Agent' => 'GesVitalPro Link Checker/1.0',
        'Accept' => '*/*',
        'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
    ],

    // Configuración de registro
    'logging' => [
        'enabled' => env('LINK_CHECK_LOGGING_ENABLED', true),
        'channel' => env('LINK_CHECK_LOG_CHANNEL', 'daily'),
        'level' => env('LINK_CHECK_LOG_LEVEL', 'info'),
    ],

    // Configuración de caché
    'cache' => [
        'enabled' => env('LINK_CHECK_CACHE_ENABLED', true),
        'ttl' => env('LINK_CHECK_CACHE_TTL', 3600), // segundos
        'prefix' => 'link_check:',
    ],

    // Configuración de rendimiento
    'performance' => [
        'max_execution_time' => env('LINK_CHECK_MAX_EXECUTION_TIME', 0),
        'memory_limit' => env('LINK_CHECK_MEMORY_LIMIT', '256M'),
    ],

    // Configuración de reintentos
    'retry' => [
        'times' => env('LINK_CHECK_RETRY_TIMES', 3),
        'sleep' => env('LINK_CHECK_RETRY_SLEEP', 5),
        'when' => [
            \GuzzleHttp\Exception\ConnectException::class,
            \GuzzleHttp\Exception\RequestException::class,
        ],
    ],

    // Configuración de monitoreo
    'monitoring' => [
        'enabled' => env('LINK_CHECK_MONITORING_ENABLED', true),
        'threshold' => [
            'warning' => env('LINK_CHECK_WARNING_THRESHOLD', 10),
            'critical' => env('LINK_CHECK_CRITICAL_THRESHOLD', 25),
        ],
        'metrics' => [
            'enabled' => env('LINK_CHECK_METRICS_ENABLED', true),
            'driver' => env('LINK_CHECK_METRICS_DRIVER', 'database'),
        ],
    ],
];
