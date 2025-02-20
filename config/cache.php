<?php

return [
    'default' => env('CACHE_DRIVER', 'redis'),

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'gesbio_cache'),

    // Configuraciones específicas para diferentes tipos de caché
    'ttl' => [
        'surgery_cache' => 3600, // 1 hora
        'equipment_cache' => 7200, // 2 horas
        'dashboard_cache' => 300, // 5 minutos
        'user_preferences' => 86400, // 24 horas
    ],
];
