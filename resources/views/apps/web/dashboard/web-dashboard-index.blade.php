<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $currentAppName ?? 'Dashboard' }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">{{ config('app.name') }}</a></li>
                        <li class="breadcrumb-item active">{{ $currentAppName ?? 'Dashboard' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="avatar-sm mr-3">
                            <span class="avatar-title rounded-circle bg-primary text-white font-size-20">
                                <i class="{{ $currentAppIcon ?? 'ri-apps-line' }}"></i>
                            </span>
                        </div>
                        <div class="media-body">
                            <p class="text-muted mb-1">Aplikasi Aktif</p>
                            <h5 class="mb-0">{{ $currentAppName ?? '-' }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="avatar-sm mr-3">
                            <span class="avatar-title rounded-circle bg-success text-white font-size-20">
                                <i class="ri-shield-user-line"></i>
                            </span>
                        </div>
                        <div class="media-body">
                            <p class="text-muted mb-1">Login</p>
                            <h5 class="mb-0">{{ $loginName }} · {{ $loginRoleName }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <div class="avatar-sm mr-3">
                            <span class="avatar-title rounded-circle bg-info text-white font-size-20">
                                <i class="ri-menu-line"></i>
                            </span>
                        </div>
                        <div class="media-body">
                            <p class="text-muted mb-1">Akses</p>
                            <h5 class="mb-0">{{ $accessibleAppCount }} aplikasi · {{ $sidebarModCount }} module</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Aplikasi Yang Bisa Diakses</h4>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Aplikasi</th>
                                    <th>Subdomain</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($appOptions as $appOption)
                                    <tr>
                                        <td>
                                            <i class="{{ $appOption['icon'] ?? 'ri-apps-line' }} mr-2 text-primary"></i>
                                            {{ $appOption['name'] }}
                                        </td>
                                        <td>{{ $appOption['subdomain'] }}</td>
                                        <td class="text-right">
                                            <a href="{{ $appOption['url'] }}" class="btn btn-sm btn-outline-primary" onclick="window.StarterTemplate.navigate(this.href); return false;">
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
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Menu Aktif</h4>
                    <ul class="list-unstyled mb-0">
                        @foreach ($sidebarMods as $mod)
                            <li class="mb-3">
                                <strong>{{ $mod['name'] }}</strong>
                                <div class="text-muted small">{{ $mod['menuLabels'] ?: 'Belum ada menu' }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
