<?php

use Altekno\StarterKit\Themes\Starter\DashcodePowerGridTheme;
use Altekno\StarterKit\Themes\Starter\TablerPowerGridTheme;

$domain = env('APP_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');

return [
    'domain' => $domain,

    'theme' => env('STARTER_THEME', 'tabler'),

    'layout' => env('STARTER_LAYOUT', 'vertical'),

    'themes' => [
        'dashcode' => [
            'views' => 'resources/themes/dashcode/views',
            'assets' => 'public/themes/dashcode/assets',
            'docs' => 'docs/template/dashcode',
            'powergrid' => DashcodePowerGridTheme::class,
            'layouts' => [
                'vertical' => 'starter.templates.layouts.navigation.vertical',
                'horizontal' => 'starter.templates.layouts.navigation.horizontal',
            ],
        ],
        'tabler' => [
            'views' => 'resources/themes/tabler/views',
            'assets' => 'public/themes/tabler/assets',
            'docs' => 'docs/template/tabler',
            'powergrid' => TablerPowerGridTheme::class,
            'layouts' => [
                'vertical' => 'starter.templates.layouts.navigation.vertical',
                'horizontal' => 'starter.templates.layouts.navigation.horizontal',
            ],
        ],
    ],

    'api' => [
        'enabled' => env('STARTER_API_ENABLED', false),
        'domain' => 'api.'.trim((string) $domain, '.'),
    ],

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
