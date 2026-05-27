<?php

return [
    'name' => 'Web',
    'desc' => 'Root domain app.',
    'icon' => 'world',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Root app summary.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'route' => 'web.dashboard',
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
