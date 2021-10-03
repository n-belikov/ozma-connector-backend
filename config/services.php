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

    'connectors-cache' => [
        'enable' => env("CONNECTORS_CACHE_ENABLE", false),
        'ttl' => env("CONNECTORS_CACHE_TTL", 900)
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'ebay' => [
        'base_url' => env("EBAY_BASE_URL"),
        'client_id' => env("EBAY_CLIENT_ID"),
        'client_secret' => env("EBAY_CLIENT_SECRET"),
        'refresh_token' => env("EBAY_REFRESH_TOKEN")
    ],

    'site' => [
        'url' => env("SITE_API_URL"),
        'secret_id' => env("SITE_API_SECRET_ID"),
        'secret_key' => env("SITE_API_SECRET_KEY"),
        'version' => env("SITE_API_VERSION")
    ]

];
