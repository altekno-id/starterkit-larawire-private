<?php

namespace App\Services\Starter;

use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Models\Starter\Package;
use Illuminate\Support\Facades\DB;

class AuthRegisterService
{
    /**
     * @param  array{client_name: string, name: string, email: string, password: string, package_code?: string|null}  $data
     */
    public function register(array $data): ClientLogin
    {
        return DB::transaction(function () use ($data): ClientLogin {
            $email = str($data['email'])->lower()->trim()->toString();
            $package = $this->package((string) ($data['package_code'] ?? ''));

            $client = Client::query()->create([
                'name' => trim($data['client_name']),
                'email' => $email,
                'package_id' => $package?->id,
                'pic_name' => trim($data['name']),
                'account_status' => 'approved',
                'approved_at' => now(),
                ...$this->subscriptionData($package),
            ]);

            $role = ClientRole::query()->create([
                'client_id' => $client->id,
                'code' => 'admin',
                'name' => 'Admin',
                'desc' => 'Client administrator with full access.',
            ]);

            return ClientLogin::query()->create([
                'client_id' => $client->id,
                'client_role_id' => $role->id,
                'name' => trim($data['name']),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => $data['password'],
                'last_login_provider' => 'email',
            ]);
        });
    }

    private function package(string $code): ?Package
    {
        return Package::query()
            ->active()
            ->where('code', $code)
            ->first();
    }

    /**
     * @return array{subscription_status: string, trial_ends_at: mixed, subscribed_at?: mixed}
     */
    private function subscriptionData(?Package $package): array
    {
        if (! $package instanceof Package) {
            return [
                'subscription_status' => 'none',
                'trial_ends_at' => null,
            ];
        }

        if ($package->type === 'trial') {
            return [
                'subscription_status' => 'trialing',
                'trial_ends_at' => now()->addDays($package->trial_days ?: 14),
            ];
        }

        if ($package->type === 'free') {
            return [
                'subscription_status' => 'active',
                'trial_ends_at' => null,
                'subscribed_at' => now(),
            ];
        }

        return [
            'subscription_status' => 'pending_approval',
            'trial_ends_at' => null,
        ];
    }
}
