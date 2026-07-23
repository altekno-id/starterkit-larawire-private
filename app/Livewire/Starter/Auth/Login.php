<?php

namespace App\Livewire\Starter\Auth;

use App\Services\Starter\AuthLoginService;
use App\Services\Starter\StarterConfigService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Login')]
class Login extends Component
{
    /**
     * @var array{username: string, password: string, remember: bool}
     */
    public array $form = [
        'username' => '',
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
            'form.username' => ['required', 'string', 'max:255'],
            'form.password' => ['required', 'string'],
        ], [], [
            'form.username' => 'username',
            'form.password' => 'password',
        ]);

        $target = $loginService->attempt(
            username: $this->form['username'],
            password: $this->form['password'],
            remember: $this->form['remember'],
            redirect: $this->redirect,
        );

        return $this->redirect($target);
    }

    public function render()
    {
        return view('starter.auth.login', [
            'rememberMeEnabled' => app(StarterConfigService::class)->boolean('security.remember_me_enabled'),
        ]);
    }
}
