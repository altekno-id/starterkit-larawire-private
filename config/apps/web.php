<?php

return [
    'name' => 'Web',
    'desc' => 'Aplikasi root domain.',
    'icon' => 'ri-global-line',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Ringkasan aplikasi root.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'ri-dashboard-line',
                    'route' => 'web.dashboard',
                ],
            ],
        ],

        'module1' => [
            'name' => 'Modules 1',
            'desc' => 'Contoh modul CRUD.',
            'menus' => [
                [
                    'label' => 'Modules 1',
                    'icon' => 'ri-folder-line',
                    'children' => [
                        [
                            'label' => 'Data',
                            'route' => 'web.module1.index',
                        ],
                        [
                            'label' => 'Form',
                            'route' => 'web.module1.create',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
