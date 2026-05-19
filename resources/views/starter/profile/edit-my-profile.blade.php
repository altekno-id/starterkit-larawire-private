<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">{{ $pageTitle }}</h4>
                    <div class="text-muted small mt-1">Kelola data login pribadi dan profil client yang terkait dengan akun ini.</div>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">Starter</li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Public Account</h4>
                        <span class="badge badge-soft-success">Semua user</span>
                    </div>

                    <form wire:submit="saveAccount">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Nama Login</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Username</label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror" wire:model="username">
                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Email Login</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-3-line align-middle mr-1"></i> Simpan Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Client Profile</h4>
                        <span class="badge {{ $canManageClient ? 'badge-soft-primary' : 'badge-soft-secondary' }}">
                            {{ $canManageClient ? 'Admin only' : 'Read only' }}
                        </span>
                    </div>

                    <form wire:submit="saveClientProfile">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Nama Client</label>
                                    <input type="text" class="form-control @error('clientName') is-invalid @enderror" wire:model="clientName" @disabled(! $canManageClient)>
                                    @error('clientName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Email Client</label>
                                    <input type="email" class="form-control @error('clientEmail') is-invalid @enderror" wire:model="clientEmail" @disabled(! $canManageClient)>
                                    @error('clientEmail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Phone</label>
                                    <input type="text" class="form-control @error('clientPhone') is-invalid @enderror" wire:model="clientPhone" @disabled(! $canManageClient)>
                                    @error('clientPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>PIC Name</label>
                                    <input type="text" class="form-control @error('clientPicName') is-invalid @enderror" wire:model="clientPicName" @disabled(! $canManageClient)>
                                    @error('clientPicName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Logo URL / Path</label>
                                    <input type="text" class="form-control @error('clientLogo') is-invalid @enderror" wire:model="clientLogo" @disabled(! $canManageClient)>
                                    @error('clientLogo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @if ($canManageClient)
                            <div class="text-right">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="ri-building-4-line align-middle mr-1"></i> Simpan Client
                                </button>
                            </div>
                        @else
                            <div class="text-muted small">Data client dipakai bersama oleh semua user login, jadi hanya role admin yang boleh mengubahnya.</div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title mb-0">Admin Controls</h4>
                        <span class="badge {{ $canManageClient ? 'badge-soft-danger' : 'badge-soft-secondary' }}">
                            {{ $canManageClient ? 'Admin only' : 'Read only' }}
                        </span>
                    </div>

                    <form wire:submit="saveAdminControls">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Account Status</label>
                                    <select class="form-control @error('accountStatus') is-invalid @enderror" wire:model="accountStatus" @disabled(! $canManageClient)>
                                        @foreach ($accountStatusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('accountStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Approved At</label>
                                    <input type="datetime-local" class="form-control @error('approvedAt') is-invalid @enderror" wire:model="approvedAt" @disabled(! $canManageClient)>
                                    @error('approvedAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Subscription Status</label>
                                    <select class="form-control @error('subscriptionStatus') is-invalid @enderror" wire:model="subscriptionStatus" @disabled(! $canManageClient)>
                                        @foreach ($subscriptionStatusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('subscriptionStatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Payment Method</label>
                                    <input type="text" class="form-control @error('paymentMethod') is-invalid @enderror" wire:model="paymentMethod" @disabled(! $canManageClient)>
                                    @error('paymentMethod') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label>Payment Reference</label>
                                    <input type="text" class="form-control @error('paymentReference') is-invalid @enderror" wire:model="paymentReference" @disabled(! $canManageClient)>
                                    @error('paymentReference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>Trial Ends</label>
                                    <input type="datetime-local" class="form-control @error('trialEndsAt') is-invalid @enderror" wire:model="trialEndsAt" @disabled(! $canManageClient)>
                                    @error('trialEndsAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>Subscribed At</label>
                                    <input type="datetime-local" class="form-control @error('subscribedAt') is-invalid @enderror" wire:model="subscribedAt" @disabled(! $canManageClient)>
                                    @error('subscribedAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>Subscription Ends</label>
                                    <input type="datetime-local" class="form-control @error('subscriptionEndsAt') is-invalid @enderror" wire:model="subscriptionEndsAt" @disabled(! $canManageClient)>
                                    @error('subscriptionEndsAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label>Payment Approved</label>
                                    <input type="datetime-local" class="form-control @error('paymentApprovedAt') is-invalid @enderror" wire:model="paymentApprovedAt" @disabled(! $canManageClient)>
                                    @error('paymentApprovedAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @if ($canManageClient)
                            <div class="text-right">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="ri-shield-keyhole-line align-middle mr-1"></i> Simpan Kontrol
                                </button>
                            </div>
                        @else
                            <div class="text-muted small">Status approval, subscription, dan payment hanya bisa diubah admin.</div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        @if ($client?->logo)
                            <img src="{{ $client->logo }}" alt="{{ $client->name }}" class="rounded mr-3" style="width: 56px; height: 56px; object-fit: cover;">
                        @else
                            <div class="avatar-sm mr-3">
                                <span class="avatar-title rounded bg-soft-primary text-primary font-size-20">
                                    {{ str($client?->name ?? $login->name)->substr(0, 1)->upper() }}
                                </span>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $client?->name ?? '-' }}</h5>
                            <div class="text-muted small">{{ $client?->email ?? '-' }}</div>
                            <div class="mt-2">
                                <span class="badge badge-soft-primary">{{ $login->role?->name ?? 'No Role' }}</span>
                                <span class="badge badge-soft-secondary">{{ $login->role?->code ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-nowrap mb-0">
                            <tbody>
                                <tr>
                                    <th class="border-top-0 text-muted">Account</th>
                                    <td class="border-top-0 text-right">{{ $accountStatusOptions[$client?->account_status] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Subscription</th>
                                    <td class="text-right">{{ $subscriptionStatusOptions[$client?->subscription_status] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Email Verified</th>
                                    <td class="text-right">{{ $login->email_verified_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Google</th>
                                    <td class="text-right">{{ $login->google_id ? 'Linked' : '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Provider</th>
                                    <td class="text-right">{{ $login->last_login_provider ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Last Login</th>
                                    <td class="text-right">{{ $login->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">IP</th>
                                    <td class="text-right">{{ $login->last_login_ip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Created</th>
                                    <td class="text-right">{{ $login->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Updated</th>
                                    <td class="text-right">{{ $login->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Security</h4>
                    <form wire:submit="changePassword">
                        <div class="form-group mb-3">
                            <label>Password Saat Ini</label>
                            <input type="password" class="form-control @error('currentPassword') is-invalid @enderror" wire:model="currentPassword">
                            @error('currentPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label>Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label>Konfirmasi</label>
                            <input type="password" class="form-control @error('passwordConfirmation') is-invalid @enderror" wire:model="passwordConfirmation">
                            @error('passwordConfirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-outline-primary btn-block">
                            <i class="ri-lock-password-line align-middle mr-1"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
