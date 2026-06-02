<?php

namespace App\Livewire\Starter\Auth;

use App\Services\Starter\AuthLoginService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Login')]
class Login extends Component
{
    /**
     * @var array{email: string, password: string, remember: bool}
     */
    public array $form = [
        'email' => '',
        'password' => '',
        'remember' => false,
    ];

    public string $redirect = '';

    public function mount(): void
    {
        $this->redirect = request()->query('redirect', '');
    }

    public function authenticate(AuthLoginService $loginService)
    {
        $this->validate([
            'form.email' => ['required', 'email'],
            'form.password' => ['required', 'string'],
        ], [], [
            'form.email' => 'email',
            'form.password' => 'password',
        ]);

        $target = $loginService->attempt(
            email: $this->form['email'],
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
