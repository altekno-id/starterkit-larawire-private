<?php

namespace App\Services\Starter\Auth;

use App\Contracts\Starter\UserLoginInterface;
use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(
        private readonly UserLoginInterface $userLogins
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
                'credential' => 'Client belum aktif atau belum disetujui.',
            ]);
        }

        Auth::login($login, $remember);

        $this->userLogins->updateLastLogin($login, $field, request()->ip());

        request()->session()->regenerate();

        return StarterNavigation::isSafeRedirect($redirect)
            ? $redirect
            : session()->pull('url.intended', route('web.dashboard'));
    }
}
