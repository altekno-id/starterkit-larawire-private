<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Header halaman">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / Profil Saya</div>
                <h2 class="page-title">Edit Profil Saya</h2>
            </div>
        </div>
    </div>

    @if ($login->must_change_password)
        <div class="alert alert-warning mb-3" role="alert">
            <div class="d-flex gap-3">
                <span class="alert-icon flex-shrink-0">
                    @include('templates.layouts.icon', ['name' => 'alert-triangle', 'class' => 'm-0'])
                </span>
                <div>
                    <h3 class="alert-title">Password sementara harus diganti</h3>
                    <div>
                        Masukkan password sementara yang diberikan admin pada kolom <strong>Password Saat Ini</strong>,
                        kemudian buat <strong>Password Baru</strong>. Anda dapat melanjutkan ke halaman lain setelah password berhasil diubah.
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="row g-0">
            <div class="col-12 col-lg-3 border-end">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-4">
                        <span class="avatar avatar-md flex-shrink-0 me-3" style="background-image: url({{ $loginAvatarUrl }})"></span>
                        <div class="flex-fill min-w-0 overflow-hidden">
                            <div class="h3 mb-1 text-truncate">{{ $login->name }}</div>
                            <div class="text-secondary text-truncate" title="{{ $login->email }}">{{ $login->email }}</div>
                            <div class="mt-2">
                                <span class="badge bg-primary-lt">{{ $login->role?->name ?? 'Tanpa Role' }}</span>
                            </div>
                        </div>
                    </div>

                    <h4 class="subheader">Pengaturan Akun</h4>
                    <div class="list-group list-group-transparent mb-4" id="profile-settings-tabs" role="tablist">
                        <a
                            href="#account-details"
                            class="list-group-item list-group-item-action d-flex align-items-center {{ $activeTab === 'account-details' ? 'active' : '' }} {{ $login->must_change_password ? 'disabled' : '' }}"
                            @unless ($login->must_change_password) data-bs-toggle="list" wire:click="showTab('account-details')" @endunless
                            role="tab"
                            aria-controls="account-details"
                            aria-selected="{{ $activeTab === 'account-details' ? 'true' : 'false' }}"
                            aria-disabled="{{ $login->must_change_password ? 'true' : 'false' }}"
                            @if ($login->must_change_password) tabindex="-1" @endif
                        >
                            @include('templates.layouts.icon', ['name' => 'user-circle', 'class' => 'me-2'])
                            Detail Akun
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action d-flex align-items-center {{ $activeTab === 'security' ? 'active' : '' }}" data-bs-toggle="list" wire:click="showTab('security')" role="tab" aria-controls="security" aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
                            @include('templates.layouts.icon', ['name' => 'lock', 'class' => 'me-2'])
                            Keamanan
                            @if ($login->must_change_password)
                                <span class="badge bg-warning-lt text-warning ms-auto">Wajib</span>
                            @endif
                        </a>
                    </div>

                    <h4 class="subheader">Ringkasan Login</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Email Terverifikasi</span>
                            <span class="fw-medium">{{ $login->email_verified_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Metode Login</span>
                            <span class="fw-medium">Username: {{ $login->username }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Login Terakhir</span>
                            <span class="fw-medium">{{ $login->last_login_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9 d-flex flex-column">
                <div class="tab-content flex-grow-1">
                    <form id="account-details" class="tab-pane fade {{ $activeTab === 'account-details' ? 'show active' : '' }}" role="tabpanel" wire:submit="saveAccount">
                        <div class="card-body">
                            <h2 class="mb-4">Akun Saya</h2>
                            <h3 class="card-title">Detail Profil</h3>

                            <div class="row align-items-center mb-4">
                                <div class="col-auto">
                                    <span class="avatar avatar-xl flex-shrink-0" style="background-image: url({{ $profilePhotoPreviewUrl }})"></span>
                                </div>
                                <div class="col-auto">
                                    <label class="btn btn-outline-primary mb-0" for="profile-photo-upload">
                                        Ganti Foto Profil
                                    </label>
                                    <input type="file" id="profile-photo-upload" class="d-none @error('profilePhotoUpload') is-invalid @enderror" wire:model="profilePhotoUpload" accept="image/*">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-ghost-danger" data-bs-toggle="modal" data-bs-target="#delete-profile-photo-modal">
                                        Hapus Foto Profil
                                    </button>
                                </div>
                                <div class="col-12">
                                    @error('profilePhotoUpload') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                    <div class="text-secondary small mt-2" wire:loading wire:target="profilePhotoUpload">Mengunggah...</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="profile-display-name">Nama Tampilan</label>
                                    <input type="text" id="profile-display-name" class="form-control @error('accountForm.name') is-invalid @enderror" wire:model="accountForm.name" autocomplete="name">
                                    @error('accountForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="profile-email">Email Login</label>
                                    <input type="email" id="profile-email" class="form-control @error('accountForm.email') is-invalid @enderror" wire:model="accountForm.email" autocomplete="email">
                                    @error('accountForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-list justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    @include('templates.layouts.icon', ['name' => 'check', 'class' => 'me-1'])
                                    Simpan Akun
                                </button>
                            </div>
                        </div>
                    </form>

                    @include('templates.components.danger-modal', [
                        'id' => 'delete-profile-photo-modal',
                        'title' => 'Hapus foto profil?',
                        'message' => 'Foto profil saat ini akan diganti dengan foto default.',
                        'confirmText' => 'Hapus Foto Profil',
                        'confirmAction' => 'resetProfilePhoto',
                        'dismissOnConfirm' => true,
                    ])
                    <form id="security" class="tab-pane fade {{ $activeTab === 'security' ? 'show active' : '' }}" role="tabpanel" wire:submit="changePassword">
                        <div class="card-body">
                            <h2 class="mb-4">Akun Saya</h2>
                            <h3 class="card-title">Keamanan</h3>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="profile-current-password">Password Saat Ini</label>
                                    <div class="input-group input-group-flat" x-data="{ visible: false }">
                                        <input
                                            :type="visible ? 'text' : 'password'"
                                            type="password"
                                            id="profile-current-password"
                                            class="form-control @error('passwordForm.current_password') is-invalid @enderror"
                                            wire:model="passwordForm.current_password"
                                            autocomplete="current-password"
                                            @error('passwordForm.current_password') aria-invalid="true" aria-describedby="profile-current-password-error" @enderror
                                        >
                                        <span class="input-group-text">
                                            <button
                                                type="button"
                                                class="btn btn-link btn-sm p-0 text-secondary"
                                                x-on:click="visible = ! visible"
                                                x-bind:aria-pressed="visible"
                                                x-bind:aria-label="visible ? 'Sembunyikan Password Saat Ini' : 'Tampilkan Password Saat Ini'"
                                            >
                                                <span x-show="! visible">
                                                    @include('templates.layouts.icon', ['name' => 'eye', 'class' => 'icon-sm m-0'])
                                                </span>
                                                <span x-show="visible" style="display: none;">
                                                    @include('templates.layouts.icon', ['name' => 'eye-off', 'class' => 'icon-sm m-0'])
                                                </span>
                                            </button>
                                        </span>
                                    </div>
                                    @error('passwordForm.current_password') <div id="profile-current-password-error" class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="profile-new-password">Password Baru</label>
                                    <div class="input-group input-group-flat" x-data="{ visible: false }">
                                        <input
                                            :type="visible ? 'text' : 'password'"
                                            type="password"
                                            id="profile-new-password"
                                            class="form-control @error('passwordForm.password') is-invalid @enderror"
                                            wire:model="passwordForm.password"
                                            autocomplete="new-password"
                                            @error('passwordForm.password') aria-invalid="true" aria-describedby="profile-new-password-error" @enderror
                                        >
                                        <span class="input-group-text">
                                            <button
                                                type="button"
                                                class="btn btn-link btn-sm p-0 text-secondary"
                                                x-on:click="visible = ! visible"
                                                x-bind:aria-pressed="visible"
                                                x-bind:aria-label="visible ? 'Sembunyikan Password Baru' : 'Tampilkan Password Baru'"
                                            >
                                                <span x-show="! visible">
                                                    @include('templates.layouts.icon', ['name' => 'eye', 'class' => 'icon-sm m-0'])
                                                </span>
                                                <span x-show="visible" style="display: none;">
                                                    @include('templates.layouts.icon', ['name' => 'eye-off', 'class' => 'icon-sm m-0'])
                                                </span>
                                            </button>
                                        </span>
                                    </div>
                                    <div class="form-hint">Minimal 10 karakter dengan huruf besar, huruf kecil, dan angka.</div>
                                    @error('passwordForm.password') <div id="profile-new-password-error" class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="profile-password-confirmation">Konfirmasi Password</label>
                                    <div class="input-group input-group-flat" x-data="{ visible: false }">
                                        <input
                                            :type="visible ? 'text' : 'password'"
                                            type="password"
                                            id="profile-password-confirmation"
                                            class="form-control @error('passwordForm.password_confirmation') is-invalid @enderror"
                                            wire:model="passwordForm.password_confirmation"
                                            autocomplete="new-password"
                                            @error('passwordForm.password_confirmation') aria-invalid="true" aria-describedby="profile-password-confirmation-error" @enderror
                                        >
                                        <span class="input-group-text">
                                            <button
                                                type="button"
                                                class="btn btn-link btn-sm p-0 text-secondary"
                                                x-on:click="visible = ! visible"
                                                x-bind:aria-pressed="visible"
                                                x-bind:aria-label="visible ? 'Sembunyikan Konfirmasi Password' : 'Tampilkan Konfirmasi Password'"
                                            >
                                                <span x-show="! visible">
                                                    @include('templates.layouts.icon', ['name' => 'eye', 'class' => 'icon-sm m-0'])
                                                </span>
                                                <span x-show="visible" style="display: none;">
                                                    @include('templates.layouts.icon', ['name' => 'eye-off', 'class' => 'icon-sm m-0'])
                                                </span>
                                            </button>
                                        </span>
                                    </div>
                                    @error('passwordForm.password_confirmation') <div id="profile-password-confirmation-error" class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-list justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    @include('templates.layouts.icon', ['name' => 'lock', 'class' => 'me-1'])
                                    Ubah Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
