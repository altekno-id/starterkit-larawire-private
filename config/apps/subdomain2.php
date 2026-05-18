<?php

return [
    'name' => 'Subdomain 2',
    'desc' => 'Contoh aplikasi subdomain kedua.',
    'icon' => 'ri-apps-2-line',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Ringkasan aplikasi.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'ri-dashboard-line',
                    'route' => 'subdomain2.dashboard',
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
                            'route' => 'subdomain2.module1.index',
                        ],
                        [
                            'label' => 'Form',
                            'route' => 'subdomain2.module1.create',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
