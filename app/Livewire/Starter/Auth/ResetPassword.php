<?php

namespace App\Livewire\Starter\Auth;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Reset Password')]
class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $status = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(NavigationAuthorizedRedirectService $redirects)
    {
        $validated = $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:5', 'same:password_confirmation'],
            'password_confirmation' => ['required', 'string'],
        ], [], [
            'email' => 'email',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
        ]);

        $login = null;
        $status = Password::broker('starter_client_logins')->reset($validated, function (ClientLogin $clientLogin, string $password) use (&$login): void {
            $clientLogin->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($clientLogin));
            $login = $clientLogin;
        });

        if ($status !== Password::PASSWORD_RESET || ! $login instanceof ClientLogin) {
            $this->status = __($status);

            return null;
        }

        Auth::login($login);
        request()->session()->regenerate();

        return $this->redirect($redirects->forLogin($login->fresh(['client', 'role'])));
    }

    public function render()
    {
        return view('starter.auth.reset-password');
    }
}
