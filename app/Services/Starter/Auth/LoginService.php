<?php

namespace App\Services\Starter\Auth;

use App\Contracts\Starter\UserLoginInterface;
use App\Services\Starter\Navigation\AuthorizedRedirectService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(
        private readonly UserLoginInterface $userLogins,
        private readonly AuthorizedRedirectService $redirects
    ) {}

    public function attempt(string $credential, string $password, bool $remember = false, ?string $redirect = null): string
    {
        $field = filter_var($credential, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $login = $this->userLogins->findForLogin($field, $credential);

        if (! $login || ! $login->password || ! Hash::check($password, $login->password)) {
            throw ValidationException::withMessages([
                'credential' => __('auth.failed'),
            ]);
        }

        if ($login->user?->account_status !== 'approved') {
            throw ValidationException::withMessages([
                'credential' => 'Client is not active or has not been approved.',
            ]);
        }

        Auth::login($login, $remember);

        $this->userLogins->updateLastLogin($login, $field, request()->ip());

        request()->session()->regenerate();

        return $this->redirects->forLogin(
            $login->fresh(['user', 'role']),
            $redirect,
            session()->pull('url.intended'),
        );
    }
}
