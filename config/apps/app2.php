<?php

return [
    'name' => 'App 2',
    'desc' => 'Contoh aplikasi kedua.',
    'icon' => 'apps',

    'mods' => [
        'dashboard' => [
            'name' => 'Dashboard',
            'desc' => 'Ringkasan app.',
            'menus' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'layout-dashboard',
                    'children' => [
                        [
                            'label' => 'Summary 1',
                            'route' => 'app2.dashboard',
                            'landing' => true,
                        ],
                        [
                            'label' => 'Summary 2',
                            'route' => 'app2.dashboard.summary2',
                        ],
                    ],
                ],
            ],
        ],

        'module1' => [
            'name' => 'Module 1',
            'desc' => 'Contoh module CRUD.',
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
