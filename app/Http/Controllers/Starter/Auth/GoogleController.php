<?php

namespace App\Http\Controllers\Starter\Auth;

use App\Http\Controllers\Controller;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Models\Starter\Package;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('auth.login')->with('starter-auth-message', 'Google login is not configured yet.');
        }

        $packageCode = (string) $request->query('package', '');

        if ($packageCode !== '' && Package::query()->active()->where('code', $packageCode)->exists()) {
            $request->session()->put('starter_register_package_code', $packageCode);
        }

        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(NavigationAuthorizedRedirectService $redirects): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (Throwable) {
            return redirect()->route('auth.login')->with('starter-auth-message', 'Google login failed. Please try again.');
        }

        $email = str((string) $googleUser->getEmail())->lower()->trim()->toString();

        if ($email === '') {
            return redirect()->route('auth.login')->with('starter-auth-message', 'Google account does not provide an email address.');
        }

        $packageCode = (string) request()->session()->pull('starter_register_package_code', '');

        $login = DB::transaction(function () use ($googleUser, $email, $packageCode): ClientLogin {
            $login = ClientLogin::query()->where('google_id', $googleUser->getId())->first()
                ?? ClientLogin::query()->where('email', $email)->first();

            if (! $login instanceof ClientLogin) {
                $package = Package::query()->active()->where('code', $packageCode)->first()
                    ?? Package::query()->active()->where('code', 'trial')->first();

                $client = Client::query()->create([
                    'name' => $googleUser->getName() ? $googleUser->getName().' Workspace' : 'Google Workspace',
                    'email' => $email,
                    'package_id' => $package?->id,
                    'pic_name' => $googleUser->getName() ?: $email,
                    'account_status' => 'approved',
                    'approved_at' => now(),
                    'subscription_status' => $package?->type === 'trial' ? 'trialing' : 'pending_approval',
                    'trial_ends_at' => $package?->type === 'trial' ? now()->addDays($package->trial_days ?: 14) : null,
                ]);

                $role = ClientRole::query()->create([
                    'client_id' => $client->id,
                    'code' => 'admin',
                    'name' => 'Admin',
                    'desc' => 'Client administrator with full access.',
                ]);

                $login = ClientLogin::query()->create([
                    'client_id' => $client->id,
                    'client_role_id' => $role->id,
                    'name' => $googleUser->getName() ?: $email,
                    'email' => $email,
                    'email_verified_at' => now(),
                ]);
            }

            $login->forceFill([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $login->email_verified_at ?: now(),
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
                'last_login_provider' => 'google',
            ])->save();

            return $login->fresh(['client', 'role']);
        });

        Auth::login($login);
        request()->session()->regenerate();

        return redirect($redirects->forLogin($login));
    }
}
