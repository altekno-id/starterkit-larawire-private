<?php

return [
    'name' => 'Web',
    'desc' => 'Aplikasi root domain.',
    'icon' => 'world',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Ringkasan aplikasi root.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
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
                    'icon' => 'folder',
                    'children' => [
                        [
                            'label' => 'Data',
                            'icon' => 'table',
                            'route' => 'web.module1.index',
                        ],
                        [
                            'label' => 'Form',
                            'icon' => 'file-plus',
                            'route' => 'web.module1.create',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
