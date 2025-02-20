<?php

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => 'UTC',
    'locale' => 'es',
    'fallback_locale' => 'en',
    'faker_locale' => 'es_ES',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'maintenance' => [
        'driver' => 'file',
    ],
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\BladeServiceProvider::class,
        App\Providers\PerformanceServiceProvider::class,
        App\Providers\CacheOptimizationServiceProvider::class,
        App\Providers\QueryOptimizationServiceProvider::class,
        App\Providers\UiOptimizationServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        App\Providers\CacheServiceProvider::class,
    ])->toArray(),
    'aliases' => Facade::defaultAliases()->merge([
    ])->toArray(),
];
