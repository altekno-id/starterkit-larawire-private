<?php

namespace App\Services\Starter;

use App\Contracts\Starter\ClientInterface;
use App\Contracts\Starter\ClientLoginInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthLoginService
{
    public function __construct(
        private readonly ClientLoginInterface $clientLogins,
        private readonly NavigationAuthorizedRedirectService $redirects,
        private readonly ClientInterface $clients,
        private readonly StarterConfigService $configs,
        private readonly AuditLogService $auditLogs,
    ) {}

    public function attempt(string $username, string $password, bool $remember = false, ?string $redirect = null): string
    {
        $username = str($username)->lower()->trim()->toString();
        $throttleKey = $username.'|'.request()->ip();

        $maxAttempts = max(1, min(20, $this->configs->integer('security.login_max_attempts')));
        $decaySeconds = max(30, min(3600, $this->configs->integer('security.login_decay_seconds')));

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $this->auditLogs->recordSecurityEvent(
                'auth.login_blocked',
                'Login dibatasi sementara',
                metadata: ['reason' => 'rate_limited'],
            );

            throw ValidationException::withMessages([
                'form.username' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        $login = $this->clientLogins->findByColumn('username', $username, ['role']);

        if (! $login || ! $login->password || ! Hash::check($password, $login->password)) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $failedLoginCount = null;

            if ($login) {
                $failedLoginCount = $login->failed_login_count + 1;
                $this->clientLogins->update($login, [
                    'failed_login_count' => $failedLoginCount,
                    'locked_until' => $failedLoginCount >= $maxAttempts
                        ? now()->addSeconds($decaySeconds)
                        : $login->locked_until,
                ]);
            }

            $this->auditLogs->recordSecurityEvent(
                $failedLoginCount !== null && $failedLoginCount >= $maxAttempts
                    ? 'auth.login_locked'
                    : 'auth.login_failed',
                $failedLoginCount !== null && $failedLoginCount >= $maxAttempts
                    ? 'Akun dikunci setelah login gagal'
                    : 'Percobaan login gagal',
                target: $login,
                metadata: array_filter([
                    'reason' => 'invalid_credentials',
                    'failed_login_count' => $failedLoginCount,
                ], fn (mixed $value): bool => $value !== null),
            );

            throw ValidationException::withMessages([
                'form.username' => __('auth.failed'),
            ]);
        }

        if ($this->clients->current()->account_status !== 'approved') {
            $this->auditLogs->recordSecurityEvent(
                'auth.login_blocked',
                'Login ditolak karena perusahaan tidak aktif',
                target: $login,
                metadata: ['reason' => 'company_inactive'],
            );

            throw ValidationException::withMessages([
                'form.username' => 'Perusahaan tidak aktif atau belum disetujui.',
            ]);
        }

        if (! $login->isActive()) {
            $this->auditLogs->recordSecurityEvent(
                'auth.login_blocked',
                'Login ditolak karena akun tidak aktif',
                target: $login,
                metadata: ['reason' => 'account_inactive'],
            );

            throw ValidationException::withMessages([
                'form.username' => 'Akun tidak aktif atau sedang dikunci. Hubungi administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($login, $remember && $this->configs->boolean('security.remember_me_enabled'));

        $this->clientLogins->update($login, [
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_count' => 0,
            'locked_until' => null,
        ]);

        if (request()->hasSession()) {
            request()->session()->regenerate();
            request()->session()->forget(['starter.locked', 'starter.lock.intended']);
            request()->session()->put('starter.last_activity_at', now()->timestamp);
            request()->session()->passwordConfirmed();
        }

        $authenticatedLogin = $login->fresh('role');
        $this->auditLogs->recordSecurityEvent(
            'auth.login_succeeded',
            'Login berhasil',
            target: $authenticatedLogin,
            actor: $authenticatedLogin,
        );

        if ($authenticatedLogin->must_change_password) {
            return $this->redirects->firstAuthorizedUrl($authenticatedLogin);
        }

        return $this->redirects->forLogin(
            $authenticatedLogin,
            $redirect,
            session()->pull('url.intended'),
        );
    }
}
