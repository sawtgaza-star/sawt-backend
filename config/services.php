<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paypal' => [
        // Fallbacks — the dashboard Settings (paypal_client_id / paypal_secret / paypal_mode) take priority
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    'instagram' => [
        // Instagram Business/Creator account ID (numeric) — the account whose reels we show
        'user_id' => env('INSTAGRAM_USER_ID'),
        // Long-lived access token generated from your Meta app
        'token' => env('INSTAGRAM_ACCESS_TOKEN'),
        // Graph API version
        'version' => env('INSTAGRAM_API_VERSION', 'v21.0'),
        // Short cache (seconds) to avoid hitting IG rate limits. Set to 0 for fully live.
        'cache_ttl' => (int) env('INSTAGRAM_CACHE_TTL', 300),
    ],

];
