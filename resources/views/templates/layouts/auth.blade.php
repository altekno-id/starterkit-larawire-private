<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="starter-auth-login-url" content="{{ \App\Support\Starter\StarterNavigation::authLoginUrl() }}">
    <title>{{ $title ?? 'Login' }} | {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/tabler/static/logo-small.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/dist/css/tabler.min.css') }}">
    <style>
        .starter-auth-mark {
            align-items: center;
            background: var(--tblr-primary);
            border-radius: 1rem;
            color: var(--tblr-primary-fg);
            display: inline-flex;
            font-size: 1.5rem;
            font-weight: 700;
            height: 3.25rem;
            justify-content: center;
            width: 3.25rem;
        }
    </style>
    @livewireStyles
</head>

<body class="d-flex flex-column bg-body-tertiary">
    <script src="{{ asset('assets/tabler/dist/js/tabler-theme.min.js') }}"></script>
    @include('templates.components.toast')

    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none" wire:navigate>
                    <span class="starter-auth-mark">{{ str(config('app.name'))->substr(0, 1)->upper() }}</span>
                </a>
            </div>

            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-2">Login to Account</h2>
                    <p class="text-secondary text-center mb-4">Use username or email to continue.</p>
                    {{ $slot }}
                </div>
            </div>

            <div class="text-center text-secondary mt-3">
                {{ now()->year }} © {{ config('app.name') }}
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/tabler/dist/js/tabler.min.js') }}" defer></script>
    <script src="{{ asset('assets/mine/starter-runtime.js') }}?v={{ filemtime(public_path('assets/mine/starter-runtime.js')) }}" data-navigate-once defer></script>
    @livewireScripts
</body>

</html>
