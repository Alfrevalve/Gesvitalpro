<?php

return [
    /*
     * Enable / disable Google2FA.
     */
    'enabled' => env('GOOGLE2FA_ENABLED', true),

    /*
     * Lifetime in minutes.
     * In case you need your users to be asked for a new one time passwords from time to time.
     */
    'lifetime' => env('GOOGLE2FA_LIFETIME', 0), // 0 = eternal

    /*
     * Renew lifetime at every new request.
     */
    'keep_alive' => env('GOOGLE2FA_KEEP_ALIVE', true),

    /*
     * Auth container binding.
     */
    'auth' => 'auth',

    /*
     * Guard.
     */
    'guard' => 'web',

    /*
     * 2FA verified session var.
     */
    'session_var' => 'google2fa',

    /*
     * One Time Password request input name.
     */
    'otp_input' => 'code',

    /*
     * One Time Password Window.
     */
    'window' => 1,

    /*
     * Forbid user to reuse One Time Passwords.
     */
    'forbid_old_passwords' => false,

    /*
     * User's table column for google2fa secret.
     */
    'secret_column' => 'two_factor_secret',

    /*
     * Recovery codes column.
     */
    'recovery_codes_column' => 'two_factor_recovery_codes',

    /*
     * Encryption keys for the secret column.
     */
    'encryption_key' => env('GOOGLE2FA_ENCRYPTION_KEY'),

    /*
     * QR Code size.
     */
    'qr_size' => 200,

    /*
     * QR Code background color.
     */
    'qr_background_color' => '#ffffff',

    /*
     * QR Code foreground color.
     */
    'qr_foreground_color' => '#000000',
];
