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
    /**
     * @var array{credential: string, password: string, remember: bool}
     */
    public array $form = [
        'credential' => '',
        'password' => '',
        'remember' => false,
    ];

    public string $redirect = '';

    public function mount(): void
    {
        $this->redirect = request()->query('redirect', '');
    }

    public function authenticate(LoginService $loginService)
    {
        $this->validate([
            'form.credential' => ['required', 'string'],
            'form.password' => ['required', 'string'],
        ], [], [
            'form.credential' => 'username atau email',
            'form.password' => 'password',
        ]);

        $target = $loginService->attempt(
            credential: $this->form['credential'],
            password: $this->form['password'],
            remember: $this->form['remember'],
            redirect: $this->redirect,
        );

        return $this->redirect($target);
    }

    public function render()
    {
        return view('starter.auth.login');
    }
}
