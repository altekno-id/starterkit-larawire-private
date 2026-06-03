<?php

return [
    'name' => 'App 1',
    'desc' => 'First sample application.',
    'icon' => 'apps',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'App summary.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'app1.dashboard',
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
                            'route' => 'app1.module1.index',
                            'landing' => true,
                        ],
                        [
                            'label' => 'Form',
                            'route' => 'app1.module1.create',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
