<?php

return [
    'name' => 'Subdomain 2',
    'desc' => 'Second sample subdomain app.',
    'icon' => 'apps',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'App summary.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'subdomain2.dashboard',
                ],
            ],
        ],

        'module1' => [
            'name' => 'Module 1',
            'desc' => 'Sample CRUD module.',
            'menus' => [
                [
                    'label' => 'Module 1',
                    'icon' => 'folder',
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
