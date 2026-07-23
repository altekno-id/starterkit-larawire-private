<?php

return [
    'name' => 'App 1',
    'desc' => 'Contoh aplikasi pertama.',
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
                            'route' => 'app1.dashboard',
                            'landing' => true,
                        ],
                        [
                            'label' => 'Summary 2',
                            'route' => 'app1.dashboard.summary2',
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
