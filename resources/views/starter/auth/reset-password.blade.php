<form wire:submit="resetPassword" autocomplete="on">
    @if ($status)
        <div class="alert alert-danger" role="alert">{{ $status }}</div>
    @endif

    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email" placeholder="name@example.com" autocomplete="email">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">New Password</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model="password" placeholder="New password" autocomplete="new-password">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="password_confirmation">Confirmation</label>
        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" wire:model="password_confirmation" placeholder="Confirm new password" autocomplete="new-password">
        @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-footer">
        <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove>Reset Password</span>
            <span wire:loading>Processing...</span>
        </button>
    </div>
</form>
