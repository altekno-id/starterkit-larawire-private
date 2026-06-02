<?php

namespace App\Services\Starter;

use App\Contracts\Starter\ClientLoginInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthLoginService
{
    public function __construct(
        private readonly ClientLoginInterface $clientLogins,
        private readonly NavigationAuthorizedRedirectService $redirects
    ) {}

    public function attempt(string $credential, string $password, bool $remember = false, ?string $redirect = null): string
    {
        $field = filter_var($credential, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $login = $this->clientLogins->findByColumn($field, $credential, ['client', 'role']);

        if (! $login || ! $login->password || ! Hash::check($password, $login->password)) {
            throw ValidationException::withMessages([
                'credential' => __('auth.failed'),
            ]);
        }

        if ($login->client?->account_status !== 'approved') {
            throw ValidationException::withMessages([
                'credential' => 'Client is not active or has not been approved.',
            ]);
        }

        Auth::login($login, $remember);

        $this->clientLogins->update($login, [
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'last_login_provider' => $field,
        ]);

        request()->session()->regenerate();

        return $this->redirects->forLogin(
            $login->fresh(['client', 'role']),
            $redirect,
            session()->pull('url.intended'),
        );
    }
}
