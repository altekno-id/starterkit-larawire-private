<div class="dropdown dropdown-mega d-none d-lg-block ml-2 starter-app-switcher">
    <button type="button" class="btn header-item waves-effect" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
        <i class="{{ $currentAppIcon ?? 'ri-apps-2-line' }} mr-1"></i>
        {{ $currentAppName ?? 'Aplikasi' }}
        <i class="mdi mdi-chevron-down"></i>
    </button>
    <div class="dropdown-menu dropdown-megamenu starter-app-megamenu">
        <div class="row">
            <div class="col-12">
                <h5 class="font-size-14 mt-0 mb-3">Aplikasi</h5>
            </div>

            @forelse ($appOptions as $appOption)
                <div class="col-12">
                    <ul class="list-unstyled megamenu-list starter-app-menu-list mb-0">
                        <li>
                            <a href="{{ $appOption['url'] }}" class="{{ $appOption['active'] ? 'active' : '' }}" data-starter-navigate>
                                <i class="{{ $appOption['icon'] ?? 'ri-apps-2-line' }}"></i>
                                <span>
                                    {{ $appOption['name'] }}
                                    <small>{{ $appOption['subdomain'] }}</small>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            @empty
                <div class="col-12">
                    <span class="text-muted">Tidak ada aplikasi</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="dropdown d-inline-block d-lg-none ml-2 starter-app-switcher">
    <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="{{ $currentAppIcon ?? 'ri-apps-2-line' }}"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right starter-app-mobile-menu">
        <div class="px-lg-2">
            <div class="row no-gutters">
                @forelse ($appOptions as $appOption)
                    <div class="col-4">
                        <a class="dropdown-icon-item {{ $appOption['active'] ? 'active' : '' }}" href="{{ $appOption['url'] }}" data-starter-navigate>
                            <i class="{{ $appOption['icon'] ?? 'ri-apps-2-line' }}"></i>
                            <span>{{ $appOption['name'] }}</span>
                        </a>
                    </div>
                @empty
                    <div class="col-12 px-3 py-2">
                        <span class="text-muted">Tidak ada aplikasi</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
