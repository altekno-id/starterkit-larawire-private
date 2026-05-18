<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? ($currentAppName ?? config('app.name')) }} | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">
    <style>
        #sidebar-menu a[data-current] {
            color: #ffffff;
        }

        #sidebar-menu a[data-current] i {
            color: #ffffff;
        }

        .starter-app-select {
            min-width: 190px;
        }
    </style>
    @livewireStyles
</head>

<body data-sidebar="dark">
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <div class="navbar-brand-box">
                        <a href="{{ $currentDashboardUrl }}" class="logo logo-dark" wire:navigate>
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-sm-dark.png') }}" alt="{{ config('app.name') }}" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="{{ config('app.name') }}" height="20">
                            </span>
                        </a>

                        <a href="{{ $currentDashboardUrl }}" class="logo logo-light" wire:navigate>
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-sm-light.png') }}" alt="{{ config('app.name') }}" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo-light.png') }}" alt="{{ config('app.name') }}" height="20">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
                        <i class="ri-menu-2-line align-middle"></i>
                    </button>

                    <div class="app-search d-none d-lg-block">
                        <div class="position-relative">
                            <select class="form-control starter-app-select" onchange="window.StarterTemplate.navigate(this.value)">
                                @foreach ($appOptions as $appOption)
                                    <option value="{{ $appOption['url'] }}" @selected($appOption['active'])>
                                        {{ $appOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="ri-apps-2-line"></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="dropdown d-inline-block d-lg-none ml-2">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ri-apps-2-line"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-3">
                            <select class="form-control" onchange="window.StarterTemplate.navigate(this.value)">
                                @foreach ($appOptions as $appOption)
                                    <option value="{{ $appOption['url'] }}" @selected($appOption['active'])>
                                        {{ $appOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="dropdown d-none d-lg-inline-block ml-1">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                            <i class="ri-fullscreen-line"></i>
                        </button>
                    </div>

                    <div class="dropdown d-inline-block user-dropdown">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-2.jpg') }}" alt="{{ $loginName }}">
                            <span class="d-none d-xl-inline-block ml-1">{{ $loginName }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <span class="dropdown-item-text text-muted small">
                                {{ $loginEmail }}
                            </span>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ url()->current() }}">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="ri-shut-down-line align-middle mr-1 text-danger"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        <li class="menu-title">{{ $currentAppName ?? 'Menu' }}</li>

                        @forelse ($sidebarMods as $mod)
                            @foreach ($mod['menus'] as $menu)
                                @include('templates.layouts.menu-item', ['menu' => $menu])
                            @endforeach
                        @empty
                            <li>
                                <a href="javascript:void(0);" class="waves-effect">
                                    <i class="ri-lock-line"></i>
                                    <span>Tidak ada menu</span>
                                </a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            {{ now()->year }} © {{ config('app.name') }}.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-right d-none d-sm-block">
                                {{ $currentAppName ?? 'Starter' }}
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script>
        window.StarterTemplate = {
            navigate(url) {
                if (! url) return;

                const target = new URL(url, window.location.href);

                if (target.origin === window.location.origin && window.Livewire && typeof window.Livewire.navigate === 'function') {
                    window.Livewire.navigate(target.href);
                    return;
                }

                window.location.assign(target.href);
            },
            init() {
                if (! window.jQuery) return;

                const $ = window.jQuery;
                const $sideMenu = $('#side-menu');

                if ($sideMenu.length && $.fn.metisMenu) {
                    $sideMenu.metisMenu();
                }

                $('#vertical-menu-btn').off('click.starter').on('click.starter', function (event) {
                    event.preventDefault();
                    $('body').toggleClass('sidebar-enable');

                    if ($(window).width() >= 992) {
                        $('body').toggleClass('vertical-collpsed');
                    } else {
                        $('body').removeClass('vertical-collpsed');
                    }
                });

                $('body,html').off('click.starter-sidebar').on('click.starter-sidebar', function (event) {
                    const $button = $('#vertical-menu-btn');

                    if ($button.is(event.target) || $button.has(event.target).length || event.target.closest('div.vertical-menu')) {
                        return;
                    }

                    $('body').removeClass('sidebar-enable');
                });

                $('#sidebar-menu a').removeClass('active');
                $('#sidebar-menu li').removeClass('mm-active');
                $('#sidebar-menu ul').removeClass('mm-show');
                $('#sidebar-menu a').each(function () {
                    const current = window.location.href.split(/[?#]/)[0];

                    if (this.href === current) {
                        $(this).addClass('active');
                        $(this).parent().addClass('mm-active');
                        $(this).parent().parent().addClass('mm-show');
                        $(this).parent().parent().prev().addClass('mm-active');
                    }
                });

                $('[data-toggle="tooltip"]').tooltip();

                if (window.Waves) {
                    window.Waves.init();
                }
            },
        };

        document.addEventListener('livewire:navigated', () => window.StarterTemplate.init());
        document.addEventListener('DOMContentLoaded', () => window.StarterTemplate.init());
    </script>
    @livewireScripts
</body>

</html>
