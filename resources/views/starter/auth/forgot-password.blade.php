<div>
    <form wire:submit="sendResetLink" autocomplete="on">
        @if ($status)
            <div class="alert alert-success" role="alert">{{ $status }}</div>
        @endif

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email" placeholder="name@example.com" autofocus autocomplete="email">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-footer">
            <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Send Reset Link</span>
                <span wire:loading>Processing...</span>
            </button>
        </div>
    </form>

    <div class="text-center text-secondary mt-3">
        Remember your password? <a href="{{ route('auth.login') }}" wire:navigate>Login</a>
    </div>
</div>
