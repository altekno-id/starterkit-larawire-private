<?php

namespace App\Livewire\Starter\Auth;

use App\Services\Starter\Auth\LoginService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Login')]
class Login extends Component
{
    public string $credential = '';

    public string $password = '';

    public bool $remember = false;

    public string $redirect = '';

    public function mount(): void
    {
        $this->redirect = request()->query('redirect', '');
    }

    public function authenticate(LoginService $loginService)
    {
        $this->validate([
            'credential' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [], [
            'credential' => 'username atau email',
            'password' => 'password',
        ]);

        $target = $loginService->attempt(
            credential: $this->credential,
            password: $this->password,
            remember: $this->remember,
            redirect: $this->redirect,
        );

        return $this->redirect($target);
    }

    public function render()
    {
        return view('starter.auth.login');
    }
}
