<?php

return [
    /*
    |--------------------------------------------------------------------------
    |   Your upbond api service uri
    |--------------------------------------------------------------------------
    |   As set in the upbond auth service administration page
    |
    */
    'api_uri'        => env( 'UPBOND_API_URI', 'api.upbond.io' ),

    /*
    |--------------------------------------------------------------------------
    |   Your upbond auth service domain
    |--------------------------------------------------------------------------
    |   As set in the upbond auth service administration page
    |
    */
    'domain'        => env( 'UPBOND_AUTH_DOMAIN' ),

    /*
    |--------------------------------------------------------------------------
    |   Your APP id
    |--------------------------------------------------------------------------
    |   As set in the upbond auth service administration page
    |
    */
    'client_id'     => env( 'UPBOND_AUTH_CLIENT_ID' ),

    /*
    |--------------------------------------------------------------------------
    |   Your APP secret
    |--------------------------------------------------------------------------
    |   As set in the upbond auth service administration page
    |
    */
    'client_secret' => env( 'UPBOND_AUTH_CLIENT_SECRET' ),

    /*
     |--------------------------------------------------------------------------
     |   The redirect URI
     |--------------------------------------------------------------------------
     |   Should be the same that the one configure in the route to handle the
     |   'Auth\Login\AuthController@callback'
     |
     */
    'redirect_uri'  => env( 'APP_URL' ) . '/auth/callback',

    /*
    |--------------------------------------------------------------------------
    |   Persistence Configuration
    |--------------------------------------------------------------------------
    |   persist_user            (Boolean) Optional. Indicates if you want to persist the user info, default true
    |   persist_access_token    (Boolean) Optional. Indicates if you want to persist the access token, default false
    |   persist_refresh_token   (Boolean) Optional. Indicates if you want to persist the refresh token, default false
    |   persist_id_token        (Boolean) Optional. Indicates if you want to persist the id token, default false
    |
    */
    'persist_user' => true,
    'persist_access_token' => true,
    'persist_refresh_token' => true,
    'persist_id_token' => false,

    /*
    |--------------------------------------------------------------------------
    |   The authorized token issuers
    |--------------------------------------------------------------------------
    |   This is used to verify the decoded tokens when using RS256
    |
    */
    'authorized_issuers'  => [ env( 'UPBOND_AUTH_DOMAIN' ) ],

    /*
    |--------------------------------------------------------------------------
    |   The authorized token audiences
    |--------------------------------------------------------------------------
    |
    */
    'api_identifier'  => '',

    /*
    |--------------------------------------------------------------------------
    |   The secret format
    |--------------------------------------------------------------------------
    |   Used to know if it should decode the secret when using HS256
    |
    */
    'secret_base64_encoded'  => false,

    /*
    |--------------------------------------------------------------------------
    |   Supported algorithms
    |--------------------------------------------------------------------------
    |   Token decoding algorithms supported by your API
    |
    */
    'supported_algs'        => [ 'RS256' ],

    /*
    |--------------------------------------------------------------------------
    |   Extend user class
    |--------------------------------------------------------------------------
    |   Token decoding algorithms supported by your API
    |
    */
    'user' => [
        'web' =>  \Upbond\Auth\Login\AuthUser::class,
        'api' => \Upbond\Auth\Login\AuthJWTUser::class,
    ]

    /*
    |--------------------------------------------------------------------------
    |   Guzzle Options
    |--------------------------------------------------------------------------
    |   guzzle_options    (array) optional. Used to specify additional connection options e.g. proxy settings
    |
    */
    // 'guzzle_options' => []
];
