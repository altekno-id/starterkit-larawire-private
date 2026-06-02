@php
    $selectedRole = $roles->firstWhere('id', (int) ($userForm['role_id'] ?: 0));
    $selectedRoleAppCount = $selectedRole?->isAdmin()
        ? $appCount
        : ($selectedRole?->mods?->pluck('app_id')->filter()->unique()->count() ?? 0);
    $selectedUserProvider = $selectedUser?->last_login_provider ?: ($selectedUser?->google_id ? 'Google' : 'Password');
    $clientInitials = str($client?->name ?? 'Client')->substr(0, 2)->upper();
@endphp

<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Page header">
        <div class="row g-3 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / User Management</div>
                <h2 class="page-title">User Management</h2>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary" wire:click="newUser">
                    @include('templates.layouts.icon', ['name' => 'file-plus', 'class' => 'icon-sm me-1'])
                    Add User
                </button>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary text-white avatar">
                                @include('templates.layouts.icon', ['name' => 'users'])
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ $userCount }} users</div>
                            <div class="text-secondary">Registered login accounts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green text-white avatar">
                                @include('templates.layouts.icon', ['name' => 'circle-check'])
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ $verifiedUserCount }} verified</div>
                            <div class="text-secondary">Email verification status</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-azure text-white avatar">
                                @include('templates.layouts.icon', ['name' => 'shield-check'])
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ $roleCount }} roles</div>
                            <div class="text-secondary">{{ $appCount }} apps available</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-indigo text-white avatar">
                                @include('templates.layouts.icon', ['name' => 'world'])
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">{{ $activeUserCount }} recent</div>
                            <div class="text-secondary">{{ $googleUserCount }} Google linked</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-xl-3">
                            <label class="form-label">Search</label>
                            <input type="search" class="form-control" placeholder="Name, email, role" wire:model.live.debounce.250ms="search">
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <label class="form-label">Role</label>
                            <select class="form-select" wire:model.live="roleFilter">
                                <option value="">All roles</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <label class="form-label">Email</label>
                            <select class="form-select" wire:model.live="emailStatusFilter">
                                <option value="">All email</option>
                                <option value="verified">Verified</option>
                                <option value="unverified">Unverified</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-2">
                            <label class="form-label">Order</label>
                            <select class="form-select" wire:model.live="orderBy">
                                <option value="name_asc">Name A-Z</option>
                                <option value="name_desc">Name Z-A</option>
                                <option value="role_asc">Role A-Z</option>
                                <option value="created_desc">Newest</option>
                                <option value="created_asc">Oldest</option>
                                <option value="last_login_desc">Last login</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-xl-auto ms-xl-auto">
                            <button type="button" class="btn btn-outline-secondary w-100" wire:click="resetUserFilters">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">User Directory</h3>
                        <p class="card-subtitle">Manage login accounts, role assignment, access scope, and account activity.</p>
                    </div>
                    <div class="card-actions">
                        <span class="status status-blue status-lite">{{ $users->count() }} shown</span>
                    </div>
                </div>

                <div class="table-responsive overflow-visible">
                    <table class="table table-vcenter table-hover table-nowrap card-table">
                        <thead>
                            <tr>
                                <th class="w-1"></th>
                                <th>User</th>
                                <th>Role & Access</th>
                                <th>Client</th>
                                <th>Security</th>
                                <th>Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $role = $user->role;
                                    $roleAppCount = $role?->isAdmin()
                                        ? $appCount
                                        : ($role?->mods?->pluck('app_id')->filter()->unique()->count() ?? 0);
                                    $userPhoto = $user->profile_photo ?: $user->google_avatar;
                                    $userPhotoUrl = $userPhoto
                                        ? (str($userPhoto)->startsWith(['http://', 'https://', '//']) ? $userPhoto : asset(ltrim($userPhoto, '/')))
                                        : null;
                                    $isCurrentLogin = auth()->id() === $user->id;
                                @endphp

                                <tr>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="link-secondary border-0 bg-transparent p-0 d-inline-flex align-items-center" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-label="Open user actions">
                                                @include('templates.layouts.icon', ['name' => 'dots-vertical', 'size' => 20])
                                            </button>
                                            <div class="dropdown-menu">
                                                <button type="button" class="dropdown-item" wire:click="showUserDetail({{ $user->id }})">
                                                    Detail
                                                </button>
                                                <button type="button" class="dropdown-item" wire:click="editUser({{ $user->id }})">
                                                    Edit
                                                </button>
                                                <button type="button" class="dropdown-item text-danger" wire:click="confirmDeleteUser({{ $user->id }})">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center overflow-hidden">
                                            @if ($userPhotoUrl)
                                                <span class="avatar avatar-md me-3" style="background-image: url({{ $userPhotoUrl }})"></span>
                                            @else
                                                <span class="avatar avatar-md bg-primary-lt text-primary me-3">{{ str($user->name)->substr(0, 2)->upper() }}</span>
                                            @endif
                                            <div class="overflow-hidden">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold text-truncate">{{ $user->name }}</span>
                                                    @if ($isCurrentLogin)
                                                        <span class="badge bg-blue-lt text-blue">You</span>
                                                    @endif
                                                </div>
                                                <div class="text-secondary text-truncate">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $role?->name ?? '-' }}</div>
                                        <div class="small text-secondary">
                                            Code:
                                            <span class="font-monospace">{{ $role?->code ?? '-' }}</span>
                                        </div>
                                        <div class="small text-secondary">{{ $role?->isAdmin() ? 'Full Access' : $roleAppCount.' apps granted' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm bg-secondary-lt text-secondary me-2">{{ $clientInitials }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $user->client?->name ?? '-' }}</div>
                                                <div class="small text-secondary text-truncate">{{ $user->client?->email ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($user->email_verified_at)
                                            <span class="status status-green status-lite">Verified</span>
                                            <div class="small text-secondary">{{ $user->email_verified_at->format('d M Y H:i') }}</div>
                                        @else
                                            <span class="status status-yellow status-lite">Unverified</span>
                                            <div class="small text-secondary">Email confirmation pending</div>
                                        @endif
                                        <div class="small text-secondary mt-1">
                                            Provider:
                                            <span class="font-monospace">{{ $user->last_login_provider ?: ($user->google_id ? 'google' : 'password') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-secondary">Last login</div>
                                        <div>{{ $user->last_login_at?->format('d M Y H:i') ?? 'Never' }}</div>
                                        <div class="small text-secondary">
                                            IP:
                                            <span class="font-monospace">{{ $user->last_login_ip ?: '-' }}</span>
                                        </div>
                                        <div class="small text-secondary">Created {{ $user->created_at?->format('d M Y') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty py-5">
                                            <div class="empty-icon">
                                                @include('templates.layouts.icon', ['name' => 'users-group'])
                                            </div>
                                            <p class="empty-title">No users found</p>
                                            <p class="empty-subtitle text-secondary">Try another keyword or add a new login account.</p>
                                            <div class="empty-action">
                                                <button type="button" class="btn btn-primary" wire:click="newUser">
                                                    @include('templates.layouts.icon', ['name' => 'file-plus', 'class' => 'icon-sm me-1'])
                                                    Add User
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($userModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" role="dialog" aria-modal="true" wire:key="user-form-modal">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <form class="modal-content" wire:submit="save">
                    <div class="modal-header">
                        <div>
                            <h3 class="modal-title">{{ $selectedUserId ? 'Edit User' : 'Create New User' }}</h3>
                            <div class="text-secondary">{{ $selectedUserId ? 'Update login identity and role assignment.' : 'Create a new login account for this client.' }}</div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeUserModal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="card bg-body-tertiary mb-4">
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-md bg-primary-lt text-primary">{{ filled($userForm['name']) ? str($userForm['name'])->substr(0, 2)->upper() : 'US' }}</span>
                                    </div>
                                    <div class="col text-truncate">
                                        <div class="fw-semibold text-truncate">{{ filled($userForm['name']) ? $userForm['name'] : 'New User' }}</div>
                                        <div class="text-secondary text-truncate">{{ filled($userForm['email']) ? $userForm['email'] : 'email@example.test' }}</div>
                                    </div>
                                </div>

                                <div class="datagrid mt-3">
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Client</div>
                                        <div class="datagrid-content text-truncate">{{ $client?->name ?? '-' }}</div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Selected Role</div>
                                        <div class="datagrid-content">{{ $selectedRole?->name ?? '-' }}</div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">App Access</div>
                                        <div class="datagrid-content">
                                            <span class="status {{ $selectedRole?->isAdmin() ? 'status-red' : 'status-blue' }} status-lite">
                                                {{ $selectedRole?->isAdmin() ? 'Full Access' : $selectedRoleAppCount.' / '.$appCount }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="datagrid-item">
                                        <div class="datagrid-title">Last Provider</div>
                                        <div class="datagrid-content font-monospace">{{ $selectedUserProvider }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Display Name</label>
                                    <input type="text" class="form-control @error('userForm.name') is-invalid @enderror" wire:model="userForm.name" autocomplete="name">
                                    @error('userForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control @error('userForm.email') is-invalid @enderror" wire:model="userForm.email" autocomplete="email">
                                    @error('userForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Role</label>
                                    <select wire:key="user-role-select-{{ $selectedUserId ?: 'new' }}" class="form-select @error('userForm.role_id') is-invalid @enderror" wire:model.live="userForm.role_id">
                                        <option value="">Select Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('userForm.role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row g-3 mt-0">
                                    <div class="col-md-6">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control @error('userForm.password') is-invalid @enderror" wire:model="userForm.password" autocomplete="new-password">
                                        @error('userForm.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        @if ($selectedUserId)
                                            <div class="form-hint">Leave blank to keep the current password.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirmation</label>
                                        <input type="password" class="form-control @error('userForm.password_confirmation') is-invalid @enderror" wire:model="userForm.password_confirmation" autocomplete="new-password">
                                        @error('userForm.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @if ($selectedUserId)
                            <button type="button" class="btn btn-outline-danger" wire:click="confirmDeleteUser({{ $selectedUserId }})">
                                @include('templates.layouts.icon', ['name' => 'trash', 'class' => 'icon-sm me-1'])
                                Delete
                            </button>
                        @endif
                        <button type="button" class="btn ms-auto" wire:click="closeUserModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $selectedUserId ? 'Save Changes' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @if ($detailUserModalOpen && $selectedDetailUser)
        @php
            $detailRole = $selectedDetailUser->role;
            $detailClient = $selectedDetailUser->client;
            $detailRoleAppCount = $detailRole?->isAdmin()
                ? $appCount
                : ($detailRole?->mods?->pluck('app_id')->filter()->unique()->count() ?? 0);
            $detailRoleModuleCount = $detailRole?->isAdmin()
                ? 'Full'
                : ($detailRole?->mods?->count() ?? 0);
            $detailPhoto = $selectedDetailUser->profile_photo ?: $selectedDetailUser->google_avatar;
            $detailPhotoUrl = $detailPhoto
                ? (str($detailPhoto)->startsWith(['http://', 'https://', '//']) ? $detailPhoto : asset(ltrim($detailPhoto, '/')))
                : null;
        @endphp

        <div class="modal modal-blur fade show d-block" tabindex="-1" role="dialog" aria-modal="true" wire:key="user-detail-modal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row g-3 align-items-center w-100">
                            <div class="col-auto">
                                @if ($detailPhotoUrl)
                                    <span class="avatar avatar-lg" style="background-image: url({{ $detailPhotoUrl }})"></span>
                                @else
                                    <span class="avatar avatar-lg bg-primary-lt text-primary">{{ str($selectedDetailUser->name)->substr(0, 2)->upper() }}</span>
                                @endif
                            </div>
                            <div class="col text-truncate">
                                <h3 class="modal-title text-truncate">{{ $selectedDetailUser->name }}</h3>
                                <div class="text-secondary text-truncate">{{ $selectedDetailUser->email }}</div>
                            </div>
                            <div class="col-auto">
                                <span class="badge {{ $detailRole?->isAdmin() ? 'bg-danger-lt text-danger' : 'bg-primary-lt text-primary' }}">
                                    {{ $detailRole?->isAdmin() ? 'Full Access' : $detailRoleAppCount.' App' }}
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeUserDetail" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row row-cards">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Login Detail</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="datagrid">
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Login ID</div>
                                                <div class="datagrid-content font-monospace">{{ $selectedDetailUser->id }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Email</div>
                                                <div class="datagrid-content text-truncate">{{ $selectedDetailUser->email }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Email Verified</div>
                                                <div class="datagrid-content">{{ $selectedDetailUser->email_verified_at?->format('d M Y H:i') ?? 'Not verified' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Provider</div>
                                                <div class="datagrid-content font-monospace">{{ $selectedDetailUser->last_login_provider ?: ($selectedDetailUser->google_id ? 'google' : 'password') }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Google ID</div>
                                                <div class="datagrid-content font-monospace text-truncate">{{ $selectedDetailUser->google_id ?: '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Last Login</div>
                                                <div class="datagrid-content">{{ $selectedDetailUser->last_login_at?->format('d M Y H:i') ?? 'Never' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Last IP</div>
                                                <div class="datagrid-content font-monospace">{{ $selectedDetailUser->last_login_ip ?: '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Role & Access</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="datagrid">
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Role Name</div>
                                                <div class="datagrid-content">{{ $detailRole?->name ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Role Code</div>
                                                <div class="datagrid-content font-monospace">{{ $detailRole?->code ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Role Description</div>
                                                <div class="datagrid-content">{{ $detailRole?->desc ?: '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">App Access</div>
                                                <div class="datagrid-content">
                                                    <span class="status {{ $detailRole?->isAdmin() ? 'status-red' : 'status-blue' }} status-lite">
                                                        {{ $detailRole?->isAdmin() ? 'Full Access' : $detailRoleAppCount.' / '.$appCount }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Module Access</div>
                                                <div class="datagrid-content">{{ $detailRoleModuleCount }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Default Pages</div>
                                                <div class="datagrid-content">{{ $detailRole?->landings?->count() ?? 0 }}</div>
                                            </div>
                                        </div>

                                        @if ($detailRole?->landings?->isNotEmpty())
                                            <div class="list-group list-group-flush mt-3 border rounded">
                                                @foreach ($detailRole->landings as $landing)
                                                    <div class="list-group-item px-3 py-2">
                                                        <div class="d-flex justify-content-between gap-3">
                                                            <span class="fw-semibold">{{ $landing->menu?->mod?->app?->name ?? 'App' }}</span>
                                                            <span class="text-secondary text-truncate">{{ $landing->menu?->label ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Client Detail</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="datagrid">
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Client ID</div>
                                                <div class="datagrid-content font-monospace">{{ $detailClient?->id ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Client Name</div>
                                                <div class="datagrid-content">{{ $detailClient?->name ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Client Email</div>
                                                <div class="datagrid-content text-truncate">{{ $detailClient?->email ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Phone</div>
                                                <div class="datagrid-content">{{ $detailClient?->phone ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">PIC Name</div>
                                                <div class="datagrid-content">{{ $detailClient?->pic_name ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Client Created</div>
                                                <div class="datagrid-content">{{ $detailClient?->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Audit</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="datagrid">
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Login Created</div>
                                                <div class="datagrid-content">{{ $selectedDetailUser->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                            </div>
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Login Updated</div>
                                                <div class="datagrid-content">{{ $selectedDetailUser->updated_at?->format('d M Y H:i') ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" wire:click="closeUserDetail">Close</button>
                        <button type="button" class="btn btn-primary" wire:click="editUser({{ $selectedDetailUser->id }})">
                            Edit User
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    @if ($deleteUserModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" role="dialog" aria-modal="true" wire:key="delete-user-modal">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <button type="button" class="btn-close" wire:click="closeDeleteUserModal" aria-label="Close"></button>
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        @include('templates.layouts.icon', ['name' => 'alert-triangle', 'class' => 'mb-2 text-danger', 'size' => 48])
                        <h3>Delete user?</h3>
                        <div class="text-secondary">
                            {{ $deleteUserName ?: 'This user' }} will no longer be able to login to this client.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn w-100" wire:click="closeDeleteUserModal">Cancel</button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-danger w-100" wire:click="deleteSelectedUser">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
