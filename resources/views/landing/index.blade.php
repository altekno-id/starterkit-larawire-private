<div>
    <header class="navbar navbar-expand-md navbar-light bg-white border-bottom">
        <div class="container-xl">
            <a class="navbar-brand fw-bold" href="{{ route('landing') }}" wire:navigate>
                <span class="avatar avatar-sm bg-primary text-white me-2">{{ str(config('app.name'))->substr(0, 1)->upper() }}</span>
                {{ config('app.name') }}
            </a>
            <a class="btn btn-primary" href="{{ \Altekno\StarterKit\Support\Starter\StarterNavigation::authLoginUrl() }}">Login</a>
        </div>
    </header>

    <main class="py-7">
        <div class="container-xl">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-primary-lt text-primary mb-3">Aplikasi Internal Perusahaan</span>
                    <h1 class="display-4 fw-bold mb-3">Satu fondasi untuk seluruh aplikasi internal.</h1>
                    <p class="lead text-secondary mb-4">Multi-subdomain, modul dan menu dinamis, serta pengaturan hak akses berbasis database. Akun hanya dibuat dan dikelola oleh administrator perusahaan.</p>
                    <a class="btn btn-primary btn-lg" href="{{ \Altekno\StarterKit\Support\Starter\StarterNavigation::authLoginUrl() }}">Masuk ke Aplikasi</a>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            @foreach ([
                                ['title' => 'Multi Subdomain', 'text' => 'Pisahkan aplikasi tanpa memisahkan autentikasi dan kontrol akses.'],
                                ['title' => 'Hak Akses Dinamis', 'text' => 'Satu role dapat diberi banyak modul sesuai kebutuhan operasional.'],
                                ['title' => 'Audit Terpusat', 'text' => 'Aktivitas tambah, ubah, dan hapus tercatat dalam satu log universal.'],
                            ] as $feature)
                                <div class="mb-4 last:mb-0">
                                    <h3 class="h4 mb-1">{{ $feature['title'] }}</h3>
                                    <p class="text-secondary mb-0">{{ $feature['text'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
