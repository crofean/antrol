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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mobilejkn' => [
        'base_url' => env('MOBILEJKN_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs/bpjs'),
        'cons_id' => env('MOBILEJKN_CONS_ID'),
        'user_key' => env('MOBILEJKN_USER_KEY'),
        'secret_key' => env('MOBILEJKN_SECRET_KEY'),
        'kd_pj' => env('BPJS_KD_PJ', 'BPJ'), // BPJS payer code
        'exclude_poli' => env('BPJS_EXCLUDE_POLI', ''), // Comma-separated list of poli codes to exclude
    ],
];
