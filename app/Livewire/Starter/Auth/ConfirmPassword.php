<?php

namespace App\Livewire\Starter\Auth;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\AuditLogService;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Services\Starter\StarterConfigService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Konfirmasi Password')]
class ConfirmPassword extends Component
{
    public string $password = '';

    public function confirm(
        StarterConfigService $configs,
        NavigationAuthorizedRedirectService $redirects,
        AuditLogService $auditLogs,
    ): mixed {
        $this->validate([
            'password' => ['required', 'string'],
        ], [], [
            'password' => 'password',
        ]);

        $login = $this->login();
        $throttleKey = 'confirm-password|'.$login->getKey().'|'.request()->ip();
        $maxAttempts = max(1, min(20, $configs->integer('security.login_max_attempts')));
        $decaySeconds = max(30, min(3600, $configs->integer('security.login_decay_seconds')));

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $auditLogs->recordSecurityEvent(
                'auth.password_confirmation_blocked',
                'Konfirmasi password dibatasi sementara',
                target: $login,
                actor: $login,
                metadata: ['reason' => 'rate_limited'],
            );

            throw ValidationException::withMessages([
                'password' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        if (! $login->password || ! Hash::check($this->password, $login->password)) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $this->reset('password');
            $auditLogs->recordSecurityEvent(
                'auth.password_confirmation_failed',
                'Konfirmasi password gagal',
                target: $login,
                actor: $login,
                metadata: ['reason' => 'invalid_password'],
            );

            throw ValidationException::withMessages([
                'password' => 'Password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->passwordConfirmed();
        $intended = session()->pull('url.intended');

        $auditLogs->recordSecurityEvent(
            'auth.password_confirmation_succeeded',
            'Konfirmasi password berhasil',
            target: $login,
            actor: $login,
        );

        return $this->redirect($redirects->forLogin($login, null, $intended));
    }

    public function render()
    {
        return view('livewire.starter.auth.confirm-password', [
            'cancelUrl' => app(NavigationAuthorizedRedirectService::class)
                ->firstAuthorizedUrl($this->login()),
        ]);
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof ClientLogin, 403);

        return $login->loadMissing('role');
    }
}
