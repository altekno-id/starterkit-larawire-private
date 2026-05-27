<details class="nav-item dropdown starter-account-menu {{ $class ?? '' }}" data-starter-details>
    <summary class="nav-link d-flex lh-1 p-0 px-2 starter-account-summary" aria-label="Open user menu" data-starter-account-summary>
        <span class="avatar avatar-sm starter-account-avatar" style="background-image: url({{ $loginAvatarUrl }})" data-starter-account-avatar></span>
        <div class="d-none d-xl-block ps-2">
            <div data-starter-account-name>{{ $loginName ?? 'User' }}</div>
            <div class="mt-1 small text-secondary" data-starter-account-role>{{ $loginRoleName ?? 'Role' }}</div>
        </div>
    </summary>

    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow starter-account-panel">
        <a href="{{ $currentProfileUrl }}" class="dropdown-item" data-starter-navigate>
            @include('templates.layouts.icon', ['name' => 'user-circle', 'class' => 'icon dropdown-item-icon'])
            Edit Profile Saya
        </a>
        <div class="dropdown-divider my-1"></div>
        <form method="POST" action="{{ route('auth.logout') }}" data-starter-logout-form>
            @csrf
            <input type="hidden" name="redirect" value="{{ url()->current() }}" data-starter-logout-redirect>
            <button type="submit" class="dropdown-item text-danger">
                @include('templates.layouts.icon', ['name' => 'logout', 'class' => 'icon dropdown-item-icon'])
                Logout
            </button>
        </form>
    </div>
</details>
