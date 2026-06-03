<?php

namespace App\Livewire\Starter\Auth;

use App\Models\Starter\Package;
use App\Services\Starter\AuthRegisterService;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Register')]
class Register extends Component
{
    /**
     * @var array{client_name: string, name: string, email: string, password: string, password_confirmation: string}
     */
    public array $form = [
        'client_name' => '',
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public string $packageCode = '';

    public function mount(): void
    {
        if (! Schema::hasTable('x_packages')) {
            return;
        }

        $requestedCode = (string) request()->query('package', '');
        $package = Package::query()
            ->active()
            ->when($requestedCode !== '', fn ($query) => $query->where('code', $requestedCode))
            ->first();

        $this->packageCode = $package?->code
            ?? Package::query()->active()->where('code', 'trial')->value('code')
            ?? Package::query()->active()->value('code')
            ?? '';
    }

    public function register(AuthRegisterService $registerService, NavigationAuthorizedRedirectService $redirects)
    {
        $validated = $this->validate([
            'form.client_name' => ['required', 'string', 'max:255'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('starter_clients', 'email'),
                Rule::unique('starter_client_logins', 'email'),
            ],
            'form.password' => ['required', 'string', 'min:5', 'same:form.password_confirmation'],
            'form.password_confirmation' => ['required', 'string'],
            'packageCode' => ['required', 'string', Rule::exists('x_packages', 'code')->where('is_active', true)],
        ], [], [
            'form.client_name' => 'client name',
            'form.name' => 'display name',
            'form.email' => 'email',
            'form.password' => 'password',
            'form.password_confirmation' => 'password confirmation',
            'packageCode' => 'package',
        ])['form'];

        $validated['package_code'] = $this->packageCode;

        $login = $registerService->register($validated);

        Auth::login($login);
        request()->session()->regenerate();

        return $this->redirect($redirects->forLogin($login->fresh(['client', 'role'])));
    }

    public function render()
    {
        return view('starter.auth.register', [
            'selectedPackage' => Schema::hasTable('x_packages')
                ? Package::query()->active()->where('code', $this->packageCode)->first()
                : null,
        ]);
    }
}
