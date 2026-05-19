@php
    $compact = $compact ?? false;
    $activeApp = $appOptions->firstWhere('active', true);
    $triggerLabel = $triggerLabel ?? ($activeApp['name'] ?? $currentAppName ?? 'Aplikasi');
    $triggerIcon = $triggerIcon ?? ($activeApp['icon'] ?? $currentAppIcon ?? 'apps');
@endphp

<details class="nav-item starter-app-menu {{ $compact ? 'starter-app-menu-compact' : '' }}" data-starter-details>
    <summary class="nav-link dropdown-toggle starter-app-menu-trigger" aria-label="Pilih aplikasi">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            @include('templates.layouts.icon', ['name' => $triggerIcon])
        </span>
        <span class="nav-link-title">
            {{ $triggerLabel }}
        </span>
    </summary>

    <div class="dropdown-menu starter-app-menu-panel">
        @forelse ($appOptions as $appOption)
            <a href="{{ $appOption['url'] }}" class="dropdown-item {{ $appOption['active'] ? 'active' : '' }}" data-starter-navigate>
                @include('templates.layouts.icon', ['name' => $appOption['icon'], 'class' => 'icon dropdown-item-icon'])
                <span class="min-w-0">
                    <span class="d-block text-truncate">{{ $appOption['name'] }}</span>
                    <span class="d-block small text-secondary text-truncate">{{ $appOption['subdomain'] }}</span>
                </span>
                @if ($appOption['active'])
                    @include('templates.layouts.icon', ['name' => 'check', 'class' => 'ms-auto text-primary icon'])
                @endif
            </a>
        @empty
            <span class="dropdown-item text-secondary">Tidak ada aplikasi</span>
        @endforelse
    </div>
</details>
