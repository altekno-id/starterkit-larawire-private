<?php

namespace App\Livewire\Starter\Auth;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Services\Starter\StarterConfigService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::auth')]
#[Title('Layar Dikunci')]
class LockScreen extends Component
{
    public string $password = '';

    public function mount(StarterConfigService $configs): mixed
    {
        $login = $this->login();

        if (! $configs->boolean('security.lock_screen_enabled')) {
            $this->clearLockState();

            return $this->redirect(
                app(NavigationAuthorizedRedirectService::class)->firstAuthorizedUrl($login),
            );
        }

        $redirect = request()->query('redirect');

        if (is_string($redirect) && StarterNavigation::isSafeRedirect($redirect)) {
            session()->put('starter.lock.intended', $redirect);
        }

        session()->put('starter.locked', true);

        return null;
    }

    public function unlock(
        StarterConfigService $configs,
        NavigationAuthorizedRedirectService $redirects,
    ): mixed {
        $this->validate([
            'password' => ['required', 'string'],
        ], [], [
            'password' => 'password',
        ]);

        $login = $this->login();
        $throttleKey = 'lock-screen|'.$login->getKey().'|'.request()->ip();
        $maxAttempts = max(1, min(20, $configs->integer('security.login_max_attempts')));
        $decaySeconds = max(30, min(3600, $configs->integer('security.login_decay_seconds')));

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            throw ValidationException::withMessages([
                'password' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ]);
        }

        if (! $login->password || ! Hash::check($this->password, $login->password)) {
            RateLimiter::hit($throttleKey, $decaySeconds);
            $this->reset('password');

            throw ValidationException::withMessages([
                'password' => 'Password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $intended = session()->pull('starter.lock.intended');
        $this->clearLockState();
        session()->regenerate();

        return $this->redirect($redirects->forLogin($login, null, $intended));
    }

    public function render()
    {
        return view('starter.auth.lock-screen', [
            'login' => $this->login(),
        ]);
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof ClientLogin, 403);

        return $login->loadMissing('role');
    }

    private function clearLockState(): void
    {
        session()->forget('starter.locked');
        session()->put('starter.last_activity_at', now()->timestamp);
    }
}
