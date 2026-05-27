@php
    $accountStatus = (string) ($client?->account_status ?? 'pending');
    $subscriptionStatus = (string) ($client?->subscription_status ?? 'none');
    $accountStatusClass = match ($accountStatus) {
        'approved' => 'bg-success-lt',
        'rejected', 'suspended' => 'bg-danger-lt',
        default => 'bg-warning-lt',
    };
    $subscriptionStatusClass = match ($subscriptionStatus) {
        'active', 'trialing' => 'bg-success-lt',
        'past_due', 'canceled', 'expired' => 'bg-danger-lt',
        'pending_approval' => 'bg-warning-lt',
        default => 'bg-secondary-lt',
    };
@endphp

<div>
    <div class="page-header d-print-none mb-3" aria-label="Header halaman">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / Profile Saya</div>
                <h2 class="page-title">Edit Profile Saya</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="row g-0">
            <div class="col-12 col-lg-3 border-end">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <span class="avatar avatar-xl me-3" style="background-image: url({{ $loginAvatarUrl }})"></span>
                        <div class="min-w-0">
                            <div class="h3 mb-1 text-truncate">{{ $login->name }}</div>
                            <div class="text-secondary text-truncate">{{ $login->email }}</div>
                            <div class="mt-2">
                                <span class="badge bg-primary-lt">{{ $login->role?->name ?? 'No Role' }}</span>
                            </div>
                        </div>
                    </div>

                    <h4 class="subheader">Account Settings</h4>
                    <div class="list-group list-group-transparent mb-4">
                        <a href="#account-details" class="list-group-item list-group-item-action d-flex align-items-center active">
                            @include('templates.layouts.icon', ['name' => 'user-circle', 'class' => 'me-2'])
                            Account Detail
                        </a>
                        @if ($canManageClient)
                            <a href="#client-profile" class="list-group-item list-group-item-action d-flex align-items-center">
                                @include('templates.layouts.icon', ['name' => 'building', 'class' => 'me-2'])
                                Client Profile
                            </a>
                        @endif
                        <a href="#security" class="list-group-item list-group-item-action d-flex align-items-center">
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

                    @if ($canManageClient)
                        <h4 class="subheader mt-4">Admin Control</h4>
                        <div class="mb-2">
                            <span class="badge {{ $accountStatusClass }}">{{ $accountStatusOptions[$accountStatus] ?? $accountStatus }}</span>
                            <span class="badge {{ $subscriptionStatusClass }}">{{ $subscriptionStatusOptions[$subscriptionStatus] ?? $subscriptionStatus }}</span>
                        </div>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Approved</span>
                                <span class="fw-medium">{{ $client?->approved_at?->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Trial Ends</span>
                                <span class="fw-medium">{{ $client?->trial_ends_at?->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Subscribed Since</span>
                                <span class="fw-medium">{{ $client?->subscribed_at?->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Subscription Ends</span>
                                <span class="fw-medium">{{ $client?->subscription_ends_at?->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Payment Method</span>
                                <span class="fw-medium">{{ $client?->payment_method ?: '-' }}</span>
                            </div>
                            <div class="list-group-item px-0">
                                <div class="text-secondary">Payment Reference</div>
                                <div class="fw-medium text-break">{{ $client?->payment_reference ?: '-' }}</div>
                            </div>
                            <div class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-secondary">Payment Approved</span>
                                <span class="fw-medium">{{ $client?->payment_approved_at?->format('d M Y H:i') ?? '-' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-9 d-flex flex-column">
                <form id="account-details" wire:submit="saveAccount">
                    <div class="card-body border-bottom">
                        <h2 class="mb-4">Account Saya</h2>
                        <h3 class="card-title">Profile Detail</h3>

                        <div class="row align-items-center mb-4">
                            <div class="col-auto">
                                <span class="avatar avatar-xl" style="background-image: url({{ $loginAvatarUrl }})"></span>
                            </div>
                            <div class="col">
                                <div class="row g-3">
                                    <div class="col-lg-8">
                                        <label class="form-label">URL / Path Profile Photo</label>
                                        <input type="text" class="form-control @error('accountForm.profile_photo') is-invalid @enderror" wire:model="accountForm.profile_photo" placeholder="assets/mine/avatar.png">
                                        @error('accountForm.profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label">Avatar Google</label>
                                        <input type="text" class="form-control" value="{{ $login->google_avatar ?: '-' }}" disabled>
                                    </div>
                                </div>
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
                    <div class="card-footer bg-transparent border-bottom">
                        <div class="btn-list justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                @include('templates.layouts.icon', ['name' => 'check', 'class' => 'me-1'])
                                Simpan Account
                            </button>
                        </div>
                    </div>
                </form>

                @if ($canManageClient)
                    <form id="client-profile" wire:submit="saveClientProfile">
                        <div class="card-body border-bottom">
                            <h3 class="card-title">Client Profile</h3>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Client Name</label>
                                    <input type="text" class="form-control @error('clientForm.name') is-invalid @enderror" wire:model="clientForm.name">
                                    @error('clientForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Client Email</label>
                                    <input type="email" class="form-control @error('clientForm.email') is-invalid @enderror" wire:model="clientForm.email">
                                    @error('clientForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('clientForm.phone') is-invalid @enderror" wire:model="clientForm.phone">
                                    @error('clientForm.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PIC Name</label>
                                    <input type="text" class="form-control @error('clientForm.pic_name') is-invalid @enderror" wire:model="clientForm.pic_name">
                                    @error('clientForm.pic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">URL / Path Logo</label>
                                    <input type="text" class="form-control @error('clientForm.logo') is-invalid @enderror" wire:model="clientForm.logo">
                                    @error('clientForm.logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-bottom">
                            <div class="btn-list justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    @include('templates.layouts.icon', ['name' => 'check', 'class' => 'me-1'])
                                    Simpan Client
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                <form id="security" wire:submit="changePassword">
                    <div class="card-body">
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
                                <label class="form-label">Konfirmasi</label>
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
