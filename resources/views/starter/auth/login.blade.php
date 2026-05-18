<form class="form-horizontal" wire:submit="authenticate">
    <div class="form-group auth-form-group-custom mb-4">
        <i class="ri-user-2-line auti-custom-input-icon"></i>
        <label for="credential">Username atau Email</label>
        <input type="text" class="form-control @error('credential') is-invalid @enderror" id="credential" wire:model="credential" placeholder="Masukkan username atau email" autofocus>
        @error('credential')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group auth-form-group-custom mb-4">
        <i class="ri-lock-2-line auti-custom-input-icon"></i>
        <label for="password">Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model="password" placeholder="Masukkan password">
        @error('password')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="remember" wire:model="remember">
        <label class="custom-control-label" for="remember">Ingat saya</label>
    </div>

    <div class="mt-4 text-center">
        <button class="btn btn-primary w-md waves-effect waves-light" type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove>Login</span>
            <span wire:loading>Memproses...</span>
        </button>
    </div>
</form>
