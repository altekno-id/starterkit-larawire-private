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

    public function attempt(string $email, string $password, bool $remember = false, ?string $redirect = null): string
    {
        $email = str($email)->lower()->trim()->toString();
        $login = $this->clientLogins->findByColumn('email', $email, ['client', 'role']);

        if (! $login || ! $login->password || ! Hash::check($password, $login->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if ($login->client?->account_status !== 'approved') {
            throw ValidationException::withMessages([
                'email' => 'Client is not active or has not been approved.',
            ]);
        }

        Auth::login($login, $remember);

        $this->clientLogins->update($login, [
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'last_login_provider' => 'email',
        ]);

        request()->session()->regenerate();

        return $this->redirects->forLogin(
            $login->fresh(['client', 'role']),
            $redirect,
            session()->pull('url.intended'),
        );
    }
}
