<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Header halaman">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Ringkasan</div>
                <h2 class="page-title">{{ $dashboardTitle ?? 'Summary 1' }}</h2>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-primary-lt text-primary me-3">{{ str($currentAppName ?? 'A')->substr(0, 1)->upper() }}</span>
                        <div>
                            <div class="text-secondary">App Aktif</div>
                            <div class="h3 mb-0">{{ $currentAppName ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-success-lt text-success me-3">{{ str($loginName ?? 'U')->substr(0, 1)->upper() }}</span>
                        <div>
                            <div class="text-secondary">Login</div>
                            <div class="h3 mb-0">{{ $loginName }} · {{ $loginRoleName }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <span class="avatar bg-info-lt text-info me-3">{{ $accessibleAppCount }}</span>
                        <div>
                            <div class="text-secondary">Akses</div>
                            <div class="h3 mb-0">{{ $accessibleAppCount }} app · {{ $sidebarModCount }} module</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">App yang Dapat Diakses</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-nowrap card-table">
                        <thead>
                            <tr>
                                <th>App</th>
                                <th>Subdomain</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appOptions as $appOption)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2">{{ str($appOption['name'])->substr(0, 1)->upper() }}</span>
                                            <span>{{ $appOption['name'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $appOption['subdomain'] }}</td>
                                    <td class="text-end">
                                        <a href="{{ $appOption['url'] }}" class="btn btn-sm btn-outline-primary" data-starter-navigate>
                                            Buka
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Menu Aktif</h3>
                </div>
                <div class="list-group list-group-flush">
                    @foreach ($sidebarMods as $mod)
                        <div class="list-group-item">
                            <div class="fw-semibold">{{ $mod['name'] }}</div>
                            <div class="text-secondary small">{{ $mod['menuLabels'] ?: 'Belum ada menu' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
