<?php

/**
 * Admin panel hardening settings (Filament /admin).
 * Login throttling protects against password guessing without slowing
 * normal browsing of the dashboard after sign-in.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Filament login attempt limits
    |--------------------------------------------------------------------------
    |
    | Failed sign-ins are counted per IP + email. Successful logins clear the
    | counter. Defaults: 5 failures within 60 seconds → temporary lockout.
    |
    */
    'login_max_attempts' => (int) env('ADMIN_LOGIN_MAX_ATTEMPTS', 5),

    'login_decay_seconds' => (int) env('ADMIN_LOGIN_DECAY_SECONDS', 60),

];
