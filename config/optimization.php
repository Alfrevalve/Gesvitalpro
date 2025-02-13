<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Optimización del Sistema
    |--------------------------------------------------------------------------
    */

    'memory' => [
        // Límite de memoria para procesos normales
        'default' => env('PHP_MEMORY_LIMIT', '256M'),
        
        // Límite de memoria para procesos pesados
        'max' => env('PHP_MAX_MEMORY_LIMIT', '512M'),
        
        // Límite de memoria para procesos de consola
        'console' => env('PHP_CONSOLE_MEMORY_LIMIT', '1024M'),
    ],

    'cache' => [
        // Tiempo de vida del caché en segundos
        'ttl' => env('CACHE_TTL', 3600),
        
        // Prefijo para las claves de caché
        'prefix' => env('CACHE_PREFIX', 'gesvitalpro_'),
        
        // Tamaño máximo del chunk para limpieza de caché
        'chunk_size' => 100,
        
        // Días para mantener el caché
        'retention_days' => 7,
    ],

    'files' => [
        // Tamaño máximo de archivo en MB
        'max_size' => env('UPLOAD_MAX_FILESIZE', 10),
        
        // Extensiones permitidas
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        
        // Días para mantener archivos temporales
        'temp_retention_days' => 1,
    ],

    'database' => [
        // Número máximo de registros por consulta
        'chunk_size' => 1000,
        
        // Tiempo máximo de ejecución para consultas (segundos)
        'timeout' => 60,
        
        // Días para mantener logs de consultas
        'query_log_retention' => 7,
    ],

    'logs' => [
        // Días para mantener logs
        'retention_days' => 7,
        
        // Tamaño máximo del archivo de log en MB
        'max_size' => 100,
        
        // Número máximo de archivos de log
        'max_files' => 30,
    ],

    'maintenance' => [
        // Horario de mantenimiento
        'schedule' => [
            'optimization' => '03:00',
            'cleanup' => '02:00',
            'backup' => '01:00',
        ],
        
        // IPs permitidas durante mantenimiento
        'allowed_ips' => explode(',', env('MAINTENANCE_ALLOWED_IPS', '')),
    ],

    'performance' => [
        // Número máximo de procesos simultáneos
        'max_processes' => 4,
        
        // Tiempo máximo de ejecución (segundos)
        'max_execution_time' => 300,
        
        // Límite de consultas por minuto
        'query_limit' => 1000,
    ],

    'security' => [
        // Tiempo de vida de la sesión (minutos)
        'session_lifetime' => env('SESSION_LIFETIME', 120),
        
        // Regenerar sesión cada X minutos
        'session_regenerate' => 60,
        
        // Número máximo de intentos fallidos
        'max_attempts' => 5,
        
        // Tiempo de bloqueo después de intentos fallidos (minutos)
        'lockout_time' => 10,
    ],

    'monitoring' => [
        // Intervalo de monitoreo en segundos
        'interval' => 300,
        
        // Umbrales de alerta
        'thresholds' => [
            'cpu' => 80, // porcentaje
            'memory' => 80, // porcentaje
            'disk' => 80, // porcentaje
            'response_time' => 1000, // milisegundos
        ],
        
        // Canales de notificación
        'channels' => ['mail', 'slack'],
    ],

    'cleanup' => [
        // Activar limpieza automática
        'enabled' => true,
        
        // Elementos a limpiar
        'targets' => [
            'cache' => true,
            'logs' => true,
            'temp_files' => true,
            'old_backups' => true,
            'sessions' => true,
        ],
    ],
];
