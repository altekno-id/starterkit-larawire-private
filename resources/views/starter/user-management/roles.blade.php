@php
    $appTotal = $modules->count();
    $selectedModuleIds = collect($roleForm['module_ids'])->map(fn ($id): string => (string) $id)->all();
    $isAdminRole = $roleForm['code'] === 'admin';
    $grantedAppCount = $isAdminRole
        ? $appTotal
        : $modules
            ->filter(fn ($appModules): bool => $appModules->contains(fn ($module): bool => in_array((string) $module->id, $selectedModuleIds, true)))
            ->count();
    $assignedUserCount = $selectedRole?->user_logins_count ?? 0;
    $moduleAppKeys = $modules->map(fn ($appModules): string => 'app-'.($appModules->first()?->app_id ?? 'none'))->values();
    $allModuleAppsExpanded = $appTotal > 0 && $moduleAppKeys->diff($expandedModuleAppKeys)->isEmpty();
    $roleTitle = filled($roleForm['name']) ? $roleForm['name'] : ($selectedRoleId ? 'Selected Role' : 'New Role');
    $formMode = $isAdminRole ? 'View Role' : ($selectedRoleId ? 'Edit Role' : 'Create New Role');
    $submitLabel = $selectedRoleId ? 'Save Changes' : 'Save Role';
@endphp

