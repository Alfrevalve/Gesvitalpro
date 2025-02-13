<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración específica para Windows
    |--------------------------------------------------------------------------
    */

    'paths' => [
        // Rutas del sistema
        'php' => env('PHP_PATH', 'C:/xampp/php/php.exe'),
        'composer' => env('COMPOSER_PATH', 'C:/ProgramData/ComposerSetup/bin/composer.phar'),
        'mysql' => env('MYSQL_PATH', 'C:/xampp/mysql/bin/mysql.exe'),
        'xampp' => env('XAMPP_PATH', 'C:/xampp'),
    ],

    'php' => [
        // Configuración de PHP
        'ini_file' => env('PHP_INI_FILE', 'C:/xampp/php/php.ini'),
        'memory_limit' => env('PHP_MEMORY_LIMIT', '512M'),
        'max_execution_time' => env('PHP_MAX_EXECUTION_TIME', '300'),
        'upload_max_filesize' => env('PHP_UPLOAD_MAX_FILESIZE', '10M'),
        'post_max_size' => env('PHP_POST_MAX_SIZE', '10M'),
    ],

    'xampp' => [
        // Configuración de XAMPP
        'htdocs' => env('XAMPP_HTDOCS', 'C:/xampp/htdocs'),
        'apache_port' => env('APACHE_PORT', '80'),
        'mysql_port' => env('MYSQL_PORT', '3306'),
    ],

    'storage' => [
        // Permisos de almacenamiento
        'permissions' => [
            'files' => 0755,
            'directories' => 0755,
        ],
        // Rutas de almacenamiento
        'paths' => [
            'logs' => storage_path('logs'),
            'cache' => storage_path('framework/cache'),
            'sessions' => storage_path('framework/sessions'),
            'views' => storage_path('framework/views'),
            'uploads' => storage_path('app/public'),
        ],
    ],

    'commands' => [
        // Comandos específicos de Windows
        'clear_cache' => 'del /s /q',
        'remove_dir' => 'rd /s /q',
        'make_dir' => 'mkdir',
        'copy' => 'xcopy /s /e /y',
    ],

    'services' => [
        // Servicios de Windows
        'apache' => [
            'name' => 'Apache2.4',
            'start' => 'net start Apache2.4',
            'stop' => 'net stop Apache2.4',
            'restart' => 'net stop Apache2.4 && net start Apache2.4',
        ],
        'mysql' => [
            'name' => 'MySQL',
            'start' => 'net start MySQL',
            'stop' => 'net stop MySQL',
            'restart' => 'net stop MySQL && net start MySQL',
        ],
    ],

    'scheduled_tasks' => [
        // Configuración de tareas programadas
        'artisan_schedule' => [
            'name' => 'Laravel Schedule',
            'command' => 'php artisan schedule:run',
            'frequency' => 'MINUTE',
            'user' => env('WINDOWS_TASK_USER'),
            'password' => env('WINDOWS_TASK_PASSWORD'),
        ],
    ],

    'environment' => [
        // Variables de entorno de Windows
        'path' => [
            'C:/xampp/php',
            'C:/xampp/mysql/bin',
            'C:/ProgramData/ComposerSetup/bin',
        ],
        'temp_dir' => env('TEMP_DIR', 'C:/Windows/Temp'),
    ],

    'optimization' => [
        // Configuraciones de optimización
        'clear_temp_files' => true,
        'clear_windows_temp' => false,
        'optimize_windows_services' => true,
        'restart_services_after_optimization' => false,
    ],

    'security' => [
        // Configuraciones de seguridad
        'disable_error_reporting' => true,
        'hide_server_info' => true,
        'secure_file_permissions' => true,
        'windows_defender_exclusions' => [
            storage_path(),
            base_path('bootstrap/cache'),
        ],
    ],

    'backup' => [
        // Configuración de copias de seguridad
        'destination' => env('BACKUP_PATH', 'C:/backups'),
        'mysql_dump_path' => env('MYSQL_DUMP_PATH', 'C:/xampp/mysql/bin/mysqldump.exe'),
        'compress' => true,
        'retention_days' => 7,
    ],
];
