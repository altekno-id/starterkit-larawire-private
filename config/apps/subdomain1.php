<?php

return [
    'name' => 'Subdomain 1',
    'desc' => 'Contoh aplikasi subdomain pertama.',
    'icon' => 'apps',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Ringkasan aplikasi.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'subdomain1.dashboard',
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
                            'route' => 'subdomain1.module1.index',
                        ],
                        [
                            'label' => 'Form',
                            'route' => 'subdomain1.module1.create',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
