<?php

return [
    'domain' => env('APP_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),

    'connector' => [
        'configure_auth' => env('STARTER_CONFIGURE_AUTH', true),
        'configure_shared_session' => env('STARTER_CONFIGURE_SHARED_SESSION', true),
    ],

    'superuser' => [
        'username' => env('STARTER_SUPERUSER_USERNAME', 'superuser'),
        'email' => env('STARTER_SUPERUSER_EMAIL', 'developer@example.test'),
        'password' => env('STARTER_SUPERUSER_PASSWORD'),
    ],
];
