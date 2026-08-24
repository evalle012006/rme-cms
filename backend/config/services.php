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
        'client_id'     => '526819845510-3113hpi5q1512cu628p9q0civ7dajtem.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect'      => 'https://ratemyestate.castledigital.com.au/login/google/callback',
        'maps_key'      => env('GOOGLE_MAPS_API_KEY'),
    ],

    'facebook' => [
        'client_id'     => '2524201768092667',
        'client_secret' => '308e0e679465836845823d02b0d42246',
        'redirect'      => 'https://ratemyestate.castledigital.com.au/login/facebook/callback',
    ],

    // Public URL where the React CMS is served. Used by
    // App\Http\Controllers\Auth\CmsSocialLoginController to redirect back after
    // a successful social sign-in with the Passport token in the URL fragment.
    'cms' => [
        'url' => 'https://ratemyestatecms-react.castledigital.com.au',
    ],

    'resend' => [
        // TEMP: hardcoded — restore env('RESEND_API_KEY') before production merge
        'api_key' => 're_QH7JnWWH_LQx4pWYhFqmVwdjKHqxQWT8c',
    ],

    // 'resend' => [
    //     'api_key' => env('RESEND_API_KEY'),
    // ],
];
