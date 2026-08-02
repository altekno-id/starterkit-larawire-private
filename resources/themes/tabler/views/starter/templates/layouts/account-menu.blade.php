<details class="nav-item dropdown position-relative starter-account-menu {{ $class ?? '' }}" data-starter-details>
    <summary class="nav-link d-flex align-items-center lh-1 p-0 px-2 cursor-pointer user-select-none starter-account-summary" aria-label="Buka menu user" data-starter-account-summary>
        <span class="avatar avatar-sm flex-shrink-0 starter-account-avatar" style="background-image: url({{ $loginAvatarUrl }})" data-starter-account-avatar></span>
        <div class="d-none d-xl-block ps-2">
            <div data-starter-account-name>{{ $loginName ?? 'User' }}</div>
            <div class="mt-1 small text-secondary" data-starter-account-role>{{ $loginRoleName ?? 'Role' }}</div>
        </div>
    </summary>

    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow end-0 top-100 mt-1 starter-account-panel">
        <a href="{{ $currentProfileUrl }}" class="dropdown-item" data-starter-navigate>
            @include('starter.templates.layouts.icon', ['name' => 'user-circle', 'class' => 'icon dropdown-item-icon'])
            Edit Profil Saya
        </a>
        @if ($login?->role?->canManageSettings())
            <a href="{{ route('starter.settings') }}" class="dropdown-item" data-starter-navigate>
                @include('starter.templates.layouts.icon', ['name' => 'settings', 'class' => 'icon dropdown-item-icon'])
                Pengaturan
            </a>
        @endif
        @if ($login?->role?->canViewLogs())
            <a href="{{ route('starter.logs.index') }}" class="dropdown-item" data-starter-navigate>
                @include('starter.templates.layouts.icon', ['name' => 'history', 'class' => 'icon dropdown-item-icon'])
                Log Aktivitas
            </a>
        @endif
        @includeIf('extensions.starter.profile-menu.index')
        <div class="dropdown-divider my-1"></div>
        @if ($lockScreenEnabled ?? false)
            <a href="{{ route('starter.lock-screen', ['manual' => 1]) }}" class="dropdown-item" data-starter-navigate>
                @include('starter.templates.layouts.icon', ['name' => 'lock', 'class' => 'icon dropdown-item-icon'])
                Kunci Layar
            </a>
        @endif
        <form method="POST" action="{{ route('auth.logout') }}" data-starter-logout-form>
            @csrf
            <input type="hidden" name="redirect" value="{{ url()->current() }}" data-starter-logout-redirect>
            <button type="submit" class="dropdown-item text-danger">
                @include('starter.templates.layouts.icon', ['name' => 'logout', 'class' => 'icon dropdown-item-icon'])
                Logout
            </button>
        </form>
    </div>
</details>
