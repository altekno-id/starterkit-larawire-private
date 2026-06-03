<div>
    <form wire:submit="register" autocomplete="on">
        @if ($selectedPackage)
            <div class="card bg-primary-lt border-primary mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="small text-secondary">Selected package</div>
                            <div class="fw-semibold">{{ $selectedPackage->name }}</div>
                            <div class="small text-secondary">{{ $selectedPackage->priceLabel() }} · {{ $selectedPackage->billingLabel() }}</div>
                        </div>
                        <span class="badge bg-primary-lt text-primary text-uppercase">{{ $selectedPackage->type }}</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-3">
            <a class="btn btn-danger w-100 gap-3" href="{{ route('auth.google.redirect', ['package' => $packageCode]) }}">
                <span class="fs-2 fw-bold lh-1">G</span>
                <span class="vr opacity-75"></span>
                Register with Google
            </a>
        </div>

        <div class="hr-text">or</div>

        <div class="mb-3">
            <label class="form-label" for="client_name">Client Name</label>
            <input type="text" class="form-control @error('form.client_name') is-invalid @enderror" id="client_name" wire:model="form.client_name" placeholder="Company or workspace name" autocomplete="organization">
            @error('form.client_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="name">Display Name</label>
            <input type="text" class="form-control @error('form.name') is-invalid @enderror" id="name" wire:model="form.name" placeholder="Your name" autocomplete="name">
            @error('form.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control @error('form.email') is-invalid @enderror" id="email" wire:model="form.email" placeholder="name@example.com" autocomplete="email">
            @error('form.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control @error('form.password') is-invalid @enderror" id="password" wire:model="form.password" placeholder="Password" autocomplete="new-password">
                @error('form.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirmation</label>
                <input type="password" class="form-control @error('form.password_confirmation') is-invalid @enderror" id="password_confirmation" wire:model="form.password_confirmation" placeholder="Confirm password" autocomplete="new-password">
                @error('form.password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-footer">
            <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Create Account</span>
                <span wire:loading>Processing...</span>
            </button>
        </div>
    </form>

    <div class="text-center text-secondary mt-3">
        Already have an account? <a href="{{ route('auth.login') }}" wire:navigate>Login</a>
    </div>
</div>
