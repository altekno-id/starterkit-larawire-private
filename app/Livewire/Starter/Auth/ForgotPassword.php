<?php

namespace App\Livewire\Starter\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Forgot Password')]
class ForgotPassword extends Component
{
    public string $email = '';

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
        ], [], [
            'email' => 'email',
        ]);

        $status = Password::broker('starter_client_logins')->sendResetLink([
            'email' => str($validated['email'])->lower()->trim()->toString(),
        ]);

        $this->status = $status === Password::RESET_LINK_SENT
            ? __($status)
            : __('If the email is registered, a password reset link will be sent.');
    }

    public function render()
    {
        return view('starter.auth.forgot-password');
    }
}
