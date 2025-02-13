<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    */
    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    */
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'system', 'monitoring'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'system' => [
            'driver' => 'daily',
            'path' => storage_path('logs/system.log'),
            'level' => 'debug',
            'days' => 30,
            'permission' => 0664,
        ],

        'monitoring' => [
            'driver' => 'daily',
            'path' => storage_path('logs/monitoring.log'),
            'level' => 'debug',
            'days' => 30,
            'permission' => 0664,
        ],

        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => 'info',
            'days' => 7,
            'permission' => 0664,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'notice',
            'days' => 90,
            'permission' => 0600,
        ],

        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'),
            'level' => 'info',
            'days' => 365,
            'permission' => 0600,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'GesVitalPro Logger'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_SLACK_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/emergency.log'),
        ],

        'database' => [
            'driver' => 'custom',
            'via' => \App\Logging\DatabaseLogger::class,
            'level' => 'debug',
            'connection' => env('DB_CONNECTION', 'mysql'),
            'table' => 'system_logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Retention
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'system' => env('LOG_RETENTION_SYSTEM', 30),
        'monitoring' => env('LOG_RETENTION_MONITORING', 30),
        'performance' => env('LOG_RETENTION_PERFORMANCE', 7),
        'security' => env('LOG_RETENTION_SECURITY', 90),
        'audit' => env('LOG_RETENTION_AUDIT', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Rotation
    |--------------------------------------------------------------------------
    */
    'rotation' => [
        'max_files' => env('LOG_MAX_FILES', 30),
        'max_size' => env('LOG_MAX_SIZE', '100M'),
        'compress' => env('LOG_COMPRESS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('LOG_MONITORING_ENABLED', true),
        'alert_threshold' => env('LOG_ALERT_THRESHOLD', 100),
        'error_threshold' => env('LOG_ERROR_THRESHOLD', 50),
        'notification_channels' => ['mail', 'slack'],
    ],
];
