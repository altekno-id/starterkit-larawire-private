<?php

return [
    'name' => 'App 2',
    'desc' => 'Second sample application.',
    'icon' => 'apps',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'App summary.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'app2.dashboard',
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
                            'route' => 'app2.module1.index',
                            'landing' => true,
                        ],
                        [
                            'label' => 'Form',
                            'route' => 'app2.module1.create',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
