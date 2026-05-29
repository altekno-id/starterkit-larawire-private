<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / My Profile</div>
                <h2 class="page-title">Edit My Profile</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="row g-0">
            <div class="col-12 col-lg-3 border-end">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-4">
                        <span class="avatar avatar-md flex-shrink-0 me-3" style="background-image: url({{ $loginAvatarUrl }})"></span>
                        <div class="flex-fill min-w-0 overflow-hidden">
                            <div class="h3 mb-1 text-truncate">{{ $login->name }}</div>
                            <div class="starter-hover-tooltip position-relative">
                                <div class="text-secondary text-truncate" tabindex="0">{{ $login->email }}</div>
                                <div class="tooltip bs-tooltip-top show" role="tooltip">
                                    <div class="tooltip-arrow"></div>
                                    <div class="tooltip-inner">{{ $login->email }}</div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-primary-lt">{{ $login->role?->name ?? 'No Role' }}</span>
                            </div>
                        </div>
                    </div>

                    <h4 class="subheader">Account Settings</h4>
                    <div class="list-group list-group-transparent mb-4" id="profile-settings-tabs" role="tablist">
                        <a href="#account-details" class="list-group-item list-group-item-action d-flex align-items-center active" data-bs-toggle="list" role="tab" aria-controls="account-details" aria-selected="true">
                            @include('templates.layouts.icon', ['name' => 'user-circle', 'class' => 'me-2'])
                            Account Detail
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action d-flex align-items-center" data-bs-toggle="list" role="tab" aria-controls="security" aria-selected="false">
                            @include('templates.layouts.icon', ['name' => 'lock', 'class' => 'me-2'])
                            Security
                        </a>
                    </div>

                    <h4 class="subheader">Login Summary</h4>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Username</span>
                            <span class="fw-medium">{{ $login->username }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Email Verified</span>
                            <span class="fw-medium">{{ $login->email_verified_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Provider Login</span>
                            <span class="fw-medium">{{ $login->last_login_provider ?? '-' }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-secondary">Last Login</span>
                            <span class="fw-medium">{{ $login->last_login_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9 d-flex flex-column">
                <div class="tab-content flex-grow-1">
                    <form id="account-details" class="tab-pane fade show active" role="tabpanel" wire:submit="saveAccount">
                        <div class="card-body">
                            <h2 class="mb-4">My Account</h2>
                            <h3 class="card-title">Profile Detail</h3>

                            <div class="row align-items-center mb-4">
                                <div class="col-auto">
                                    <span class="avatar avatar-xl flex-shrink-0" style="background-image: url({{ $profilePhotoPreviewUrl }})"></span>
                                </div>
                                <div class="col-auto">
                                    <label class="btn btn-outline-primary mb-0" for="profile-photo-upload">
                                        Change profile photo
                                    </label>
                                    <input type="file" id="profile-photo-upload" class="d-none @error('profilePhotoUpload') is-invalid @enderror" wire:model="profilePhotoUpload" accept="image/*">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-ghost-danger" data-bs-toggle="modal" data-bs-target="#delete-profile-photo-modal">
                                        Delete profile photo
                                    </button>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" wire:model="accountForm.profile_photo">
                                    @error('profilePhotoUpload') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                    @error('accountForm.profile_photo') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                    <div class="text-secondary small mt-2" wire:loading wire:target="profilePhotoUpload">Uploading...</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Display Name</label>
                                    <input type="text" class="form-control @error('accountForm.name') is-invalid @enderror" wire:model="accountForm.name">
                                    @error('accountForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control @error('accountForm.username') is-invalid @enderror" wire:model="accountForm.username">
                                    @error('accountForm.username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email Login</label>
                                    <input type="email" class="form-control @error('accountForm.email') is-invalid @enderror" wire:model="accountForm.email">
                                    @error('accountForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-list justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    @include('templates.layouts.icon', ['name' => 'check', 'class' => 'me-1'])
                                    Save Account
                                </button>
                            </div>
                        </div>
                    </form>

                    @include('templates.components.danger-modal', [
                        'id' => 'delete-profile-photo-modal',
                        'title' => 'Delete profile photo?',
                        'message' => 'Your current profile photo will be replaced with the default photo.',
                        'confirmText' => 'Delete profile photo',
                        'confirmAction' => 'resetProfilePhoto',
                        'dismissOnConfirm' => true,
                    ])
                    <form id="security" class="tab-pane fade" role="tabpanel" wire:submit="changePassword">
                        <div class="card-body">
                            <h2 class="mb-4">My Account</h2>
                            <h3 class="card-title">Security</h3>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" class="form-control @error('passwordForm.current_password') is-invalid @enderror" wire:model="passwordForm.current_password" autocomplete="current-password">
                                    @error('passwordForm.current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('passwordForm.password') is-invalid @enderror" wire:model="passwordForm.password" autocomplete="new-password">
                                    @error('passwordForm.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Confirmation</label>
                                    <input type="password" class="form-control @error('passwordForm.password_confirmation') is-invalid @enderror" wire:model="passwordForm.password_confirmation" autocomplete="new-password">
                                    @error('passwordForm.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-list justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    @include('templates.layouts.icon', ['name' => 'lock', 'class' => 'me-1'])
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
