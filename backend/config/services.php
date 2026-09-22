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

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT', 'http://localhost:8000/auth/google/callback'),
        'maps_key'      => env('GOOGLE_MAPS_API_KEY'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT', 'http://localhost:8000/login/facebook/callback'),
    ],

    // Public URL where the React CMS is served. Used by
    // App\Http\Controllers\Auth\CmsSocialLoginController to redirect back after
    // a successful social sign-in with the Passport token in the URL fragment.
    'cms' => [
        'url' => env('CMS_APP_URL'),
    ],

    'resend' => [
        'api_key' => env('RESEND_API_KEY'),
    ],

    // 'resend' => [
    //     'api_key' => env('RESEND_API_KEY'),
    // ],
];