<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Page header">
        <div class="row g-3 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / User Management</div>
                <h2 class="page-title">Role Management</h2>
            </div>
            <div class="col-12 col-lg-4">
                <input type="search" class="form-control" placeholder="Search roles, code, description" wire:model.live.debounce.250ms="search">
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-xl-4 order-1 order-xl-2">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Defined Roles</h3>
                        <p class="card-subtitle">{{ $roleCount }} role registered</p>
                    </div>
                    <div class="card-actions">
                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="newRole">
                            @include('templates.layouts.icon', ['name' => 'file-plus', 'class' => 'icon-sm me-1'])
                            New
                        </button>
                    </div>
                </div>

                <div class="list-group list-group-flush list-group-hoverable rounded-0">
                    @forelse ($roles as $role)
                        @php
                            $roleSelected = $selectedRoleId === $role->id;
                            $roleAppAccess = $role->isAdmin()
                                ? $modules->map(fn ($appModules): int => $appModules->count())
                                : $role->mods->groupBy(fn ($mod): string => $mod->app?->name ?? 'No App')->map(fn ($appModules): int => $appModules->count());
                            $roleAppCount = $roleAppAccess->count();
                            $roleDescription = filled($role->desc) ? $role->desc : 'No description provided.';
                        @endphp

                        <div class="list-group-item list-group-item-action py-3 cursor-pointer {{ $roleSelected ? 'active' : '' }}" role="button" tabindex="0" wire:click="editRole({{ $role->id }})" wire:key="role-list-{{ $role->id }}">
                            <div class="d-flex w-100 align-items-start justify-content-between gap-3">
                                <div class="text-start overflow-hidden">
                                    <div class="fw-semibold text-truncate">{{ $role->name }}</div>
                                    <div class="small text-secondary">
                                        {{ $roleDescription }}
                                    </div>
                                </div>
                                <span class="badge {{ $role->isAdmin() ? 'bg-danger-lt text-danger' : 'bg-primary-lt text-primary' }}">
                                    {{ $role->isAdmin() ? 'Full Access' : $roleAppCount.' App' }}
                                </span>
                            </div>
                            <div class="row g-1 mt-1 small">
                                <span class="col-6">
                                    <span class="text-secondary">Code</span>
                                    <span class="text-secondary">:</span>
                                    <span class="font-monospace ms-1">{{ $role->code }}</span>
                                </span>
                                <span class="col-6 text-end">
                                    @if ($role->user_logins_count > 0)
                                        <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click.stop="showRoleUsers({{ $role->id }})">
                                            <span>Users</span>
                                            <span>:</span>
                                            <span class="fw-semibold ms-1">
                                                {{ $role->user_logins_count }}
                                            </span>
                                        </button>
                                    @else
                                        <span class="text-secondary">Users</span>
                                        <span class="text-secondary">:</span>
                                        <span class="fw-semibold ms-1">
                                            {{ $role->user_logins_count }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item">
                            <div class="empty py-4">
                                <div class="empty-icon">
                                    @include('templates.layouts.icon', ['name' => 'users-group'])
                                </div>
                                <p class="empty-title">No roles found</p>
                                <p class="empty-subtitle text-secondary">Try another keyword or create a new role.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-8 order-2 order-xl-1">
            <form class="vstack gap-3" wire:submit="save">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title h2 mb-0">{{ $formMode }}</h2>
                            <p class="card-subtitle">Role Detail</p>
                        </div>
                        @if ($selectedRoleId)
                            <div class="card-actions">
                                <div class="btn-list">
                                    <button type="button" class="btn btn-outline-danger" wire:click="deleteRole({{ $selectedRoleId }})">
                                        @include('templates.layouts.icon', ['name' => 'trash', 'class' => 'icon-sm me-1'])
                                        Delete
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-start mb-3">
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2 text-secondary">
                                    <div class="fw-semibold">{{ $roleTitle }}</div>
                                    @if ($isAdminRole)
                                        <span class="badge bg-danger-lt text-danger">High Privileges</span>
                                        <span class="badge bg-secondary-lt">View Only</span>
                                    @elseif ($selectedRoleId)
                                        <span class="badge bg-primary-lt text-primary">Custom Access</span>
                                    @else
                                        <span class="badge bg-secondary-lt">Draft</span>
                                    @endif
                                </div>
                                <p class="text-secondary mt-1 mb-0">
                                    {{ filled($roleForm['desc']) ? $roleForm['desc'] : 'Configure the role identity and choose which application modules it can access.' }}
                                </p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control @error('roleForm.code') is-invalid @enderror" wire:model.live="roleForm.code" @readonly($isAdminRole)>
                                @error('roleForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control @error('roleForm.name') is-invalid @enderror" wire:model="roleForm.name" @readonly($isAdminRole)>
                                @error('roleForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5 col-lg-7">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control @error('roleForm.desc') is-invalid @enderror" wire:model="roleForm.desc" @readonly($isAdminRole)>
                                @error('roleForm.desc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="datagrid mt-4">
                            <div class="datagrid-item">
                                <div class="datagrid-title">Users Assigned</div>
                                <div class="datagrid-content">{{ $assignedUserCount }}</div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">App Access</div>
                                <div class="datagrid-content">
                                    <span class="status {{ $isAdminRole ? 'status-green' : 'status-blue' }} status-lite">
                                        {{ $grantedAppCount }} / {{ $appTotal }}
                                    </span>
                                </div>
                            </div>
                            <div class="datagrid-item">
                                <div class="datagrid-title">Role Code</div>
                                <div class="datagrid-content font-monospace text-truncate">{{ filled($roleForm['code']) ? $roleForm['code'] : '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card-header border-top">
                        <div>
                            <h3 class="card-title">Module Access</h3>
                            <p class="card-subtitle">Grant or deny access to registered modules for this role.</p>
                        </div>
                        <div class="card-actions">
                            <div class="btn-list">
                                @if ($isAdminRole)
                                    <span class="status status-green status-lite">Admin full access</span>
                                @else
                                    <span class="status status-blue status-lite">{{ $grantedAppCount }} apps granted</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-end align-items-center mb-3">
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="toggleAllModuleApps">
                                @include('templates.layouts.icon', ['name' => $allModuleAppsExpanded ? 'chevron-up' : 'chevron-down', 'class' => 'me-1'])
                                {{ $allModuleAppsExpanded ? 'Collapse all' : 'Expand all' }}
                            </button>
                        </div>

                        <div class="accordion accordion-inverted" id="role-module-access">
                            @foreach ($modules as $appName => $appModules)
                                @php
                                    $grantedAppModules = $isAdminRole
                                        ? $appModules->count()
                                        : $appModules->filter(fn ($module): bool => in_array((string) $module->id, $selectedModuleIds, true))->count();
                                    $appId = $appModules->first()?->app_id;
                                    $appKey = 'app-'.($appId ?? 'none');
                                    $appExpanded = in_array($appKey, $expandedModuleAppKeys, true);
                                    $appAccordionId = 'role-app-modules-'.$appKey;
                                @endphp

                                <div class="accordion-item" wire:key="role-app-modules-{{ $appKey }}">
                                    <div class="accordion-header">
                                        <button class="accordion-button {{ $appExpanded ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $appAccordionId }}" wire:click="toggleModuleApp('{{ $appKey }}')" aria-expanded="{{ $appExpanded ? 'true' : 'false' }}" aria-controls="{{ $appAccordionId }}">
                                            <span class="me-auto">
                                                <span class="d-block fw-semibold">{{ $appName }}</span>
                                                <span class="d-block small text-secondary">{{ $appModules->count() }} module available</span>
                                            </span>
                                            <span class="badge {{ $grantedAppModules > 0 ? 'bg-primary-lt text-primary' : 'bg-secondary-lt' }}">
                                                {{ $grantedAppModules }} / {{ $appModules->count() }} mods
                                            </span>
                                            <div class="accordion-button-toggle">
                                                @include('templates.layouts.icon', ['name' => 'chevron-down', 'class' => 'icon-1'])
                                            </div>
                                        </button>
                                    </div>

                                    <div id="{{ $appAccordionId }}" class="accordion-collapse collapse {{ $appExpanded ? 'show' : '' }}">
                                        <div class="accordion-body">
                                            <div class="vstack gap-3">
                                                @foreach ($appModules as $module)
                                                    @php
                                                        $moduleGranted = $isAdminRole || in_array((string) $module->id, $selectedModuleIds, true);
                                                        $moduleLandingMenus = $moduleGranted ? $module->menus : collect();
                                                    @endphp

                                                    <div class="form-check mb-0" wire:key="role-module-{{ $module->id }}">
                                                        @if ($isAdminRole)
                                                            <input type="checkbox" class="form-check-input" id="module-{{ $module->id }}" checked disabled>
                                                        @else
                                                            <input type="checkbox" class="form-check-input" id="module-{{ $module->id }}" value="{{ $module->id }}" wire:model.live="roleForm.module_ids">
                                                        @endif
                                                        <label class="form-check-label" for="module-{{ $module->id }}">
                                                            <span class="d-block">
                                                                <span class="overflow-hidden">
                                                                    <span class="d-flex align-items-baseline gap-2">
                                                                        <span class="fw-semibold">{{ $module->name }}</span>
                                                                        <span class="small text-secondary font-monospace">{{ $module->code }}</span>
                                                                    </span>
                                                                    <span class="d-block small text-secondary">
                                                                        {{ filled($module->desc) ? $module->desc : 'No description provided.' }}
                                                                    </span>
                                                                </span>
                                                            </span>
                                                        </label>

                                                        @if ($moduleGranted && $appId)
                                                            <div class="mt-2 vstack gap-1">
                                                                @forelse ($moduleLandingMenus as $menu)
                                                                    <label class="form-check mb-0 ps-4">
                                                                        <input type="radio" class="form-check-input w-3 h-3 ms-n4" value="{{ $menu->id }}" wire:model.live="roleForm.landing_menu_ids.{{ $appId }}" @disabled($isAdminRole)>
                                                                        <span class="form-check-label small">
                                                                            Set <span class="fw-semibold">{{ $menu->label }}</span> as landing page
                                                                        </span>
                                                                    </label>
                                                                @empty
                                                                    <div class="text-warning small">
                                                                        No default page available for this module.
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('roleForm.landing_menu_ids') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('roleForm.module_ids.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>
                    <div class="card-footer position-sticky bottom-0 z-2 bg-body shadow-sm">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                            <div class="text-secondary small">
                                {{ $isAdminRole ? 'This role is view only.' : 'Review module access and default pages before saving.' }}
                            </div>
                            <div class="btn-list ms-sm-auto">
                                @if ($isAdminRole)
                                    <button type="button" class="btn btn-outline-secondary" disabled>
                                        @include('templates.layouts.icon', ['name' => 'lock', 'class' => 'icon-sm me-1'])
                                        View Only
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary">
                                        @include('templates.layouts.icon', ['name' => 'check', 'class' => 'icon-sm me-1'])
                                        {{ $submitLabel }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($roleUsersModalOpen)
        <div class="modal modal-blur fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="role-users-modal-title">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h3 class="modal-title" id="role-users-modal-title">Role Users</h3>
                            <div class="text-secondary small">{{ $roleUsersRoleName }} · {{ count($roleUsers) }} user</div>
                        </div>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="closeRoleUsersModal"></button>
                    </div>

                    <div class="modal-body overflow-auto" style="max-height: min(32rem, calc(100vh - 14rem));">
                        <div class="list-group list-group-flush">
                            @forelse ($roleUsers as $user)
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="avatar avatar-sm">{{ str($user['name'])->substr(0, 1)->upper() }}</span>
                                        <div class="overflow-hidden">
                                            <div class="fw-semibold text-truncate">{{ $user['name'] }}</div>
                                            <div class="text-secondary small text-truncate">{{ $user['email'] }}</div>
                                        </div>
                                        <div class="ms-auto font-monospace small text-secondary">{{ $user['username'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty">
                                    <p class="empty-title">No users assigned</p>
                                    <p class="empty-subtitle text-secondary">This role is not assigned to any login account.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" wire:click="closeRoleUsersModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
