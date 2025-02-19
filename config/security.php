<?php

return [
    // Configuración de intentos de inicio de sesión
    'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
    'lockout_time' => env('LOCKOUT_TIME', 15), // minutos
    'password_expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),

    // Configuración de autenticación de dos factores
    'require_2fa_for_roles' => [
        'admin',
        'doctor',
        'supervisor',
    ],
    '2fa_methods' => [
        'email',
        'google_authenticator',
        'sms',
    ],

    // Control de acceso IP
    'allowed_ips' => env('ALLOWED_IPS', ''),
    'blocked_ips' => env('BLOCKED_IPS', ''),
    'ip_whitelist_enabled' => env('IP_WHITELIST_ENABLED', false),

    // Patrones de detección de ataques
    'suspicious_patterns' => [
        'sql_injection' => [
            'UNION[[:space:]]+SELECT',
            'DROP[[:space:]]+TABLE',
            'DELETE[[:space:]]+FROM',
            'INSERT[[:space:]]+INTO',
            'UPDATE[[:space:]]+.*SET',
            'EXEC[[:space:]]+.*sp_',
            'WAITFOR[[:space:]]+DELAY',
        ],
        'xss' => [
            '<[[:space:]]*script',
            'javascript[[:space:]]*:',
            'on[a-zA-Z]+[[:space:]]*=',
            'data[[:space:]]*:',
            'vbscript[[:space:]]*:',
            '<[[:space:]]*iframe',
            '<[[:space:]]*object',
            '<[[:space:]]*embed',
        ],
        'path_traversal' => [
            '\.\.[\/\\]',
            '%2e%2e%2f',
            '\.\.%2f',
            '%252e%252e%252f',
        ],
    ],

    // Configuración de headers de seguridad
    'security_headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    // Configuración de sesión
    'session' => [
        'regenerate' => true,
        'expire_on_close' => true,
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'lax',
    ],

    // Configuración de contraseñas
    'password_requirements' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_numeric' => true,
        'require_special_chars' => true,
        'prevent_common_passwords' => true,
        'prevent_sequential_chars' => true,
        'prevent_personal_info' => true,
    ],

    // Rate Limiting
    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => [
            'login' => [
                'tries' => 5,
                'minutes' => 15,
            ],
            'password_reset' => [
                'tries' => 3,
                'minutes' => 60,
            ],
            'api' => [
                'tries' => 60,
                'minutes' => 1,
            ],
        ],
    ],

    // Monitoreo de seguridad
    'security_monitoring' => [
        'log_suspicious_activities' => true,
        'notify_admins_on_suspicious_activity' => true,
        'block_suspicious_ips' => true,
        'alert_on_multiple_failed_logins' => true,
        'alert_on_password_changes' => true,
        'alert_on_role_changes' => true,
    ],

    // Configuración de backup
    'backup' => [
        'encrypt_backups' => true,
        'backup_frequency' => 'daily',
        'retain_backups_for_days' => 30,
        'notify_on_backup_failure' => true,
    ],

    // Configuración de auditoría
    'audit' => [
        'enabled' => true,
        'log_authentication_events' => true,
        'log_model_changes' => true,
        'log_route_access' => true,
        'retention_period' => 90, // días
    ],
];
