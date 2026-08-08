<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="starter-auth-login-url" content="{{ \Altekno\StarterKit\Support\Starter\StarterNavigation::authLoginUrl() }}">
    <meta name="starter-lock-screen-enabled" content="{{ $lockScreenEnabled ? '1' : '0' }}">
    <meta name="starter-lock-screen-timeout" content="{{ $lockScreenTimeoutSeconds }}">
    <meta name="starter-lock-screen-url" content="{{ $lockScreenUrl }}">
    <meta name="starter-session-activity-url" content="{{ $sessionActivityUrl }}">
    <title>{{ $title ?? ($currentAppName ?? config('app.name')) }} | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/tabler/static/logo-small.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler.min.css') }}?v={{ filemtime(public_path('assets/tabler/dist/css/tabler.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler-vendors.min.css') }}?v={{ filemtime(public_path('assets/tabler/dist/css/tabler-vendors.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/starter/vendor/flatpickr/flatpickr.min.css') }}?v={{ filemtime(public_path('assets/starter/vendor/flatpickr/flatpickr.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/livewire-powergrid/bootstrap5.css') }}?v={{ filemtime(public_path('vendor/livewire-powergrid/bootstrap5.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/starter/css/starter.css') }}?v={{ file_exists(public_path('assets/starter/css/starter.css')) ? filemtime(public_path('assets/starter/css/starter.css')) : time() }}">
    @includeIf('extensions.starter.layout.head')
    @stack('page-styles')
    @livewireStyles
</head>

@php
    $starterLayout = \Altekno\StarterKit\Support\Starter\StarterTheme::layout();
@endphp

<body data-starter-app-shell data-starter-layout="{{ $starterLayout }}">
    <script src="{{ asset('assets/tabler/dist/js/tabler-theme.min.js') }}?v={{ filemtime(public_path('assets/tabler/dist/js/tabler-theme.min.js')) }}"></script>

    @php
        $accountPersistBase = 'starter-account-'.($login?->getKey() ?? 'guest');
        $defaultBrandLogoUrl = asset('assets/tabler/static/logo-white.svg');
        $brandLogoUrl = $clientLogoUrl ?: $defaultBrandLogoUrl;
        $brandLogoAlt = $clientLogoUrl ? ($clientName ?: config('app.name')) : config('app.name');
    @endphp

    @include('starter.templates.components.toast')

    <div class="page starter-layout-{{ $starterLayout }}">
        @if ($starterLayout === 'horizontal')
            <header class="navbar navbar-expand-md navbar-overlap sticky-top d-print-none starter-navbar-horizontal" data-bs-theme="dark">
                <div class="container-fluid starter-content-container">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#starter-horizontal-menu" aria-controls="starter-horizontal-menu" aria-expanded="false" aria-label="Buka atau tutup navigasi">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="navbar-brand navbar-brand-autodark me-2 me-md-3">
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

                    <div class="navbar-nav flex-row order-md-last ms-auto align-items-center gap-2" x-persist="{{ $accountPersistBase }}-horizontal">
                        @includeIf('extensions.starter.header-actions.index', ['compact' => true])
                        @include('starter.templates.layouts.app-switcher', ['compact' => true])
                        @include('starter.templates.layouts.account-menu')
                    </div>

                    <div class="collapse navbar-collapse" id="starter-horizontal-menu" data-starter-navigation>
                        <ul class="navbar-nav">
                            <li class="nav-item d-md-none px-2 pt-3 pb-2">
                                <div class="small text-secondary">App Aktif</div>
                                <div class="fw-semibold text-truncate" data-starter-current-app-name>{{ $currentAppName ?? 'App' }}</div>
                            </li>

                            @forelse ($sidebarMods as $mod)
                                @foreach ($mod['menus'] as $menu)
                                    @include('starter.templates.layouts.menu-item-horizontal', ['menu' => $menu])
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
            </header>
        @else
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
                    @includeIf('extensions.starter.header-actions.index', ['compact' => true])
                    @include('starter.templates.layouts.app-switcher', ['compact' => true])
                    @include('starter.templates.layouts.account-menu')
                </div>

                <div class="collapse navbar-collapse" id="starter-sidebar-menu" data-starter-navigation>
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
                    @includeIf('extensions.starter.header-actions.index', ['compact' => false])
                    @include('starter.templates.layouts.app-switcher', ['compact' => false])
                    @include('starter.templates.layouts.account-menu')
                </div>
            </div>
        </header>
        @endif

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
    <script src="{{ asset('assets/starter/vendor/flatpickr/flatpickr.min.js') }}?v={{ filemtime(public_path('assets/starter/vendor/flatpickr/flatpickr.min.js')) }}" data-navigate-once defer></script>
    <script src="{{ asset('vendor/livewire-powergrid/powergrid.js') }}?v={{ filemtime(public_path('vendor/livewire-powergrid/powergrid.js')) }}" data-navigate-once defer></script>
    @livewireScripts
    @stack('page-scripts')
    @includeIf('extensions.starter.layout.body-end')
</body>

</html>
