<div>
    <form wire:submit="authenticate" autocomplete="on">
        <div class="mb-3">
            <a class="btn btn-danger w-100 gap-3" href="{{ route('auth.google.redirect') }}">
                <span class="fs-2 fw-bold lh-1">G</span>
                <span class="vr opacity-75"></span>
                Login with Google
            </a>
        </div>

        <div class="hr-text">or</div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control @error('form.email') is-invalid @enderror" id="email" wire:model="form.email" placeholder="name@example.com" autofocus autocomplete="email">
            @error('form.email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-2">
            <label class="form-label" for="password">
                Password
                <span class="form-label-description">
                    <a href="{{ route('auth.password.request') }}" wire:navigate>Forgot password?</a>
                </span>
            </label>
            <input type="password" class="form-control @error('form.password') is-invalid @enderror" id="password" wire:model="form.password" placeholder="Password" autocomplete="current-password">
            @error('form.password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <label class="form-check mb-4">
            <input type="checkbox" class="form-check-input" id="remember" wire:model="form.remember">
            <span class="form-check-label">Remember me</span>
        </label>

        <div class="form-footer">
            <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Login</span>
                <span wire:loading>Processing...</span>
            </button>
        </div>
    </form>

    <div class="text-center text-secondary mt-3">
        Don't have an account yet? <a href="{{ route('auth.register') }}" wire:navigate>Register</a>
    </div>
</div>
