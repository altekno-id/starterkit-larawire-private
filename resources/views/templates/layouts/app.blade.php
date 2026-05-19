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
        :root {
            --starter-sidebar-width: 15rem;
            --starter-sidebar-bg: #182433;
            --starter-sidebar-muted: rgba(255, 255, 255, .58);
            --starter-sidebar-text: rgba(255, 255, 255, .78);
            --starter-sidebar-hover: rgba(255, 255, 255, .08);
            --starter-sidebar-active: rgba(var(--tblr-primary-rgb), .95);
        }

        .navbar-vertical {
            background: var(--starter-sidebar-bg);
            width: var(--starter-sidebar-width);
        }

        @media (min-width: 992px) {
            .page-wrapper {
                margin-left: var(--starter-sidebar-width);
            }
        }

        .starter-brand-mark {
            align-items: center;
            background: var(--tblr-primary);
            border-radius: var(--tblr-border-radius);
            color: var(--tblr-primary-fg);
            display: inline-flex;
            font-weight: 700;
            height: 2rem;
            justify-content: center;
            width: 2rem;
        }

        .starter-menu {
            padding: .5rem .5rem 1rem;
            width: 100%;
        }

        .starter-menu-item {
            margin: .125rem 0;
        }

        .starter-menu-link {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: .5rem;
            color: var(--starter-sidebar-text);
            display: flex;
            gap: .65rem;
            min-height: 2.35rem;
            padding: .5rem .625rem;
            text-align: left;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
            width: 100%;
        }

        .starter-menu-link:hover,
        .starter-menu-link:focus {
            background: var(--starter-sidebar-hover);
            color: #fff;
            text-decoration: none;
        }

        .starter-menu-link.active,
        .starter-menu-link[data-current] {
            background: var(--starter-sidebar-active);
            color: #fff;
        }

        .starter-menu-icon {
            flex: 0 0 1.1rem;
            height: 1.1rem;
            margin-top: .1rem;
            width: 1.1rem;
        }

        .starter-menu-title {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .starter-menu-chevron {
            flex: 0 0 1rem;
            height: 1rem;
            margin-left: auto;
            opacity: .65;
            transition: transform .15s ease;
            width: 1rem;
        }

        .starter-menu-link[aria-expanded="true"] .starter-menu-chevron {
            transform: rotate(90deg);
        }

        .starter-menu-children {
            border-left: 1px solid rgba(255, 255, 255, .09);
            margin: .2rem 0 .35rem 1.15rem;
            padding-left: .45rem;
        }

        .starter-menu-children .starter-menu-link {
            color: var(--starter-sidebar-muted);
            min-height: 2.1rem;
            padding-block: .4rem;
        }

        .starter-menu-children .starter-menu-link.active,
        .starter-menu-children .starter-menu-link[data-current] {
            background: rgba(var(--tblr-primary-rgb), .22);
            color: #fff;
        }

        .starter-sidebar-section {
            color: var(--starter-sidebar-muted);
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .06em;
            padding: .85rem 1rem .35rem;
            text-transform: uppercase;
        }

        .starter-account-avatar {
            background-position: center;
            background-size: cover;
            flex: 0 0 2rem;
        }

        .starter-account-summary,
        .starter-account-summary * {
            transition: none;
        }

        .starter-app-menu,
        .starter-account-menu {
            position: relative;
        }

        .starter-app-menu summary,
        .starter-account-menu summary {
            align-items: center;
            cursor: pointer;
            display: flex;
            list-style: none;
            user-select: none;
        }

        .starter-app-menu summary::-webkit-details-marker,
        .starter-account-menu summary::-webkit-details-marker {
            display: none;
        }

        .starter-app-menu-trigger {
            border-radius: var(--tblr-border-radius);
            color: var(--tblr-secondary);
            gap: .45rem;
            min-height: 2.25rem;
            padding: .45rem .55rem;
        }

        .starter-app-menu-trigger:hover,
        .starter-app-menu[open] .starter-app-menu-trigger {
            background: var(--tblr-bg-surface-secondary);
            color: var(--tblr-body-color);
        }

        .starter-app-menu-icon {
            flex: 0 0 1.1rem;
            height: 1.1rem;
            width: 1.1rem;
        }

        .starter-app-menu-text {
            display: flex;
            min-width: 0;
        }

        .starter-app-menu-name {
            color: var(--tblr-body-color);
            font-size: .925rem;
            font-weight: 500;
            max-width: 12rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .starter-app-menu-chevron {
            color: var(--tblr-secondary);
            height: 1rem;
            width: 1rem;
        }

        .starter-app-menu-panel {
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius-lg);
            box-shadow: var(--tblr-box-shadow-dropdown);
            left: 0;
            min-width: 23rem;
            padding: .45rem;
            position: absolute;
            top: calc(100% + .5rem);
            z-index: 1050;
        }

        .starter-app-menu:not([open]) .starter-app-menu-panel {
            display: none;
        }

        .starter-app-menu[open] .starter-app-menu-panel {
            column-gap: .45rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: .15rem;
        }

        .starter-app-menu-item {
            align-items: center;
            border-radius: var(--tblr-border-radius);
            color: var(--tblr-body-color);
            display: flex;
            gap: .55rem;
            min-height: 2.25rem;
            padding: .45rem .55rem;
            text-decoration: none;
        }

        .starter-app-menu-item:hover,
        .starter-app-menu-item:focus,
        .starter-app-menu-item.active {
            background: var(--tblr-primary-lt);
            color: var(--tblr-primary);
            text-decoration: none;
        }

        .starter-app-menu-item-icon {
            flex: 0 0 1.1rem;
            height: 1.1rem;
            width: 1.1rem;
        }

        @media (max-width: 575.98px) {
            .starter-app-menu-name {
                max-width: 8rem;
            }

            .starter-app-menu-panel {
                grid-template-columns: 1fr;
                min-width: min(18rem, calc(100vw - 1.5rem));
            }
        }

        .starter-account-panel {
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius-lg);
            box-shadow: var(--tblr-box-shadow-dropdown);
            min-width: 13.5rem;
            padding: .35rem;
            position: absolute;
            right: 0;
            top: calc(100% + .6rem);
            z-index: 1050;
        }

        .starter-account-item {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: var(--tblr-border-radius);
            color: var(--tblr-body-color);
            display: flex;
            gap: .65rem;
            padding: .55rem .65rem;
            position: relative;
            text-align: left;
            text-decoration: none;
            width: 100%;
            z-index: 1;
        }

        .starter-account-item:hover,
        .starter-account-item:focus {
            background: var(--tblr-bg-surface-secondary);
            color: var(--tblr-body-color);
            text-decoration: none;
        }

        .table-nowrap th,
        .table-nowrap td {
            white-space: nowrap;
        }

        .page-body .page-header {
            margin-top: 0;
        }

        .starter-page-body {
            transition: opacity .16s ease;
        }

        .starter-navigate-loader {
            height: .1875rem;
            left: 0;
            opacity: 0;
            overflow: hidden;
            pointer-events: none;
            position: fixed;
            right: 0;
            top: 0;
            transition: opacity .14s ease;
            z-index: 2000;
        }

        .starter-navigate-loader::before {
            animation: starter-loader-slide 1s ease-in-out infinite;
            background: linear-gradient(90deg, transparent, var(--tblr-primary), transparent);
            content: "";
            height: 100%;
            left: -45%;
            position: absolute;
            top: 0;
            width: 45%;
        }

        body.starter-is-navigating .starter-navigate-loader {
            opacity: 1;
        }

        @keyframes starter-loader-slide {
            to {
                left: 100%;
            }
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

            .starter-navigate-loader::before,
            ::view-transition-old(starter-page),
            ::view-transition-new(starter-page) {
                animation: none;
            }
        }

        .navbar-vertical {
            --tblr-navbar-bg: #182433;
            width: 100%;
        }

        @media (min-width: 992px) {
            .navbar-vertical {
                width: 15rem;
            }

            .page-wrapper {
                margin-left: 15rem;
            }
        }

        .starter-brand-image {
            height: 2rem;
            width: auto;
        }

        .starter-app-menu[open] .starter-app-menu-panel {
            column-gap: .45rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: .15rem;
        }

        .starter-account-menu[open] .starter-account-panel {
            display: block;
        }

        .starter-app-menu-panel {
            min-width: min(36rem, calc(100vw - 2rem));
        }

        .starter-account-panel {
            min-width: 13.5rem;
        }

        @media (max-width: 575.98px) {
            .starter-app-menu[open] .starter-app-menu-panel {
                grid-template-columns: 1fr;
            }

            .starter-app-menu-panel {
                min-width: min(18rem, calc(100vw - 1.5rem));
            }
        }

        .starter-account-menu summary::-webkit-details-marker,
        .starter-app-menu summary::-webkit-details-marker {
            display: none;
        }

        .starter-account-menu summary,
        .starter-app-menu summary {
            cursor: pointer;
            list-style: none;
        }

        .navbar-vertical .dropdown-menu.show {
            position: static;
        }

        .navbar-vertical .starter-sidebar-details summary {
            cursor: pointer;
            list-style: none;
            user-select: none;
        }

        .navbar-vertical .starter-sidebar-details summary::-webkit-details-marker {
            display: none;
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

        .navbar-vertical .starter-sidebar-submenu {
            display: none;
            position: static;
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

    <div class="starter-navigate-loader" aria-label="Memuat halaman" role="status"></div>
    @include('templates.components.toast')

    <div class="page">
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#starter-sidebar-menu" aria-controls="starter-sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="navbar-brand navbar-brand-autodark">
                    <a href="{{ $currentDashboardUrl }}" aria-label="{{ config('app.name') }}" data-starter-navigate>
                        <img src="{{ asset('assets/tabler/static/logo-white.svg') }}" class="navbar-brand-image starter-brand-image" alt="{{ config('app.name') }}">
                    </a>
                </div>

                <div class="navbar-nav flex-row d-lg-none ms-auto" x-persist="{{ $accountPersistBase }}-mobile">
                    @include('templates.layouts.account-menu')
                </div>

                <div class="collapse navbar-collapse" id="starter-sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="starter-sidebar-section">Menu Utama</li>

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
                                    <span class="nav-link-title">Tidak ada menu</span>
                                </span>
                            </li>
                        @endforelse

                        @if ($login?->role?->isAdmin())
                            <li class="starter-sidebar-section">Pengaturan</li>

                            @php
                                $userManagementOpen = request()->routeIs('starter.user-management.*');
                                $rolesActive = request()->routeIs('starter.user-management.roles');
                                $usersActive = request()->routeIs('starter.user-management.users');
                            @endphp

                            <li class="nav-item {{ $userManagementOpen ? 'active' : '' }}">
                                <details class="starter-sidebar-details" @if ($userManagementOpen) open @endif>
                                    <summary class="nav-link" role="button" aria-controls="starter-global-user-management">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            @include('templates.layouts.icon', ['name' => 'users-group'])
                                        </span>
                                        <span class="nav-link-title">User Management</span>
                                    </summary>
                                    <div class="dropdown-menu starter-sidebar-submenu" id="starter-global-user-management">
                                        <a href="{{ route('starter.user-management.roles') }}" class="dropdown-item {{ $rolesActive ? 'active' : '' }}" @if ($rolesActive) data-current="true" @endif data-starter-navigate data-starter-menu-url="{{ route('starter.user-management.roles') }}">
                                            @include('templates.layouts.icon', ['name' => 'shield-check', 'class' => 'icon-inline me-1'])
                                            Roles
                                        </a>
                                        <a href="{{ route('starter.user-management.users') }}" class="dropdown-item {{ $usersActive ? 'active' : '' }}" @if ($usersActive) data-current="true" @endif data-starter-navigate data-starter-menu-url="{{ route('starter.user-management.users') }}">
                                            @include('templates.layouts.icon', ['name' => 'users', 'class' => 'icon-inline me-1'])
                                            Users
                                        </a>
                                    </div>
                                </details>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </aside>

        <header class="navbar navbar-expand-lg d-none d-lg-flex d-print-none" x-persist="{{ $accountPersistBase }}-topbar">
            <div class="container-xl">
                <div class="navbar-nav flex-row order-lg-last ms-auto">
                    @include('templates.layouts.account-menu')
                </div>

                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="navbar-nav">
                        @include('templates.layouts.app-switcher', ['compact' => false])
                    </ul>
                </div>
            </div>
        </header>

        <div class="page-wrapper">
            <div class="page-body starter-page-body" wire:transition="starter-page">
                <div class="container-xl">
                    {{ $slot }}
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
    <script src="{{ asset('assets/mine/starter-runtime.js') }}" data-navigate-once defer></script>
    @livewireScripts
</body>

</html>
