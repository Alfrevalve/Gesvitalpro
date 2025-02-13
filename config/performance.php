<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puede configurar todos los aspectos del monitoreo de rendimiento,
    | incluyendo umbrales, intervalos de muestreo y acciones de mitigación.
    |
    */

    'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Sampling Configuration
    |--------------------------------------------------------------------------
    */
    'sampling' => [
        'enabled' => env('PERFORMANCE_SAMPLING_ENABLED', true),
        'rate' => env('PERFORMANCE_SAMPLE_RATE', 0.1), // 10% de las peticiones
        'exclude_paths' => [
            'public/*',
            '_debugbar/*',
            'horizon/*',
            'telescope/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thresholds Configuration
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        // Umbrales de peticiones HTTP
        'request_duration' => env('PERFORMANCE_REQUEST_THRESHOLD', 1000), // ms
        'slow_request' => env('PERFORMANCE_SLOW_REQUEST', 3000), // ms
        'critical_request' => env('PERFORMANCE_CRITICAL_REQUEST', 5000), // ms

        // Umbrales de base de datos
        'query_duration' => env('PERFORMANCE_QUERY_THRESHOLD', 100), // ms
        'slow_query' => env('PERFORMANCE_SLOW_QUERY', 500), // ms
        'critical_query' => env('PERFORMANCE_CRITICAL_QUERY', 1000), // ms
        'max_queries_per_request' => env('PERFORMANCE_MAX_QUERIES', 50),

        // Umbrales de memoria
        'memory_usage' => env('PERFORMANCE_MEMORY_THRESHOLD', 128), // MB
        'critical_memory' => env('PERFORMANCE_CRITICAL_MEMORY', 256), // MB
        'memory_leak_threshold' => env('PERFORMANCE_MEMORY_LEAK', 512), // MB

        // Umbrales de caché
        'cache_hit_rate' => env('PERFORMANCE_CACHE_HIT_RATE', 80), // porcentaje
        'critical_cache_rate' => env('PERFORMANCE_CRITICAL_CACHE_RATE', 50), // porcentaje
        'cache_ttl' => env('PERFORMANCE_CACHE_TTL', 3600), // segundos

        // Umbrales de CPU
        'cpu_usage' => env('PERFORMANCE_CPU_THRESHOLD', 80), // porcentaje
        'critical_cpu' => env('PERFORMANCE_CRITICAL_CPU', 90), // porcentaje
        'load_average' => env('PERFORMANCE_LOAD_AVERAGE', 5),

        // Umbrales de disco
        'disk_usage' => env('PERFORMANCE_DISK_THRESHOLD', 80), // porcentaje
        'critical_disk' => env('PERFORMANCE_CRITICAL_DISK', 90), // porcentaje
        'disk_iops' => env('PERFORMANCE_DISK_IOPS', 1000),

        // Umbrales de cola
        'queue_size' => env('PERFORMANCE_QUEUE_SIZE', 1000),
        'queue_wait_time' => env('PERFORMANCE_QUEUE_WAIT', 300), // segundos
        'failed_jobs' => env('PERFORMANCE_FAILED_JOBS', 100),

        // Umbrales de API
        'api_response_time' => env('PERFORMANCE_API_RESPONSE', 1000), // ms
        'api_error_rate' => env('PERFORMANCE_API_ERROR_RATE', 5), // porcentaje
        'api_timeout' => env('PERFORMANCE_API_TIMEOUT', 30), // segundos
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Collection
    |--------------------------------------------------------------------------
    */
    'metrics' => [
        'storage' => [
            'driver' => env('PERFORMANCE_METRICS_DRIVER', 'database'),
            'connection' => env('PERFORMANCE_METRICS_CONNECTION'),
            'table' => env('PERFORMANCE_METRICS_TABLE', 'performance_metrics'),
        ],

        'retention' => [
            'days' => env('PERFORMANCE_METRICS_RETENTION', 30),
            'aggregate_after' => env('PERFORMANCE_METRICS_AGGREGATE', 7),
        ],

        'aggregation' => [
            'intervals' => ['hourly', 'daily', 'weekly', 'monthly'],
            'functions' => ['avg', 'min', 'max', 'count', 'p95', 'p99'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Features
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'requests' => [
            'enabled' => true,
            'track_middleware' => true,
            'track_queries' => true,
            'track_models' => true,
            'track_events' => true,
            'track_cache' => true,
        ],

        'queries' => [
            'enabled' => true,
            'log_slow_queries' => true,
            'explain_queries' => true,
            'backtrace' => true,
        ],

        'memory' => [
            'enabled' => true,
            'track_objects' => true,
            'track_leaks' => true,
        ],

        'cpu' => [
            'enabled' => true,
            'track_load' => true,
            'track_processes' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mitigation Actions
    |--------------------------------------------------------------------------
    */
    'mitigation' => [
        'enabled' => env('PERFORMANCE_MITIGATION_ENABLED', true),
        
        'actions' => [
            'high_memory' => [
                'clear_cache',
                'clear_views',
                'restart_workers',
            ],
            'high_cpu' => [
                'throttle_queues',
                'pause_workers',
                'reduce_concurrency',
            ],
            'high_disk' => [
                'clean_logs',
                'clean_temp',
                'compress_old_logs',
            ],
        ],

        'auto_scale' => [
            'enabled' => env('PERFORMANCE_AUTO_SCALE', false),
            'driver' => env('PERFORMANCE_SCALE_DRIVER', 'local'),
            'min_instances' => env('PERFORMANCE_MIN_INSTANCES', 1),
            'max_instances' => env('PERFORMANCE_MAX_INSTANCES', 5),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    */
    'alerting' => [
        'enabled' => env('PERFORMANCE_ALERTS_ENABLED', true),
        
        'channels' => [
            'mail' => [
                'enabled' => true,
                'recipients' => explode(',', env('PERFORMANCE_ALERT_EMAILS', '')),
            ],
            'slack' => [
                'enabled' => env('PERFORMANCE_SLACK_ENABLED', false),
                'webhook' => env('PERFORMANCE_SLACK_WEBHOOK'),
                'channel' => env('PERFORMANCE_SLACK_CHANNEL'),
            ],
        ],

        'throttle' => [
            'enabled' => true,
            'frequency' => env('PERFORMANCE_ALERT_FREQUENCY', 15), // minutos
        ],

        'grouping' => [
            'enabled' => true,
            'window' => env('PERFORMANCE_ALERT_WINDOW', 5), // minutos
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting Configuration
    |--------------------------------------------------------------------------
    */
    'reporting' => [
        'enabled' => env('PERFORMANCE_REPORTS_ENABLED', true),
        
        'schedule' => [
            'daily' => [
                'enabled' => true,
                'time' => '23:59',
                'recipients' => explode(',', env('PERFORMANCE_REPORT_EMAILS', '')),
            ],
            'weekly' => [
                'enabled' => true,
                'day' => 'sunday',
                'time' => '23:59',
            ],
            'monthly' => [
                'enabled' => true,
                'day' => 'last',
                'time' => '23:59',
            ],
        ],

        'formats' => [
            'pdf' => true,
            'excel' => true,
            'json' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled' => env('PERFORMANCE_DASHBOARD_ENABLED', true),
        'route' => env('PERFORMANCE_DASHBOARD_ROUTE', 'admin/performance'),
        'middleware' => ['web', 'auth', 'role:admin'],
        
        'refresh_interval' => env('PERFORMANCE_DASHBOARD_REFRESH', 30), // segundos
        
        'views' => [
            '1h' => '1 hora',
            '6h' => '6 horas',
            '24h' => '24 horas',
            '7d' => '7 días',
            '30d' => '30 días',
        ],

        'charts' => [
            'response_times' => true,
            'memory_usage' => true,
            'cpu_usage' => true,
            'database_queries' => true,
            'cache_hits' => true,
            'queue_jobs' => true,
        ],
    ],
];
