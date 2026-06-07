<?php

/**
 * Production Security Configuration
 * Phase 4: Security & Production Hardening
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for sensitive endpoints.
    | Format: [max_attempts, decay_minutes]
    |
    */
    'rate_limits' => [
        'login'         => [5, 1],    // 5 attempts per minute
        'register'      => [3, 1],    // 3 registrations per minute
        'deposit'       => [10, 1],   // 10 deposit attempts per minute
        'api'           => [60, 1],   // 60 API calls per minute
        'web'           => [120, 1],  // 120 web requests per minute
        'password_reset'=> [3, 5],    // 3 attempts per 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Deposit Limits (Real Money)
    |--------------------------------------------------------------------------
    */
    'deposit' => [
        'min_amount'    => 1.00,       // Minimum deposit in EUR
        'max_amount'    => 10000.00,   // Maximum single deposit in EUR
        'daily_limit'   => 50000.00,   // Maximum daily deposit per user
        'currency'      => 'EUR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'lifetime'          => 120,    // Minutes
        'secure'            => true,   // HTTPS only cookies
        'http_only'         => true,   // No JS access to session cookie
        'same_site'         => 'lax',  // CSRF protection
        'regenerate_on_login' => true, // Prevent session fixation
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'X-Frame-Options'           => 'SAMEORIGIN',
        'X-Content-Type-Options'    => 'nosniff',
        'X-XSS-Protection'          => '1; mode=block',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'geolocation=(), microphone=(), camera=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'log_failed_logins'     => true,
        'log_deposits'          => true,
        'log_withdrawals'       => true,
        'log_admin_actions'     => true,
        'alert_on_large_deposit'=> true,
        'large_deposit_threshold' => 1000.00, // EUR
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo / Test Account Restrictions
    |--------------------------------------------------------------------------
    |
    | In production, demo seeders should be disabled.
    | Set DISABLE_DEMO_ACCOUNTS=true in .env
    |
    */
    'disable_demo_accounts' => env('DISABLE_DEMO_ACCOUNTS', true),
    'disable_test_payments' => env('DISABLE_TEST_PAYMENTS', true),
];
