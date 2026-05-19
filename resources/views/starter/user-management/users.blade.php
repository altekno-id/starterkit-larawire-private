<div>
    <div class="page-header d-print-none mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / User Management</div>
                <h2 class="page-title">Users</h2>
            </div>
            @if ($selectedUserId)
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-outline-primary" wire:click="newUser">
                        Tambah User
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if (session('status'))
        @include('templates.components.alert', ['type' => 'success', 'message' => session('status')])
    @endif
    @error('user') @include('templates.components.alert', ['type' => 'danger', 'message' => $message]) @enderror

    <div class="row row-cards">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Login</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover table-nowrap card-table">
                        <thead>
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm me-2">{{ str($user->name)->substr(0, 2)->upper() }}</span>
                                            <span class="fw-semibold">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $user->username }}</div>
                                        <div class="text-secondary small">{{ $user->email }}</div>
                                    </td>
                                    <td>{{ $user->role?->name ?? '-' }}</td>
                                    <td>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary">Belum ada user login.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <form class="card" wire:submit="save">
                <div class="card-header">
                    <h3 class="card-title">{{ $selectedUserId ? 'Edit User' : 'Tambah User Baru' }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" wire:model="username">
                            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Role</label>
                        <select wire:key="user-role-select-{{ $selectedUserId ?: 'new' }}" class="form-select @error('roleId') is-invalid @enderror" wire:model="roleId">
                            <option value="">Pilih role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->code }})</option>
                            @endforeach
                        </select>
                        @error('roleId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi</label>
                            <input type="password" class="form-control @error('passwordConfirmation') is-invalid @enderror" wire:model="passwordConfirmation">
                            @error('passwordConfirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex">
                    @if ($selectedUserId)
                        <button type="button" class="btn btn-outline-danger" wire:click="deleteUser({{ $selectedUserId }})">
                            Hapus
                        </button>
                    @else
                        <span class="text-secondary align-self-center">Isi data lalu simpan sebagai user baru.</span>
                    @endif
                    <button type="submit" class="btn btn-primary ms-auto">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>
