<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $pageTitle }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">Starter</li>
                        <li class="breadcrumb-item">User Management</li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif
    @error('role') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    <div class="row">
        <div class="col-xl-5">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Role List</h4>
                        @if ($selectedRoleId)
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="newRole">
                                <i class="ri-add-line align-middle"></i> Tambah Role
                            </button>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-nowrap mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Role</th>
                                    <th>Access</th>
                                    <th class="text-right">Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr role="button" wire:click="editRole({{ $role->id }})" class="{{ $selectedRoleId === $role->id ? 'table-active' : '' }}">
                                        <td>
                                            <strong>{{ $role->name }}</strong>
                                            <div class="text-muted small">{{ $role->code }}</div>
                                        </td>
                                        <td>
                                            @if ($role->isAdmin())
                                                <span class="badge badge-soft-success">Full</span>
                                            @else
                                                <span class="badge badge-soft-secondary">{{ $role->mods->count() }} Module</span>
                                            @endif
                                        </td>
                                        <td class="text-right">{{ $role->user_logins_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">Belum ada role.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ $selectedRoleId ? 'Edit Role' : 'Tambah Role Baru' }}</h4>

                    <form wire:submit="save">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Kode</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" wire:model.live="code">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Nama</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Deskripsi</label>
                                    <input type="text" class="form-control @error('desc') is-invalid @enderror" wire:model="desc">
                                    @error('desc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            @if ($selectedRoleId)
                                <button type="button" class="btn btn-outline-danger" wire:click="deleteRole({{ $selectedRoleId }})" @disabled($code === 'admin')>
                                    <i class="ri-delete-bin-line align-middle mr-1"></i> Hapus
                                </button>
                            @else
                                <span class="text-muted small align-self-center">Isi data lalu simpan sebagai role baru.</span>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-3-line align-middle mr-1"></i> Simpan Role
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Module Access</h4>
                        @if ($code === 'admin')
                            <span class="badge badge-soft-success">Admin full access</span>
                        @endif
                    </div>

                    <div class="row">
                        @foreach ($modules as $appName => $appModules)
                            <div class="col-md-6">
                                <h6 class="font-size-13 text-muted mb-2">{{ $appName }}</h6>
                                @foreach ($appModules as $module)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input" id="module-{{ $module->id }}" value="{{ $module->id }}" wire:model="moduleIds" @disabled($code === 'admin')>
                                        <label class="custom-control-label" for="module-{{ $module->id }}">
                                            {{ $module->name }}
                                            <span class="text-muted small">({{ $module->code }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    @error('moduleIds.*') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>
</div>
