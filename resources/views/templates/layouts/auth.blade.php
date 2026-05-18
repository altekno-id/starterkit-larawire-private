<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Login' }} | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">
    @livewireStyles
</head>

<body class="auth-body-bg">
    <div class="home-btn d-none d-sm-block">
        <a href="{{ url('/') }}" wire:navigate><i class="mdi mdi-home-variant h2 text-white"></i></a>
    </div>

    <div>
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <div class="col-lg-4">
                    <div class="authentication-page-content p-4 d-flex align-items-center min-vh-100">
                        <div class="w-100">
                            <div class="row justify-content-center">
                                <div class="col-lg-9">
                                    <div>
                                        <div class="text-center">
                                            <a href="{{ url('/') }}" class="logo" wire:navigate>
                                                <img src="{{ asset('assets/images/logo-dark.png') }}" height="20" alt="{{ config('app.name') }}">
                                            </a>

                                            <h4 class="font-size-18 mt-4">Selamat Datang</h4>
                                            <p class="text-muted">Masuk untuk melanjutkan ke starter dashboard.</p>
                                        </div>

                                        <div class="p-2 mt-5">
                                            {{ $slot }}
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p>{{ now()->year }} © {{ config('app.name') }}.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="authentication-bg">
                        <div class="bg-overlay"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    @livewireScripts
</body>

</html>
