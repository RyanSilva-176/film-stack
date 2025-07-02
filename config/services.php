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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    'tmdb' => [
        'api_key' => env('TMDB_API_KEY'),
        'account_id' => env('TMDB_ACCOUNT_ID'),
        'base_url' => 'https://api.themoviedb.org/3',
        'image_base_url' => 'https://image.tmdb.org/t/p/',
        'image_sizes' => [
            'poster' => ['w92', 'w154', 'w185', 'w342', 'w500', 'w780', 'original'],
            'backdrop' => ['w300', 'w780', 'w1280', 'original'],
            'logo' => ['w45', 'w92', 'w154', 'w185', 'w300', 'w500', 'original'],
            'profile' => ['w45', 'w185', 'h632', 'original'],
        ],
        'default_sizes' => [
            'poster' => 'w500',
            'backdrop' => 'w1280',
            'logo' => 'w185',
            'profile' => 'w185',
        ]
    ]

];
