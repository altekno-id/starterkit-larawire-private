@php
    $rawName = (string) ($name ?? 'circle');
    $icon = match ($rawName) {
        'ri-global-line' => 'world',
        'ri-apps-line', 'ri-apps-2-line' => 'apps',
        'ri-dashboard-line' => 'layout-dashboard',
        'ri-folder-line' => 'folder',
        'ri-user-settings-line', 'user-management' => 'users-group',
        default => str($rawName)->replaceStart('ri-', '')->replaceEnd('-line', '')->toString(),
    };

    $allowedIcons = [
        'apps',
        'alert-triangle',
        'arrow-left',
        'building',
        'check',
        'chevron-down',
        'chevron-right',
        'circle',
        'circle-check',
        'circle-x',
        'folder',
        'file-plus',
        'info-circle',
        'layout-dashboard',
        'lock',
        'logout',
        'menu-2',
        'shield-check',
        'table',
        'user',
        'user-circle',
        'users',
        'users-group',
        'world',
    ];

    $icon = in_array($icon, $allowedIcons, true) ? $icon : 'circle';
    $classes = trim('icon icon-tabler icon-tabler-'.$icon.' '.($class ?? ''));
@endphp

<svg xmlns="http://www.w3.org/2000/svg" class="{{ $classes }}" width="{{ $size ?? 24 }}" height="{{ $size ?? 24 }}" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <path stroke="none" d="M0 0h24v24H0z" fill="none" />

    @switch($icon)
        @case('apps')
            <path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            <path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            <path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            <path d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            @break

        @case('alert-triangle')
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
            <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z" />
            @break

        @case('arrow-left')
            <path d="M5 12l14 0" />
            <path d="M5 12l6 6" />
            <path d="M5 12l6 -6" />
            @break

        @case('building')
            <path d="M3 21l18 0" />
            <path d="M9 8l1 0" />
            <path d="M9 12l1 0" />
            <path d="M9 16l1 0" />
            <path d="M14 8l1 0" />
            <path d="M14 12l1 0" />
            <path d="M14 16l1 0" />
            <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
            @break

        @case('check')
            <path d="M5 12l5 5l10 -10" />
            @break

        @case('chevron-down')
            <path d="M6 9l6 6l6 -6" />
            @break

        @case('chevron-right')
            <path d="M9 6l6 6l-6 6" />
            @break

        @case('circle-check')
            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
            <path d="M9 12l2 2l4 -4" />
            @break

        @case('circle-x')
            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
            <path d="M10 10l4 4m0 -4l-4 4" />
            @break

        @case('folder')
            <path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" />
            @break

        @case('file-plus')
            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
            <path d="M12 11v6" />
            <path d="M9 14h6" />
            @break

        @case('info-circle')
            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
            <path d="M12 9h.01" />
            <path d="M11 12h1v4h1" />
            @break

        @case('layout-dashboard')
            <path d="M4 4h6v8h-6z" />
            <path d="M4 16h6v4h-6z" />
            <path d="M14 12h6v8h-6z" />
            <path d="M14 4h6v4h-6z" />
            @break

        @case('lock')
            <path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" />
            <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
            <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
            @break

        @case('logout')
            <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
            <path d="M9 12h12l-3 -3" />
            <path d="M18 15l3 -3" />
            @break

        @case('menu-2')
            <path d="M4 6l16 0" />
            <path d="M4 12l16 0" />
            <path d="M4 18l16 0" />
            @break

        @case('shield-check')
            <path d="M11.46 20.846a12 12 0 0 1 -7.46 -10.846v-4l8 -3l8 3v4a12 12 0 0 1 -7.46 10.846a1 1 0 0 1 -1.08 0z" />
            <path d="M9 12l2 2l4 -4" />
            @break

        @case('table')
            <path d="M4 5a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
            <path d="M4 10h16" />
            <path d="M10 3v18" />
            @break

        @case('user')
            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
            <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
            @break

        @case('user-circle')
            <path d="M12 12a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z" />
            <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
            <path d="M12 21a9 9 0 1 0 0 -18a9 9 0 0 0 0 18z" />
            @break

        @case('users')
            <path d="M9 7a4 4 0 1 0 0 8a4 4 0 0 0 0 -8z" />
            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
            @break

        @case('users-group')
            <path d="M10 13a4 4 0 1 0 0 -8a4 4 0 0 0 0 8z" />
            <path d="M3 21v-2a4 4 0 0 1 4 -4h6a4 4 0 0 1 4 4v2" />
            <path d="M17 5a3 3 0 0 1 0 6" />
            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
            @break

        @case('world')
            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
            <path d="M3.6 9h16.8" />
            <path d="M3.6 15h16.8" />
            <path d="M11.5 3a17 17 0 0 0 0 18" />
            <path d="M12.5 3a17 17 0 0 1 0 18" />
            @break

        @default
            <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
    @endswitch
</svg>
