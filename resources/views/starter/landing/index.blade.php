<div>
    <header class="navbar navbar-expand-md navbar-light bg-white border-bottom sticky-top">
        <div class="container-xl">
            <a class="navbar-brand fw-bold" href="{{ route('landing') }}" wire:navigate>
                <span class="avatar avatar-sm bg-primary text-white me-2">{{ str(config('app.name'))->substr(0, 1)->upper() }}</span>
                {{ config('app.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landing-navbar" aria-controls="landing-navbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="landing-navbar">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="#features">Features</a>
                    <a class="nav-link" href="#pricing">Pricing</a>
                    <a class="nav-link" href="#faq">FAQ</a>
                </div>
                <div class="ms-md-3 mt-3 mt-md-0">
                    <a class="btn btn-outline-primary me-2" href="{{ \App\Support\Starter\StarterNavigation::authLoginUrl() }}">Login</a>
                    <a class="btn btn-primary" href="{{ \App\Support\Starter\StarterNavigation::authUrl('register?package=trial') }}">Start Trial</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="py-6 py-lg-7">
            <div class="container-xl">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <span class="badge bg-primary-lt text-primary mb-3">Starterpack SaaS & Private Apps</span>
                        <h1 class="display-4 fw-bold mb-3">Build multi-app client portals faster.</h1>
                        <p class="lead text-secondary mb-4">A Laravel starterpack for SaaS multi-client projects or private internal apps, with auth, roles, app switcher, and dynamic module access ready to extend.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary btn-lg" href="{{ \App\Support\Starter\StarterNavigation::authUrl('register?package=trial') }}">Start Trial</a>
                            <a class="btn btn-outline-secondary btn-lg" href="#pricing">View Pricing</a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="card bg-primary-lt border-0">
                                            <div class="card-body">
                                                <div class="h1 text-primary mb-1">2</div>
                                                <div class="text-secondary">Sample apps</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-green-lt border-0">
                                            <div class="card-body">
                                                <div class="h1 text-green mb-1">Role</div>
                                                <div class="text-secondary">Dynamic access</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card border-0 bg-body-tertiary">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="avatar bg-primary text-white me-3">A1</span>
                                                    <div>
                                                        <div class="fw-semibold">App 1 Dashboard</div>
                                                        <div class="text-secondary small">Landing page per role and app</div>
                                                    </div>
                                                </div>
                                                <div class="progress mb-3">
                                                    <div class="progress-bar" style="width: 72%"></div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col">
                                                        <span class="status status-blue status-lite w-100">Auth</span>
                                                    </div>
                                                    <div class="col">
                                                        <span class="status status-green status-lite w-100">Roles</span>
                                                    </div>
                                                    <div class="col">
                                                        <span class="status status-purple status-lite w-100">Apps</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-5 bg-body-tertiary" id="features">
            <div class="container-xl">
                <div class="row g-4">
                    @foreach ([
                        ['title' => 'Multi Client', 'text' => 'Separate client profiles, logins, and roles for public SaaS or single-client installs.'],
                        ['title' => 'Multi App', 'text' => 'Manual app route files are synced into module, route, and menu metadata.'],
                        ['title' => 'Role Landing', 'text' => 'Each role can define landing pages per app using user-friendly menu choices.'],
                    ] as $feature)
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h3 class="card-title">{{ $feature['title'] }}</h3>
                                    <p class="text-secondary mb-0">{{ $feature['text'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="py-6" id="pricing">
            <div class="container-xl">
                <div class="text-center mb-5">
                    <h2 class="h1">Packages</h2>
                    <p class="text-secondary">Flexible package options for SaaS clients or private application installs.</p>
                </div>
                <div class="row g-4 justify-content-center">
                    @forelse ($packages as $package)
                        <div class="col-md-6 col-xl-3">
                            <div class="card h-100 {{ $package->code === 'business' ? 'border-primary' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                        <div>
                                            <h3 class="mb-1">{{ $package->name }}</h3>
                                            <span class="badge bg-primary-lt text-primary text-uppercase">{{ $package->type }}</span>
                                        </div>
                                        @if ($package->code === 'business')
                                            <span class="badge bg-primary">Popular</span>
                                        @endif
                                    </div>
                                    <div class="display-6 fw-bold mb-1">{{ $package->priceLabel() }}</div>
                                    <div class="text-secondary small mb-3">{{ $package->billingLabel() }}</div>
                                    <p class="text-secondary">{{ $package->desc }}</p>
                                    <div class="mb-3">
                                        <div class="small fw-semibold text-secondary text-uppercase mb-2">Included</div>
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($package->features ?? [] as $feature)
                                                <li class="d-flex gap-2 mb-2">
                                                    <span class="text-primary">@include('templates.layouts.icon', ['name' => 'check', 'class' => 'icon icon-sm'])</span>
                                                    <span>{{ $feature }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="small text-secondary mb-3">{{ $package->setupFeeLabel() }}</div>
                                    @if ($package->trial_days)
                                        <div class="small text-secondary mb-3">{{ $package->trial_days }} days trial included</div>
                                    @endif
                                    <a href="{{ \App\Support\Starter\StarterNavigation::authUrl('register?'.http_build_query(['package' => $package->code])) }}" class="btn {{ $package->code === 'business' ? 'btn-primary' : 'btn-outline-primary' }} w-100">Choose Package</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-lg-6">
                            <div class="empty">
                                <div class="empty-title">No packages available</div>
                                <p class="empty-subtitle text-secondary">Run the starter sync command to publish default package data.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="py-5 bg-body-tertiary" id="faq">
            <div class="container-xl">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <h2 class="h1">FAQ</h2>
                    </div>
                    <div class="col-lg-8">
                        <div class="accordion accordion-separated" id="landing-faq">
                            @foreach ([
                                ['q' => 'Can this be used for private apps?', 'a' => 'Yes. The same structure works for one client with multiple internal apps and dynamic roles.'],
                                ['q' => 'Can this be used for SaaS?', 'a' => 'Yes. Clients, logins, roles, and app access are separated so the project can grow into SaaS flows.'],
                            ] as $index => $item)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="faq-heading-{{ $index }}">
                                        <button class="accordion-button {{ $index ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $index }}" aria-expanded="{{ $index ? 'false' : 'true' }}">
                                            {{ $item['q'] }}
                                        </button>
                                    </h2>
                                    <div id="faq-collapse-{{ $index }}" class="accordion-collapse collapse {{ $index ? '' : 'show' }}" data-bs-parent="#landing-faq">
                                        <div class="accordion-body text-secondary">{{ $item['a'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-4 border-top">
        <div class="container-xl d-flex flex-column flex-md-row justify-content-between gap-2 text-secondary">
            <span>{{ now()->year }} © {{ config('app.name') }}</span>
            <span>Root landing for SaaS and private app deployments.</span>
        </div>
    </footer>
</div>
