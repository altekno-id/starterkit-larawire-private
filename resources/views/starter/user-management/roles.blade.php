<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / User Management</div>
                <h2 class="page-title">Role</h2>
            </div>
            @if ($selectedRoleId)
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-outline-primary" wire:click="newRole">
                        Add Role
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role List</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover table-nowrap card-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Access</th>
                                <th class="text-end">User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                                <tr role="button" wire:click="editRole({{ $role->id }})" class="{{ $selectedRoleId === $role->id ? 'table-active' : '' }}">
                                    <td>
                                        <div class="fw-semibold">{{ $role->name }}</div>
                                        <div class="text-secondary small">{{ $role->code }}</div>
                                    </td>
                                    <td>
                                        @if ($role->isAdmin())
                                            <span class="badge bg-success-lt">Full</span>
                                        @else
                                            <span class="badge bg-secondary-lt">{{ $role->mods->count() }} Module</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $role->user_logins_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-secondary">No role yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <form class="card" wire:submit="save">
                <div class="card-header">
                    <h3 class="card-title">{{ $selectedRoleId ? 'Edit Role' : 'Add New Role' }}</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control @error('roleForm.code') is-invalid @enderror" wire:model.live="roleForm.code">
                            @error('roleForm.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control @error('roleForm.name') is-invalid @enderror" wire:model="roleForm.name">
                            @error('roleForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control @error('roleForm.desc') is-invalid @enderror" wire:model="roleForm.desc">
                            @error('roleForm.desc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex">
                    @if ($selectedRoleId)
                        <button type="button" class="btn btn-outline-danger" wire:click="deleteRole({{ $selectedRoleId }})" @disabled($roleForm['code'] === 'admin')>
                            Delete
                        </button>
                    @else
                        <span class="text-secondary align-self-center">Fill the form and save it as a new role.</span>
                    @endif
                    <button type="submit" class="btn btn-primary ms-auto">Save Role</button>
                </div>
            </form>

            <div class="card mt-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Module Access</h3>
                        <p class="card-subtitle">Select modules this role can access.</p>
                    </div>
                    @if ($roleForm['code'] === 'admin')
                        <div class="card-actions">
                            <span class="badge bg-success-lt">Admin full access</span>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($modules as $appName => $appModules)
                            <div class="col-md-6">
                                <div class="text-secondary text-uppercase small fw-bold mb-2">{{ $appName }}</div>
                                @foreach ($appModules as $module)
                                    <label class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="module-{{ $module->id }}" value="{{ $module->id }}" wire:model="roleForm.module_ids" @disabled($roleForm['code'] === 'admin')>
                                        <span class="form-check-label">
                                            {{ $module->name }}
                                            <span class="text-secondary small">({{ $module->code }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    @error('roleForm.module_ids.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>
