<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configuration settings for performance monitoring
    | including thresholds, cache durations, and monitoring settings.
    |
    */

    'monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'log_slow_queries' => true,
        'log_memory_usage' => true,
        'log_cache_hits' => true,
    ],

    'thresholds' => [
        'slow_query' => 500, // milliseconds
        'high_memory' => 80, // MB
        'cache_hit_ratio' => 0.7, // 70%
        'database_size' => 1000, // MB
    ],

    'cache_duration' => [
        'dashboard' => 600,    // 10 minutos
        'equipment' => 1800,   // 30 minutos
        'visits' => 300,       // 5 minutos
        'surgeries' => 900,    // 15 minutos
        'reports' => 3600,     // 1 hora
    ],

    'alerts' => [
        'channels' => [
            'slack' => env('PERFORMANCE_ALERTS_SLACK', false),
            'email' => env('PERFORMANCE_ALERTS_EMAIL', false),
            'log' => true,
        ],
        'thresholds' => [
            'slow_queries_count' => 5,
            'failed_jobs_count' => 10,
            'error_rate' => 0.05, // 5%
        ],
    ],

    'metrics' => [
        'collect' => [
            'response_time' => true,
            'memory_usage' => true,
            'database_queries' => true,
            'cache_hits' => true,
            'error_rates' => true,
        ],
        'retention_days' => 30,
    ],

    'optimization' => [
        'query_cache' => [
            'enabled' => true,
            'duration' => 3600, // 1 hora
        ],
        'model_cache' => [
            'enabled' => true,
            'duration' => 1800, // 30 minutos
        ],
        'view_cache' => [
            'enabled' => env('VIEW_CACHE', true),
        ],
    ],

    'logging' => [
        'slow_queries' => [
            'enabled' => true,
            'threshold' => 500, // milliseconds
            'channel' => 'performance',
        ],
        'memory_peaks' => [
            'enabled' => true,
            'threshold' => 80, // MB
            'channel' => 'performance',
        ],
    ],
];
