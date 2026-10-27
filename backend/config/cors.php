<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    // Bearer tokens, not cookies. A wildcard origin is rejected by browsers
    // when credentials are enabled, and echoing the request Origin makes the
    // publicly cached locality responses fail CORS for the other subdomain.
    // App\Http\ApiCors forces these headers onto every API response.
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => 0,
    'paths' => ['api/*'],
];
