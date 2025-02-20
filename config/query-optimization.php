<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Optimización de Consultas
    |--------------------------------------------------------------------------
    */

    // Umbral para considerar una consulta como lenta (en segundos)
    'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1.0),

    // Habilitar logging de consultas lentas
    'log_slow_queries' => env('LOG_SLOW_QUERIES', true),

    // Número máximo de consultas lentas a almacenar
    'max_slow_queries' => env('MAX_SLOW_QUERIES', 100),

    // Configuración de caché de consultas
    'query_cache' => [
        'enabled' => env('QUERY_CACHE_ENABLED', true),
        'ttl' => env('QUERY_CACHE_TTL', 3600), // 1 hora
        'prefix' => env('QUERY_CACHE_PREFIX', 'query_cache_'),
    ],

    // Configuración de monitoreo
    'monitoring' => [
        // Intervalo de muestreo en segundos
        'sampling_interval' => env('QUERY_MONITORING_INTERVAL', 300), // 5 minutos

        // Métricas a monitorear
        'metrics' => [
            'query_time',
            'query_count',
            'cache_hits',
            'cache_misses',
            'memory_usage',
        ],

        // Umbrales de alerta
        'thresholds' => [
            'average_query_time' => 1.0, // segundos
            'memory_usage' => 100 * 1024 * 1024, // 100MB
            'cache_hit_ratio' => 0.7, // 70%
        ],
    ],

    // Optimizaciones automáticas
    'auto_optimize' => [
        // Habilitar optimizaciones automáticas
        'enabled' => env('AUTO_OPTIMIZE_QUERIES', true),

        // Intervalo entre optimizaciones (en minutos)
        'interval' => env('AUTO_OPTIMIZE_INTERVAL', 60),

        // Tipos de optimización a realizar
        'optimizations' => [
            'analyze_tables' => true,
            'update_statistics' => true,
            'clear_query_cache' => true,
        ],
    ],

    // Patrones de consultas a monitorear especialmente
    'watch_patterns' => [
        'joins' => [
            'pattern' => '/JOIN/i',
            'threshold' => 2.0, // segundos
        ],
        'group_by' => [
            'pattern' => '/GROUP BY/i',
            'threshold' => 1.5,
        ],
        'like_wildcards' => [
            'pattern' => '/LIKE\s+[\'"]%/i',
            'threshold' => 1.0,
        ],
    ],

    // Índices recomendados
    'recommended_indexes' => [
        'surgeries' => [
            ['columns' => ['status', 'surgery_date'], 'type' => 'index'],
            ['columns' => ['medico_id', 'surgery_date'], 'type' => 'index'],
        ],
        'equipment' => [
            ['columns' => ['status', 'next_maintenance'], 'type' => 'index'],
        ],
        'surgery_materials' => [
            ['columns' => ['surgery_id', 'status'], 'type' => 'index'],
        ],
    ],

    // Configuración de reportes
    'reporting' => [
        // Habilitar generación automática de reportes
        'enabled' => env('QUERY_REPORTS_ENABLED', true),

        // Frecuencia de generación de reportes
        'frequency' => 'daily', // daily, weekly, monthly

        // Tipos de reportes a generar
        'types' => [
            'slow_queries',
            'query_patterns',
            'optimization_suggestions',
            'performance_metrics',
        ],

        // Destinatarios de reportes
        'notify' => [
            'slow_queries' => ['database-admin@example.com'],
            'performance_alerts' => ['system-admin@example.com'],
        ],
    ],
];
