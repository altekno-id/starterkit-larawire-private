<div>
    <div class="page-header d-print-none mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / User Management</div>
                <h2 class="page-title">User</h2>
            </div>
            @if ($selectedUserId)
                <div class="col-auto ms-auto">
                    <button type="button" class="btn btn-outline-primary" wire:click="newUser">
                        Add User
                    </button>
                </div>
            @endif
        </div>
    </div>

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
                                <th>Name</th>
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
                                    <td colspan="4" class="text-secondary">No user login yet.</td>
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
                    <h3 class="card-title">{{ $selectedUserId ? 'Edit User' : 'Add New User' }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control @error('userForm.name') is-invalid @enderror" wire:model="userForm.name">
                        @error('userForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control @error('userForm.username') is-invalid @enderror" wire:model="userForm.username">
                            @error('userForm.username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('userForm.email') is-invalid @enderror" wire:model="userForm.email">
                            @error('userForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Role</label>
                        <select wire:key="user-role-select-{{ $selectedUserId ?: 'new' }}" class="form-select @error('userForm.role_id') is-invalid @enderror" wire:model="userForm.role_id">
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
                            <input type="password" class="form-control @error('userForm.password') is-invalid @enderror" wire:model="userForm.password">
                            @error('userForm.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmation</label>
                            <input type="password" class="form-control @error('userForm.password_confirmation') is-invalid @enderror" wire:model="userForm.password_confirmation">
                            @error('userForm.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex">
                    @if ($selectedUserId)
                        <button type="button" class="btn btn-outline-danger" wire:click="deleteUser({{ $selectedUserId }})">
                            Delete
                        </button>
                    @else
                        <span class="text-secondary align-self-center">Fill the form and save it as a new user.</span>
                    @endif
                    <button type="submit" class="btn btn-primary ms-auto">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
