<div>
    @unless ($embedded)
        <div class="page-header d-print-none mt-0 mb-3">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle">Starter / Manajemen User</div>
                    <h2 class="page-title">Manajemen User</h2>
                    <div class="text-secondary">Pembuatan akun dan reset password hanya dilakukan oleh Superuser.</div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info mb-3" role="note">
            <div class="d-flex gap-2">
                @include('starter.templates.layouts.icon', ['name' => 'info-circle', 'class' => 'icon-sm flex-shrink-0 mt-1'])
                <div>
                    <div class="fw-semibold">Akun dikelola oleh Superuser</div>
                    <div class="small">User tidak dapat melakukan register atau reset password sendiri.</div>
                </div>
            </div>
        </div>
    @endunless

    @if ($temporaryPassword)
        <div class="alert alert-warning alert-dismissible" role="alert" data-temporary-credentials-alert>
            <div>
                <h3 class="alert-title">Simpan kredensial sementara ini sekarang</h3>
                <div>Username: <strong class="font-monospace">{{ $temporaryPasswordUsername }}</strong></div>
                <div>Password: <strong class="font-monospace">{{ $temporaryPassword }}</strong></div>
                <div class="small mt-1">Password tidak akan ditampilkan lagi dan pengguna wajib menggantinya saat login.</div>
            </div>
            <button type="button" class="btn-close" wire:click="dismissTemporaryPassword" aria-label="Tutup" data-temporary-credentials-dismiss></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="search" class="form-control" maxlength="100" placeholder="Cari nama, username, email, atau role" wire:model.live.debounce.350ms="search">
                </div>
                <div class="col-md-4">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="locked">Terkunci</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Daftar User</h3>
                <p class="card-subtitle">Menampilkan {{ \App\Support\Starter\StarterNumber::decimal($users->firstItem() ?? 0) }}–{{ \App\Support\Starter\StarterNumber::decimal($users->lastItem() ?? 0) }} dari {{ \App\Support\Starter\StarterNumber::decimal($users->total()) }} user</p>
            </div>
            <div class="card-actions">
                <a
                    href="{{ route('starter.user-management.users.create') }}"
                    class="btn btn-primary"
                    data-user-create-location="content"
                    data-starter-navigate
                >
                    @include('starter.templates.layouts.icon', ['name' => 'user-plus', 'class' => 'icon-sm me-1'])
                    Tambah User
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th class="w-1"></th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr
                            class="{{ $user->role?->isSuperuser() ? 'bg-danger-lt' : '' }}"
                            wire:key="user-row-{{ $user->id }}"
                            @if ($user->role?->isSuperuser()) data-default-user @endif
                        >
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                    @if ($user->role?->isSuperuser())
                                        <span class="badge bg-danger-lt text-danger">Akun Default</span>
                                    @endif
                                </div>
                                <div class="text-secondary"><span class="font-monospace">{{ $user->username }}</span> · {{ $user->email }}</div>
                            </td>
                            <td>
                                <div>{{ $user->role?->name ?? '-' }}</div>
                                <div class="small text-secondary">{{ $user->role?->isSuperuser() ? 'Akses penuh' : \App\Support\Starter\StarterNumber::decimal($user->role?->mods_count ?? 0).' module' }}</div>
                            </td>
                            <td>
                                <span class="status {{ $user->status === 'active' ? 'status-green' : 'status-red' }} status-lite">{{ ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'locked' => 'Terkunci'][$user->status] ?? $user->status }}</span>
                                @if ($user->must_change_password)<div class="small text-warning mt-1">Wajib mengganti password</div>@endif
                            </td>
                            <td>{{ $user->last_login_at?->format('d M Y H:i') ?? 'Belum pernah' }}</td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('starter.user-management.users.edit', $user->id) }}" class="btn btn-sm" data-starter-navigate>Edit</a>
                                    @if (! $user->role?->isSuperuser())
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-warning"
                                            wire:click="preparePasswordReset({{ $user->id }})"
                                        >Reset</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-5">Belum ada user sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer d-flex align-items-center">
                <div class="ms-auto">
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>

    @include('starter.templates.components.danger-modal', [
        'id' => 'reset-user-password-modal',
        'title' => 'Reset password user?',
        'message' => filled($passwordResetUserName)
            ? 'Password sementara baru akan dibuat untuk '.$passwordResetUserName.'.'
            : 'Password sementara baru akan dibuat untuk user ini.',
        'confirmText' => 'Reset Password',
        'confirmAction' => 'resetSelectedPassword',
        'cancelAction' => 'cancelPasswordReset',
        'visible' => $passwordResetModalOpen,
        'dismissOnConfirm' => false,
    ])
    @if ($passwordResetModalOpen)
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
