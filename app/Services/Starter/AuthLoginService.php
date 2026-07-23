<?php

namespace App\Services\Starter;

use App\Contracts\Starter\ClientLoginInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthLoginService
{
    public function __construct(
        private readonly ClientLoginInterface $clientLogins,
        private readonly NavigationAuthorizedRedirectService $redirects
    ) {}

    public function attempt(string $username, string $password, bool $remember = false, ?string $redirect = null): string
    {
        $username = str($username)->lower()->trim()->toString();
        $throttleKey = $username.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'form.username' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        $login = $this->clientLogins->findByColumn('username', $username, ['client', 'role']);

        if (! $login || ! $login->password || ! Hash::check($password, $login->password)) {
            RateLimiter::hit($throttleKey, 60);

            if ($login) {
                $this->clientLogins->update($login, [
                    'failed_login_count' => $login->failed_login_count + 1,
                ]);
            }

            throw ValidationException::withMessages([
                'form.username' => __('auth.failed'),
            ]);
        }

        if ($login->client?->account_status !== 'approved') {
            throw ValidationException::withMessages([
                'form.username' => 'Perusahaan tidak aktif atau belum disetujui.',
            ]);
        }

        if (! $login->isActive()) {
            throw ValidationException::withMessages([
                'form.username' => 'Akun tidak aktif atau sedang dikunci. Hubungi administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($login, $remember);

        $this->clientLogins->update($login, [
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'failed_login_count' => 0,
            'locked_until' => null,
        ]);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $authenticatedLogin = $login->fresh(['client', 'role']);

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
