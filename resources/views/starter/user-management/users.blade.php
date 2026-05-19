<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">{{ $pageTitle }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">Starter</li>
                        <li class="breadcrumb-item">User Management</li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif
    @error('user') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">User Login</h4>
                        @if ($selectedUserId)
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="newUser">
                                <i class="ri-add-line align-middle"></i> Tambah User
                            </button>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-nowrap mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Credential</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr role="button" wire:click="editUser({{ $user->id }})" class="{{ $selectedUserId === $user->id ? 'table-active' : '' }}">
                                        <td><strong>{{ $user->name }}</strong></td>
                                        <td>
                                            {{ $user->username }}
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </td>
                                        <td>{{ $user->role?->name ?? '-' }}</td>
                                        <td>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">Belum ada user login.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">{{ $selectedUserId ? 'Edit User' : 'Tambah User Baru' }}</h4>

                    <form wire:submit="save">
                        <div class="form-group mb-3">
                            <label>Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Username</label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" wire:model="username">
                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Role</label>
                            <select class="form-control @error('roleId') is-invalid @enderror" wire:model="roleId">
                                <option value="">Pilih role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->code }})</option>
                                @endforeach
                            </select>
                            @error('roleId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Konfirmasi</label>
                                    <input type="password" class="form-control @error('passwordConfirmation') is-invalid @enderror" wire:model="passwordConfirmation">
                                    @error('passwordConfirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            @if ($selectedUserId)
                                <button type="button" class="btn btn-outline-danger" wire:click="deleteUser({{ $selectedUserId }})">
                                    <i class="ri-delete-bin-line align-middle mr-1"></i> Hapus
                                </button>
                            @else
                                <span class="text-muted small align-self-center">Isi data lalu simpan sebagai user baru.</span>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-3-line align-middle mr-1"></i> Simpan User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
