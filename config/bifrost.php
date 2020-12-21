<?php

return [
    // Wether or not bifrost auth is enabled
    'enabled' => env('BIFROST_ENABLED', false),

    // Push key
    'auth_push_key' => env('BIFROST_AUTH_PUSH_KEY'),

    // Wether or not to remove users from a role if it is deleted on bifrost
    'auth_push_detech_on_remove' => true,

    // User model options
    'user' => [
        // Model that is used to login
        'model' => 'App\\Models\\User',

        // Key of the remote oauth id
        'oauth_user_id_key' => 'oauth_user_id',

        // Key of the name field
        'name_key' => 'name',

        // Key of the email field
        'email_key' => 'email',

        // Key of the email_verified_at field
        'email_verified_at_key' => 'email_verified_at',
    ],

    // Optional route prefix
    'route_prefix' => env('BIFROST_ROUTE_PREFIX'),

    // Redirects
    'redirects' => [
        // Route name or path where to redirect to
        'after_login' => 'home',

        // Route name or path where to redirect to
        'after_logout' => '/',
    ],
];
