<?php
return [
    'laravelpassport' => [
        'host'          => env('BIFROST_HOST', 'https://bifrost.thor.edu'),
        'client_id'     => env('BIFROST_CLIENT_ID'),
        'client_secret' => env('BIFROST_CLIENT_SECRET'),
        'redirect'      => env('BIFROST_REDIRECT_URL', '/login/callback'),
    ],
];
