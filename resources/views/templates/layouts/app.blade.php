<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="starter-auth-login-url" content="{{ \App\Support\Starter\StarterNavigation::authLoginUrl() }}">
    <title>{{ $title ?? ($currentAppName ?? config('app.name')) }} | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/tabler/static/logo-small.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler-vendors.min.css') }}">
    <style>
        .navbar-vertical {
            --tblr-navbar-bg: #182433;
        }

        .starter-account-summary,
        .starter-account-summary * {
            transition: none;
        }

        .starter-account-menu summary,
        .navbar-vertical .starter-sidebar-details summary {
            list-style: none;
        }

        .starter-account-menu summary {
            display: flex;
        }

        .starter-account-menu summary::-webkit-details-marker,
        .navbar-vertical .starter-sidebar-details summary::-webkit-details-marker {
            display: none;
        }

        .starter-account-panel {
            min-width: 13.5rem;
        }

        .starter-page-body {
            transition: opacity .16s ease;
        }

        .starter-navigate-loader {
            background: color-mix(in srgb, var(--tblr-bg-surface) 88%, transparent);
            opacity: 0;
            pointer-events: none;
            transition: opacity .14s ease;
            z-index: 20;
        }

        .starter-page-loader {
            transform: translateY(-.75rem);
            width: min(16rem, calc(100vw - 2rem));
        }

        .starter-hover-tooltip > .tooltip {
            bottom: calc(100% + .35rem);
            left: 0;
            max-width: min(18rem, 80vw);
            position: absolute;
            pointer-events: none;
        }

        .starter-hover-tooltip:not(:hover):not(:focus-within) > .tooltip {
            display: none;
        }

        body.starter-is-navigating .starter-navigate-loader {
            opacity: 1;
            pointer-events: auto;
        }

        @supports (view-transition-name: starter-page) {
            ::view-transition-old(starter-page) {
                animation: starter-page-out .16s ease both;
            }

            ::view-transition-new(starter-page) {
                animation: starter-page-in .2s ease both;
            }
        }

        @keyframes starter-page-out {
            to {
                opacity: 0;
                transform: translateY(.35rem);
            }
        }

        @keyframes starter-page-in {
            from {
                opacity: 0;
                transform: translateY(.35rem);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .starter-page-body,
            .starter-navigate-loader {
                transition: none;
            }

            ::view-transition-old(starter-page),
            ::view-transition-new(starter-page) {
                animation: none;
            }
        }

        .starter-account-menu[open] .starter-account-panel {
            display: block;
        }

        .navbar-vertical .starter-sidebar-details > summary.nav-link::after,
        .navbar-vertical .starter-sidebar-details > summary.dropdown-item::after {
            border: 0;
            content: "";
            height: 1rem;
            margin-left: auto;
            opacity: .7;
            transform: rotate(0deg);
            transition: transform .15s ease;
            width: 1rem;
            background-color: currentColor;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M9 6l6 6l-6 6'/%3E%3C/svg%3E") center / 1rem 1rem no-repeat;
        }

        .navbar-vertical .starter-sidebar-details[open] > summary.nav-link::after,
        .navbar-vertical .starter-sidebar-details[open] > summary.dropdown-item::after {
            transform: rotate(90deg);
        }

        .navbar-vertical .starter-sidebar-details[open] > .starter-sidebar-submenu {
            display: block;
        }
    </style>
    @livewireStyles
</head>

<body>
    <script src="{{ asset('assets/tabler/dist/js/tabler-theme.min.js') }}"></script>

    @php
        $accountPersistBase = 'starter-account-'.($login?->getKey() ?? 'guest');
    @endphp

    @include('templates.components.toast')

    <div class="page">
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#starter-sidebar-menu" aria-controls="starter-sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="navbar-brand navbar-brand-autodark">
                    <a href="{{ $currentDashboardUrl }}" aria-label="{{ config('app.name') }}" data-starter-navigate>
                        <img src="{{ asset('assets/tabler/static/logo-white.svg') }}" class="navbar-brand-image" alt="{{ config('app.name') }}">
                    </a>
                </div>

                <div class="navbar-nav flex-row d-lg-none ms-auto align-items-center gap-2" x-persist="{{ $accountPersistBase }}-mobile">
                    @include('templates.layouts.app-switcher', ['compact' => true])
                    @include('templates.layouts.account-menu')
                </div>

                <div class="collapse navbar-collapse" id="starter-sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item px-0 px-lg-3 pt-3 pb-1">
                            <span class="subheader">Main Menu</span>
                        </li>

                        @forelse ($sidebarMods as $mod)
                            @foreach ($mod['menus'] as $menu)
                                @include('templates.layouts.menu-item', ['menu' => $menu])
                            @endforeach
                        @empty
                            <li class="nav-item">
                                <span class="nav-link disabled">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        @include('templates.layouts.icon', ['name' => 'circle'])
                                    </span>
                                        <span class="nav-link-title">No menu available</span>
                                </span>
                            </li>
                        @endforelse

                        @if ($login?->role?->isAdmin())
                            <li class="nav-item px-0 px-lg-3 pt-3 pb-1">
                                <span class="subheader">Settings</span>
                            </li>

                            @php
                                $userManagementOpen = request()->routeIs('starter.user-management.*');
                                $rolesActive = request()->routeIs('starter.user-management.roles');
                                $usersActive = request()->routeIs('starter.user-management.users');
                                $clientProfileActive = request()->routeIs('starter.client-profile');
                            @endphp

                            <li class="nav-item {{ $userManagementOpen ? 'active' : '' }}">
                                <details class="starter-sidebar-details" @if ($userManagementOpen) open @endif>
                                    <summary class="nav-link cursor-pointer user-select-none" role="button" aria-controls="starter-global-user-management">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            @include('templates.layouts.icon', ['name' => 'users-group'])
                                        </span>
                                        <span class="nav-link-title">User Management</span>
                                    </summary>
                                    <div class="dropdown-menu starter-sidebar-submenu position-static" id="starter-global-user-management">
                                        <a href="{{ route('starter.user-management.roles') }}" class="dropdown-item {{ $rolesActive ? 'active' : '' }}" @if ($rolesActive) data-current="true" @endif data-starter-navigate data-starter-menu-url="{{ route('starter.user-management.roles') }}">
                                            Role
                                        </a>
                                        <a href="{{ route('starter.user-management.users') }}" class="dropdown-item {{ $usersActive ? 'active' : '' }}" @if ($usersActive) data-current="true" @endif data-starter-navigate data-starter-menu-url="{{ route('starter.user-management.users') }}">
                                            User
                                        </a>
                                    </div>
                                </details>
                            </li>

                            <li class="nav-item {{ $clientProfileActive ? 'active' : '' }}">
                                <a href="{{ route('starter.client-profile') }}" class="nav-link {{ $clientProfileActive ? 'active' : '' }}" @if ($clientProfileActive) data-current="true" @endif data-starter-navigate data-starter-menu-url="{{ route('starter.client-profile') }}">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        @include('templates.layouts.icon', ['name' => 'building'])
                                    </span>
                                    <span class="nav-link-title">Client Profile</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </aside>

        <header class="navbar navbar-expand-lg d-none d-lg-flex d-print-none" x-persist="{{ $accountPersistBase }}-topbar">
            <div class="container-xl">
                <div class="d-none d-lg-flex flex-column lh-sm me-auto">
                    <span class="small text-secondary">Active App</span>
                    <span class="fw-semibold text-truncate" data-starter-current-app-name>{{ $currentAppName ?? 'App' }}</span>
                </div>

                <div class="navbar-nav flex-row order-lg-last ms-auto align-items-center">
                    @include('templates.layouts.app-switcher', ['compact' => false])
                    @include('templates.layouts.account-menu')
                </div>
            </div>
        </header>

        <div class="page-wrapper">
            <div class="page-body starter-page-body" wire:transition="starter-page">
                <div class="container-xl">
                    <div class="starter-slot-area position-relative">
                        {{ $slot }}

                        <div class="starter-navigate-loader position-absolute top-0 start-0 end-0 bottom-0 d-flex align-items-center justify-content-center rounded" aria-label="Loading..." role="status">
                            <div class="starter-page-loader text-center">
                                <div class="mb-3">
                                    <span class="navbar-brand navbar-brand-autodark justify-content-center">
                                        <img src="{{ asset('assets/tabler/static/logo-small.svg') }}" height="36" alt="{{ config('app.name') }}">
                                    </span>
                                </div>
                                <div class="text-secondary mb-3">Loading...</div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar progress-bar-indeterminate"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <span class="text-secondary">{{ $currentAppName ?? 'Starter' }}</span>
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            {{ now()->year }} © {{ config('app.name') }}.
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('assets/tabler/dist/js/tabler.min.js') }}" defer></script>
    <script src="{{ asset('assets/mine/starter-runtime.js') }}?v={{ filemtime(public_path('assets/mine/starter-runtime.js')) }}" data-navigate-once defer></script>
    @livewireScripts
</body>

</html>
