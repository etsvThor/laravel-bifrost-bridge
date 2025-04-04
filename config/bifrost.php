<?php

return [
    // Whether or not bifrost auth is enabled
    'enabled' => env('BIFROST_ENABLED', false),

    // Push key
    'auth_push_key' => env('BIFROST_AUTH_PUSH_KEY'),

    // Whether or not to remove users from a role if it is deleted on bifrost
    'auth_push_detach_on_remove' => true,

    // Wether or not to remember the local user after a successful login
    'remember_user' => true,

    // Whether or not to keep track of which roles are assigned by Bifrost, and only change roles assigned by Bifrost
    // requires an 'auto_assigned' pivot in the user.roles and role.users relations
    'auto_assign' => false,

    // User model options
    'user' => [
        // Model that is used to login
        'model' => 'App\\Models\\User',

        'requires_verified_email' => true,

        // Key of the remote oauth id
        'oauth_user_id_key' => 'oauth_user_id',

        // Key of the name field
        'name_key' => 'name',

        // Key of the email field
        'email_key' => 'email',

        // Key of the email_verified_at field
        'email_verified_at_key' => 'email_verified_at',

        // Key of the member_id field (leave null to leave out)
        'member_id_key' => null,
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

    // Service configuration
    'service' => [
        'host'          => env('BIFROST_HOST', 'https://bifrost.thor.edu'),
        'client_id'     => env('BIFROST_CLIENT_ID'),
        'client_secret' => env('BIFROST_CLIENT_SECRET'),
        'redirect'      => env('BIFROST_REDIRECT_URL', '/login/callback'),

        'authorize_uri' => 'oauth/authorize',
        'token_uri'     => 'oauth/token',
        'userinfo_uri'  => 'api/user',

        'register'      => false,

        'scopes'        => [],

        'guzzle'        => [],
    ],
];
