<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
            'timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
            'rules' => [
                'min_length' => env('AUTH_PASSWORD_MIN_LENGTH', 8),
                'require_uppercase' => env('AUTH_PASSWORD_REQUIRE_UPPERCASE', false),
                'require_numeric' => env('AUTH_PASSWORD_REQUIRE_NUMERIC', false),
                'require_special_char' => env('AUTH_PASSWORD_REQUIRE_SPECIAL', false),
                'prevent_common_passwords' => env('AUTH_PASSWORD_PREVENT_COMMON', false),
                'password_history' => env('AUTH_PASSWORD_HISTORY', 0),
                'max_age' => env('AUTH_PASSWORD_MAX_AGE', 0),
            ],
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    'security' => [
        'session' => [
            'lifetime' => env('SESSION_LIFETIME', 120), // minutos
            'expire_on_close' => true,
            'same_site' => 'lax',
            'secure' => env('SESSION_SECURE_COOKIE', true),
            'http_only' => true,
        ],
        'login' => [
            'max_attempts' => 5,
            'lockout_time' => 300, // 5 minutos
            'verify_ip' => true,
            'track_location' => true,
        ],
        'two_factor' => [
            'enabled' => env('AUTH_2FA_ENABLED', false),
            'provider' => env('AUTH_2FA_PROVIDER', 'google'),
            'timeout' => env('AUTH_2FA_TIMEOUT', 300),
            'enforce_for_roles' => [],
            'trusted_devices' => [
                'enabled' => env('AUTH_2FA_TRUSTED_DEVICES', false),
                'max_devices' => env('AUTH_2FA_MAX_DEVICES', 3),
                'expire_after' => env('AUTH_2FA_DEVICE_EXPIRY', 30),
            ],
        ],
        'api' => [
            'token_lifetime' => 60, // minutos
            'refresh_token_lifetime' => 1440, // 24 horas
            'rate_limiting' => [
                'enabled' => true,
                'max_attempts' => 60,
                'decay_minutes' => 1,
            ],
        ],
        'headers' => [
            'x-frame-options' => 'SAMEORIGIN',
            'x-content-type-options' => 'nosniff',
            'x-xss-protection' => '1; mode=block',
            'strict-transport-security' => 'max-age=31536000; includeSubDomains',
            'content-security-policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
            'referrer-policy' => 'same-origin',
            'permissions-policy' => 'camera=(), microphone=(), geolocation=()',
        ],
    ],

    'activity_log' => [
        'enabled' => true,
        'events' => [
            'login' => true,
            'logout' => true,
            'failed_login' => true,
            'password_reset' => true,
            'two_factor_challenge' => true,
        ],
        'cleanup' => [
            'enabled' => true,
            'keep_days' => 90,
        ],
    ],
];
