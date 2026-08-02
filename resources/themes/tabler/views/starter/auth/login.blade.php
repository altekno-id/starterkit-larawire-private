<div>
    <form wire:submit="authenticate" autocomplete="on">
        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input type="text" class="form-control @error('form.username') is-invalid @enderror" id="username" wire:model.defer="form.username" placeholder="Username" autofocus autocomplete="username">
            @error('form.username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-2">
            <label class="form-label" for="password">Password</label>
            <input type="password" class="form-control @error('form.password') is-invalid @enderror" id="password" wire:model.defer="form.password" placeholder="Password" autocomplete="current-password">
            @error('form.password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($rememberMeEnabled)
            <label class="form-check mb-4">
                <input type="checkbox" class="form-check-input" id="remember" wire:model.defer="form.remember">
                <span class="form-check-label">Ingat saya di perangkat ini</span>
            </label>
        @else
            <div class="mb-4"></div>
        @endif

        <div class="form-footer">
            <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Login</span>
                <span wire:loading>Memproses...</span>
            </button>
        </div>
    </form>

    <div class="text-center text-secondary mt-3">Hubungi administrator jika lupa password.</div>
</div>
