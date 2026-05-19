<div>
    <div class="page-header d-print-none mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / Profile</div>
                <h2 class="page-title">Edit My Profile</h2>
                <div class="text-secondary mt-1">Kelola data login pribadi dan profil client yang terkait dengan akun ini.</div>
            </div>
        </div>
    </div>

    <div class="row row-cards">
        <div class="col-xl-8">
            <form class="card" wire:submit="saveAccount">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Public Account</h3>
                        <p class="card-subtitle">Data akun login yang boleh diubah semua user.</p>
                    </div>
                    <div class="card-actions">
                        <span class="badge bg-success-lt">Semua user</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama Login</label>
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
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Simpan Akun</button>
                </div>
            </form>

            <form class="card mt-3" wire:submit="saveClientProfile">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Client Profile</h3>
                        <p class="card-subtitle">Data client dipakai bersama oleh semua user login.</p>
                    </div>
                    <div class="card-actions">
                        <span class="badge {{ $canManageClient ? 'bg-primary-lt' : 'bg-secondary-lt' }}">
                            {{ $canManageClient ? 'Admin only' : 'Read only' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Client</label>
                            <input type="text" class="form-control @error('clientForm.name') is-invalid @enderror" wire:model="clientForm.name" @disabled(! $canManageClient)>
                            @error('clientForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Client</label>
                            <input type="email" class="form-control @error('clientForm.email') is-invalid @enderror" wire:model="clientForm.email" @disabled(! $canManageClient)>
                            @error('clientForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control @error('clientForm.phone') is-invalid @enderror" wire:model="clientForm.phone" @disabled(! $canManageClient)>
                            @error('clientForm.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PIC Name</label>
                            <input type="text" class="form-control @error('clientForm.pic_name') is-invalid @enderror" wire:model="clientForm.pic_name" @disabled(! $canManageClient)>
                            @error('clientForm.pic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Logo URL / Path</label>
                            <input type="text" class="form-control @error('clientForm.logo') is-invalid @enderror" wire:model="clientForm.logo" @disabled(! $canManageClient)>
                            @error('clientForm.logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    @if ($canManageClient)
                        <button type="submit" class="btn btn-outline-primary">Simpan Client</button>
                    @else
                        <span class="text-secondary">Hanya role admin yang boleh mengubah data client.</span>
                    @endif
                </div>
            </form>

            <form class="card mt-3" wire:submit="saveAdminControls">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Admin Controls</h3>
                        <p class="card-subtitle">Status approval, subscription, dan payment.</p>
                    </div>
                    <div class="card-actions">
                        <span class="badge {{ $canManageClient ? 'bg-danger-lt' : 'bg-secondary-lt' }}">
                            {{ $canManageClient ? 'Admin only' : 'Read only' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Account Status</label>
                            <select wire:key="profile-account-status-select" class="form-select @error('adminForm.account_status') is-invalid @enderror" wire:model="adminForm.account_status" @disabled(! $canManageClient)>
                                @foreach ($accountStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('adminForm.account_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Approved At</label>
                            <input type="datetime-local" class="form-control @error('adminForm.approved_at') is-invalid @enderror" wire:model="adminForm.approved_at" @disabled(! $canManageClient)>
                            @error('adminForm.approved_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Subscription Status</label>
                            <select wire:key="profile-subscription-status-select" class="form-select @error('adminForm.subscription_status') is-invalid @enderror" wire:model="adminForm.subscription_status" @disabled(! $canManageClient)>
                                @foreach ($subscriptionStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('adminForm.subscription_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Method</label>
                            <input type="text" class="form-control @error('adminForm.payment_method') is-invalid @enderror" wire:model="adminForm.payment_method" @disabled(! $canManageClient)>
                            @error('adminForm.payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" class="form-control @error('adminForm.payment_reference') is-invalid @enderror" wire:model="adminForm.payment_reference" @disabled(! $canManageClient)>
                            @error('adminForm.payment_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trial Ends</label>
                            <input type="datetime-local" class="form-control @error('adminForm.trial_ends_at') is-invalid @enderror" wire:model="adminForm.trial_ends_at" @disabled(! $canManageClient)>
                            @error('adminForm.trial_ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subscribed At</label>
                            <input type="datetime-local" class="form-control @error('adminForm.subscribed_at') is-invalid @enderror" wire:model="adminForm.subscribed_at" @disabled(! $canManageClient)>
                            @error('adminForm.subscribed_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subscription Ends</label>
                            <input type="datetime-local" class="form-control @error('adminForm.subscription_ends_at') is-invalid @enderror" wire:model="adminForm.subscription_ends_at" @disabled(! $canManageClient)>
                            @error('adminForm.subscription_ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Approved</label>
                            <input type="datetime-local" class="form-control @error('adminForm.payment_approved_at') is-invalid @enderror" wire:model="adminForm.payment_approved_at" @disabled(! $canManageClient)>
                            @error('adminForm.payment_approved_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    @if ($canManageClient)
                        <button type="submit" class="btn btn-outline-danger">Simpan Kontrol</button>
                    @else
                        <span class="text-secondary">Status approval dan subscription hanya bisa diubah admin.</span>
                    @endif
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        @if ($client?->logo)
                            <span class="avatar avatar-lg me-3" style="background-image: url({{ $client->logo }})"></span>
                        @else
                            <span class="avatar avatar-lg me-3">{{ str($client?->name ?? $login->name)->substr(0, 2)->upper() }}</span>
                        @endif
                        <div class="flex-fill">
                            <h3 class="mb-1">{{ $client?->name ?? '-' }}</h3>
                            <div class="text-secondary">{{ $client?->email ?? '-' }}</div>
                            <div class="mt-2">
                                <span class="badge bg-primary-lt">{{ $login->role?->name ?? 'No Role' }}</span>
                                <span class="badge bg-secondary-lt">{{ $login->role?->code ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-sm">
                        <tbody>
                            <tr>
                                <td class="text-secondary">Account</td>
                                <td class="text-end">{{ $accountStatusOptions[$client?->account_status] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Subscription</td>
                                <td class="text-end">{{ $subscriptionStatusOptions[$client?->subscription_status] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Email Verified</td>
                                <td class="text-end">{{ $login->email_verified_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Google</td>
                                <td class="text-end">{{ $login->google_id ? 'Linked' : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Provider</td>
                                <td class="text-end">{{ $login->last_login_provider ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Last Login</td>
                                <td class="text-end">{{ $login->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">IP</td>
                                <td class="text-end">{{ $login->last_login_ip ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Created</td>
                                <td class="text-end">{{ $login->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Updated</td>
                                <td class="text-end">{{ $login->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form class="card mt-3" wire:submit="changePassword">
                <div class="card-header">
                    <h3 class="card-title">Security</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control @error('passwordForm.current_password') is-invalid @enderror" wire:model="passwordForm.current_password">
                        @error('passwordForm.current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" class="form-control @error('passwordForm.password') is-invalid @enderror" wire:model="passwordForm.password">
                        @error('passwordForm.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi</label>
                        <input type="password" class="form-control @error('passwordForm.password_confirmation') is-invalid @enderror" wire:model="passwordForm.password_confirmation">
                        @error('passwordForm.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-outline-primary w-100">Ganti Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
