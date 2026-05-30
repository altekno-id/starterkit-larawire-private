<?php

return [
    'name' => 'Subdomain 1',
    'desc' => 'First sample subdomain app.',
    'icon' => 'apps',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'App summary.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'subdomain1.dashboard',
                    'landing' => true,
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
                            'route' => 'subdomain1.module1.index',
                            'landing' => true,
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
