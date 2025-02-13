<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'GesVitalPro'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => 'America/Bogota',
    'locale' => 'es',
    'fallback_locale' => 'en',
    'faker_locale' => 'es_ES',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
    ],

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */
        Spatie\Permission\PermissionServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\MonitoringServiceProvider::class,
        App\Providers\LinkCheckServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Custom Application Settings
    |--------------------------------------------------------------------------
    */
    'admin_email' => env('ADMIN_EMAIL', 'admin@gesvitalpro.com'),
    'company_name' => env('COMPANY_NAME', 'GesVitalPro'),
    'company_address' => env('COMPANY_ADDRESS', ''),
    'company_phone' => env('COMPANY_PHONE', ''),
    'company_email' => env('COMPANY_EMAIL', ''),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'secure_cookies' => env('SECURE_COOKIES', true),
    'force_https' => env('FORCE_HTTPS', false),
    'api_rate_limit' => env('API_RATE_LIMIT', 60),
    'api_rate_limit_window' => env('API_RATE_LIMIT_WINDOW', 1),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        '2fa' => env('ENABLE_2FA', true),
        'audit_logs' => env('ENABLE_AUDIT_LOGS', true),
        'api' => env('ENABLE_API', true),
        'notifications' => env('ENABLE_NOTIFICATIONS', true),
        'reports' => env('ENABLE_REPORTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'cache_ttl' => env('CACHE_TTL', 3600),
        'queue_timeout' => env('QUEUE_TIMEOUT', 60),
        'queue_retry_after' => env('QUEUE_RETRY_AFTER', 90),
        'queue_worker_sleep' => env('QUEUE_WORKER_SLEEP', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    */
    'debug_settings' => [
        'debugbar_enabled' => env('DEBUGBAR_ENABLED', false),
        'telescope_enabled' => env('TELESCOPE_ENABLED', false),
        'query_detector_enabled' => env('QUERY_DETECTOR_ENABLED', false),
    ],
];
