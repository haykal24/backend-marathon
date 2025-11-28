<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Toggle
    |--------------------------------------------------------------------------
    |
    | Allow the analytics integration to be disabled via environment variable.
    |
    */
    'enabled' => env('ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | GA4 Property
    |--------------------------------------------------------------------------
    |
    | The GA4 property ID that should be queried.
    |
    */
    'property_id' => env('ANALYTICS_PROPERTY_ID'),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the service account JSON file. You can override the location via
    | `ANALYTICS_CREDENTIALS_PATH` if you store it elsewhere (for example in
    | a secrets volume in production).
    |
    */
    'service_account_credentials_json' => env(
        'ANALYTICS_CREDENTIALS_PATH',
        storage_path('app/analytics/service-account-credentials.json')
    ),

    /*
    |--------------------------------------------------------------------------
    | Cache Lifetime
    |--------------------------------------------------------------------------
    */
    'cache_lifetime_in_minutes' => 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'store' => 'file',
    ],
];