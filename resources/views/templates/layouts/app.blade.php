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

        .starter-app-switcher.dropdown-mega {
            position: relative !important;
        }

        .starter-app-megamenu {
            left: 0 !important;
            min-width: 320px;
            padding: 1.25rem 1.5rem;
            right: auto !important;
            width: 320px;
        }

        .starter-app-menu-list li a {
            align-items: center;
            border-radius: 4px;
            display: flex;
            gap: .75rem;
            padding: .55rem .65rem;
        }

        .starter-app-menu-list li a:hover,
        .starter-app-menu-list li a.active {
            background-color: #f1f5f7;
            color: #5664d2;
        }

        .starter-app-menu-list li a i {
            font-size: 1.25rem;
            line-height: 1;
        }

        .starter-app-menu-list small {
            color: #74788d;
            display: block;
            line-height: 1.1;
        }

        .starter-app-mobile-menu {
            min-width: 280px;
        }

        .starter-app-mobile-menu .dropdown-icon-item i {
            display: block;
            font-size: 24px;
            line-height: 24px;
        }

        .starter-app-mobile-menu .dropdown-icon-item.active {
            background-color: #f1f5f7;
            border-color: #5664d2;
            color: #5664d2;
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
                        <a href="{{ $currentDashboardUrl }}" class="logo logo-dark" data-starter-navigate>
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-sm-dark.png') }}" alt="{{ config('app.name') }}" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo-dark.png') }}" alt="{{ config('app.name') }}" height="20">
                            </span>
                        </a>

                        <a href="{{ $currentDashboardUrl }}" class="logo logo-light" data-starter-navigate>
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

                    @include('templates.layouts.app-switcher')
                </div>

                <div class="d-flex">
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
                            <a href="{{ $currentProfileUrl }}" class="dropdown-item" data-starter-navigate>
                                <i class="ri-user-settings-line align-middle mr-1"></i> Edit My Profile
                            </a>
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
            coreBound: false,
            normalizeUrl(url) {
                const parsed = new URL(url, window.location.href);
                parsed.hash = '';
                parsed.search = '';
                parsed.pathname = parsed.pathname.replace(/\/+$/, '') || '/';

                return parsed.href;
            },
            isSameUrl(url, compareUrl = window.location.href) {
                return this.normalizeUrl(url) === this.normalizeUrl(compareUrl);
            },
            navigate(url) {
                if (! url) return;

                const target = new URL(url, window.location.href);
                const current = new URL(window.location.href);

                if (this.isSameUrl(target.href, current.href)) {
                    return;
                }

                if (target.origin === current.origin && window.Livewire && typeof window.Livewire.navigate === 'function') {
                    window.Livewire.navigate(target.href);
                    return;
                }

                window.location.assign(target.href);
            },
            bindCore() {
                if (this.coreBound) {
                    return;
                }

                document.addEventListener('click', (event) => this.handleDocumentClick(event), true);
                this.coreBound = true;
            },
            handleDocumentClick(event) {
                const navLink = event.target.closest('a[data-starter-navigate]');

                if (navLink) {
                    if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || navLink.target === '_blank') {
                        return;
                    }

                    event.preventDefault();
                    this.closeDropdowns();
                    this.navigate(navLink.href);
                    return;
                }

                const dropdownToggle = event.target.closest('#page-topbar [data-toggle="dropdown"]');

                if (dropdownToggle) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.toggleDropdown(dropdownToggle);
                    return;
                }

                const sidebarToggle = event.target.closest('#sidebar-menu a.has-arrow');

                if (sidebarToggle && ! (window.jQuery && window.jQuery.fn && window.jQuery.fn.metisMenu)) {
                    event.preventDefault();
                    this.toggleSidebarGroup(sidebarToggle);
                    return;
                }

                if (! event.target.closest('#page-topbar .dropdown')) {
                    this.closeDropdowns();
                }
            },
            toggleDropdown(toggle) {
                const dropdown = toggle.closest('.dropdown');
                const menu = dropdown ? dropdown.querySelector('.dropdown-menu') : null;

                if (! menu) {
                    return;
                }

                const willShow = ! menu.classList.contains('show');

                this.closeDropdowns(dropdown);
                menu.classList.toggle('show', willShow);
                toggle.setAttribute('aria-expanded', willShow ? 'true' : 'false');
            },
            closeDropdowns(exceptDropdown = null) {
                document.querySelectorAll('#page-topbar .dropdown').forEach((dropdown) => {
                    if (exceptDropdown && dropdown === exceptDropdown) {
                        return;
                    }

                    dropdown.querySelectorAll('.dropdown-menu.show').forEach((menu) => menu.classList.remove('show'));
                    dropdown.querySelectorAll('[data-toggle="dropdown"][aria-expanded="true"]').forEach((toggle) => toggle.setAttribute('aria-expanded', 'false'));
                });
            },
            resetSidebar(sideMenu) {
                sideMenu.querySelectorAll('a').forEach((link) => {
                    link.classList.remove('active');
                    link.removeAttribute('data-current');
                    link.setAttribute('aria-expanded', 'false');
                });

                sideMenu.querySelectorAll('li').forEach((item) => item.classList.remove('mm-active'));
                sideMenu.querySelectorAll('ul').forEach((submenu) => {
                    submenu.classList.remove('mm-show', 'mm-collapsing');
                    submenu.classList.add('mm-collapse');
                    submenu.style.height = '';
                });
            },
            activateSidebar() {
                const sideMenu = document.querySelector('#side-menu');

                if (! sideMenu) {
                    return;
                }

                this.resetSidebar(sideMenu);

                const activeLink = Array.from(sideMenu.querySelectorAll('a[href]:not([href^="javascript"])'))
                    .find((link) => this.isSameUrl(link.href));

                if (! activeLink) {
                    return;
                }

                activeLink.classList.add('active');
                activeLink.setAttribute('data-current', 'true');

                let item = activeLink.closest('li');

                while (item && sideMenu.contains(item)) {
                    item.classList.add('mm-active');

                    const submenu = item.parentElement;

                    if (! submenu || submenu === sideMenu || submenu.id === 'side-menu') {
                        break;
                    }

                    submenu.classList.add('mm-show');
                    submenu.classList.remove('mm-collapse');

                    const parentItem = submenu.closest('li');
                    const parentLink = parentItem ? parentItem.querySelector(':scope > a') : null;

                    if (parentLink) {
                        parentLink.setAttribute('aria-expanded', 'true');
                    }

                    item = parentItem;
                }
            },
            toggleSidebarGroup(link) {
                const item = link.closest('li');
                const submenu = item ? item.querySelector(':scope > ul') : null;

                if (! item || ! submenu) {
                    return;
                }

                const willShow = ! submenu.classList.contains('mm-show');

                item.classList.toggle('mm-active', willShow);
                link.setAttribute('aria-expanded', willShow ? 'true' : 'false');
                submenu.classList.toggle('mm-show', willShow);
                submenu.classList.toggle('mm-collapse', ! willShow);
            },
            initMetisMenu($) {
                const $sideMenu = $('#side-menu');

                if (! $sideMenu.length || ! $.fn.metisMenu) {
                    return;
                }

                if ($sideMenu.data('metisMenu')) {
                    $sideMenu.metisMenu('dispose');
                }

                this.activateSidebar();
                $sideMenu.metisMenu();
            },
            init() {
                this.bindCore();
                this.activateSidebar();

                if (! window.jQuery) {
                    return;
                }

                const $ = window.jQuery;

                this.initMetisMenu($);

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
