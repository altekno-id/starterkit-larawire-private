<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="starter-auth-login-url" content="{{ \App\Support\Starter\StarterNavigation::authLoginUrl() }}">
    <meta name="starter-lock-screen-enabled" content="{{ $lockScreenEnabled ? '1' : '0' }}">
    <meta name="starter-lock-screen-timeout" content="{{ $lockScreenTimeoutSeconds }}">
    <meta name="starter-lock-screen-url" content="{{ $lockScreenUrl }}">
    <meta name="starter-session-activity-url" content="{{ $sessionActivityUrl }}">
    <title>{{ $title ?? ($currentAppName ?? config('app.name')) }} | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/tabler/static/logo-small.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler.min.css') }}?v={{ filemtime(public_path('assets/tabler/dist/css/tabler.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler-vendors.min.css') }}?v={{ filemtime(public_path('assets/tabler/dist/css/tabler-vendors.min.css')) }}">
    <style>
        [x-cloak] {
            display: none !important;
        }

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

        .starter-sidebar-brand {
            align-items: center;
            display: flex;
            height: 3rem;
            justify-content: center;
            padding: .25rem .75rem;
            width: 100%;
        }

        .starter-sidebar-brand-image {
            display: block;
            height: 2.5rem;
            max-height: 2.5rem;
            max-width: 10.5rem;
            object-fit: contain;
            object-position: center;
            width: 100%;
        }

        .starter-page-loader-brand-image {
            display: block;
            height: 2.25rem;
            margin-inline: auto;
            max-width: 10rem;
            object-fit: contain;
            object-position: center;
            width: 100%;
        }

        .starter-client-logo-preview {
            align-items: center;
            background: var(--tblr-bg-surface);
            border: 1px solid var(--tblr-border-color);
            border-radius: var(--tblr-border-radius);
            display: flex;
            height: 5rem;
            justify-content: center;
            overflow: hidden;
            padding: .625rem;
            width: 9rem;
        }

        .starter-client-logo-preview-image {
            display: block;
            height: 100%;
            object-fit: contain;
            object-position: center;
            width: 100%;
        }

        .input-group:has(> .input-group-text, > .btn) > .form-control.is-invalid {
            background-image: none;
            padding-right: .75rem;
        }

        .input-group:has(> .form-control.is-invalid, > .form-select.is-invalid) > .form-control.is-invalid,
        .input-group:has(> .form-control.is-invalid, > .form-select.is-invalid) > .form-select.is-invalid,
        .input-group:has(> .form-control.is-invalid, > .form-select.is-invalid) > .input-group-text,
        .input-group:has(> .form-control.is-invalid, > .form-select.is-invalid) > .btn {
            border-color: var(--tblr-form-invalid-border-color) !important;
        }

        .input-group-flat:has(> .form-control.is-invalid, > .form-select.is-invalid):focus-within {
            box-shadow: 0 0 0 .25rem rgba(var(--tblr-danger-rgb), .25);
        }

        .starter-account-menu summary::-webkit-details-marker,
        .navbar-vertical .starter-sidebar-details summary::-webkit-details-marker {
            display: none;
        }

        .starter-account-panel {
            min-width: 13.5rem;
        }

        .starter-role-access-trigger {
            appearance: none;
            color: inherit;
            font: inherit;
            line-height: 1.25;
        }

        .starter-role-access-trigger:hover,
        .starter-role-access-trigger:active {
            background: transparent !important;
            color: inherit;
        }

        .starter-role-access-trigger:hover .starter-role-access-title {
            color: var(--tblr-primary);
        }

        .starter-role-access-trigger:focus-visible {
            border-radius: var(--tblr-border-radius);
            outline: 2px solid color-mix(in srgb, var(--tblr-primary) 35%, transparent);
            outline-offset: 4px;
        }

        .starter-table-action-link {
            appearance: none;
            background: transparent;
            border: 0;
            color: var(--tblr-primary);
            font: inherit;
            padding: 0;
        }

        .starter-table-action-link:hover,
        .starter-table-action-link:active {
            background: transparent !important;
            color: color-mix(in srgb, var(--tblr-primary) 78%, var(--tblr-body-color));
            text-decoration: underline;
        }

        .starter-table-action-link:focus-visible {
            border-radius: .125rem;
            outline: 2px solid color-mix(in srgb, var(--tblr-primary) 35%, transparent);
            outline-offset: 3px;
        }

        .starter-page-body {
            transition: opacity .16s ease;
        }

        .starter-content-container {
            max-width: 1680px;
            width: 100%;
        }

        .starter-navigate-loader,
        .starter-livewire-loader {
            background: color-mix(in srgb, var(--tblr-bg-surface) 88%, transparent);
            bottom: var(--starter-loader-bottom, 0);
            left: var(--starter-loader-left, 0);
            opacity: 0;
            pointer-events: none;
            position: fixed;
            right: var(--starter-loader-right, 0);
            top: var(--starter-loader-top, 0);
            transition: opacity .14s ease;
            z-index: 20;
        }

        .starter-livewire-loader {
            background: color-mix(in srgb, var(--tblr-bg-surface) 68%, transparent);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 1045;
        }

        .starter-livewire-loader-card {
            min-width: 10rem;
        }

        [data-starter-livewire-loading] {
            filter: blur(2px);
            opacity: .55;
            pointer-events: none;
            transition: filter .14s ease, opacity .14s ease;
            user-select: none;
        }

        .starter-page-loader {
            transform: translateY(-.75rem);
            width: min(16rem, calc(100vw - 2rem));
        }

        .modal-backdrop {
            --tblr-backdrop-opacity: 0.4;
        }

        .modal-blur {
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        body.starter-is-navigating .starter-navigate-loader {
            opacity: 1;
            pointer-events: auto;
        }

        body.starter-is-navigating .starter-slot-area > [wire\:id] {
            filter: blur(2px);
            opacity: .55;
            pointer-events: none;
        }

        body.starter-livewire-is-loading .starter-livewire-loader {
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
            .starter-navigate-loader,
            .starter-livewire-loader,
            [data-starter-livewire-loading] {
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
    @stack('page-styles')
    @livewireStyles
</head>

<body data-starter-app-shell>
    <script src="{{ asset('assets/tabler/dist/js/tabler-theme.min.js') }}?v={{ filemtime(public_path('assets/tabler/dist/js/tabler-theme.min.js')) }}"></script>

    @php
        $accountPersistBase = 'starter-account-'.($login?->getKey() ?? 'guest');
        $defaultBrandLogoUrl = asset('assets/tabler/static/logo-white.svg');
        $brandLogoUrl = $clientLogoUrl ?: $defaultBrandLogoUrl;
        $brandLogoAlt = $clientLogoUrl ? ($clientName ?: config('app.name')) : config('app.name');
    @endphp

    @include('starter.templates.components.toast')

    <div class="page">
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#starter-sidebar-menu" aria-controls="starter-sidebar-menu" aria-expanded="false" aria-label="Buka atau tutup navigasi">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="navbar-brand navbar-brand-autodark">
                    <a href="{{ $currentDashboardUrl }}" class="starter-sidebar-brand" aria-label="{{ $brandLogoAlt }}" data-starter-navigate>
                        <img
                            src="{{ $brandLogoUrl }}"
                            class="starter-sidebar-brand-image"
                            alt="{{ $brandLogoAlt }}"
                            data-starter-brand-logo
                            data-fallback-src="{{ $defaultBrandLogoUrl }}"
                            @if ($clientLogoUrl) data-company-logo="true" @endif
                        >
                    </a>
                </div>

                <div class="navbar-nav flex-row d-lg-none ms-auto align-items-center gap-2" x-persist="{{ $accountPersistBase }}-mobile">
                    @include('starter.templates.layouts.app-switcher', ['compact' => true])
                    @include('starter.templates.layouts.account-menu')
                </div>

                <div class="collapse navbar-collapse" id="starter-sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item d-lg-none px-0 pt-3 pb-2">
                            <div class="small text-secondary">App Aktif</div>
                            <div class="fw-semibold text-truncate" data-starter-current-app-name>{{ $currentAppName ?? 'App' }}</div>
                        </li>

                        <li class="nav-item px-0 px-lg-3 pt-3 pb-1">
                            <span class="subheader">Menu Utama</span>
                        </li>

                        @forelse ($sidebarMods as $mod)
                            @foreach ($mod['menus'] as $menu)
                                @include('starter.templates.layouts.menu-item', ['menu' => $menu])
                            @endforeach
                        @empty
                            <li class="nav-item">
                                <span class="nav-link disabled">
                                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                                        @include('starter.templates.layouts.icon', ['name' => 'circle'])
                                    </span>
                                        <span class="nav-link-title">Belum ada menu</span>
                                </span>
                            </li>
                        @endforelse

                    </ul>
                </div>
            </div>
        </aside>

        <header class="navbar navbar-expand-lg d-none d-lg-flex d-print-none" x-persist="{{ $accountPersistBase }}-topbar">
            <div class="container-fluid starter-content-container">
                <div class="d-none d-lg-flex flex-column lh-sm me-auto">
                    <span class="small text-secondary">App Aktif</span>
                    <span class="fw-semibold text-truncate" data-starter-current-app-name>{{ $currentAppName ?? 'App' }}</span>
                </div>

                <div class="navbar-nav flex-row order-lg-last ms-auto align-items-center">
                    @include('starter.templates.layouts.app-switcher', ['compact' => false])
                    @include('starter.templates.layouts.account-menu')
                </div>
            </div>
        </header>

        <div class="page-wrapper">
            <div class="page-body starter-page-body" wire:transition="starter-page">
                <div class="container-fluid starter-content-container">
                    <div class="starter-slot-area position-relative">
                        {{ $slot }}

                        <div class="starter-livewire-loader d-flex align-items-center justify-content-center rounded" data-starter-livewire-loader aria-label="Memproses permintaan" aria-hidden="true" role="status">
                            <div class="card card-sm shadow starter-livewire-loader-card">
                                <div class="card-body d-flex align-items-center justify-content-center gap-3">
                                    <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
                                    <span class="fw-semibold">Memproses...</span>
                                </div>
                            </div>
                        </div>

                        <div class="starter-navigate-loader d-flex align-items-center justify-content-center rounded" aria-label="Memuat..." role="status">
                            <div class="starter-page-loader text-center">
                                <div class="mb-3">
                                    <span class="navbar-brand navbar-brand-autodark justify-content-center">
                                        <img
                                            src="{{ $brandLogoUrl }}"
                                            class="starter-page-loader-brand-image"
                                            alt="{{ $brandLogoAlt }}"
                                            data-starter-brand-logo
                                            data-fallback-src="{{ $defaultBrandLogoUrl }}"
                                            @if ($clientLogoUrl) data-company-logo="true" @endif
                                        >
                                    </span>
                                </div>
                                <div class="text-secondary mb-3">Memuat...</div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar progress-bar-indeterminate"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer footer-transparent d-print-none">
                <div class="container-fluid starter-content-container">
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

    <script src="{{ asset('assets/tabler/dist/js/tabler.min.js') }}?v={{ filemtime(public_path('assets/tabler/dist/js/tabler.min.js')) }}" defer></script>
    <script src="{{ asset('assets/starter/js/starter-runtime.js') }}?v={{ filemtime(public_path('assets/starter/js/starter-runtime.js')) }}" data-navigate-once defer></script>
    @livewireScripts
    @stack('page-scripts')
</body>

</html>
