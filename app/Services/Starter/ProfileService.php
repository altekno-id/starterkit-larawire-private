<?php

namespace App\Services\Starter;

use App\Contracts\Starter\ClientInterface;
use App\Contracts\Starter\ClientLoginInterface;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function __construct(
        private readonly ClientInterface $clients,
        private readonly ClientLoginInterface $clientLogins
    ) {}

    /**
     * @param  array{name: string, email: string, profile_photo?: ?string}  $data
     */
    public function updateProfile(ClientLogin $login, array $data): ClientLogin
    {
        return $this->clientLogins->update($login, [
            'name' => trim($data['name']),
            'email' => str($data['email'])->lower()->trim()->toString(),
            'profile_photo' => $this->nullableTrim($data['profile_photo'] ?? null),
        ]);
    }

    /**
     * @param  array{name: string, email?: ?string, phone?: ?string, pic_name?: ?string, logo?: ?string}  $data
     */
    public function updateClientProfile(ClientLogin $login, array $data): Client
    {
        $this->ensureAdmin($login);

        return $this->clients->update($this->client($login), [
            'name' => trim($data['name']),
            'email' => $this->nullableTrim($data['email'] ?? null),
            'phone' => $this->nullableTrim($data['phone'] ?? null),
            'pic_name' => $this->nullableTrim($data['pic_name'] ?? null),
            'logo' => $this->nullableTrim($data['logo'] ?? null),
        ]);
    }

    public function changePassword(ClientLogin $login, string $currentPassword, string $password): void
    {
        if (! $login->password || ! Hash::check($currentPassword, $login->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $this->clientLogins->update($login, [
            'password' => $password,
            'must_change_password' => false,
            'password_changed_at' => now(),
            'failed_login_count' => 0,
            'locked_until' => null,
            'remember_token' => Str::random(60),
        ]);
    }

    private function client(ClientLogin $login): Client
    {
        $client = $login->client;

        abort_unless($client instanceof Client, 403);

        return $client;
    }

    private function ensureAdmin(ClientLogin $login): void
    {
        abort_unless($login->loadMissing('role')->role?->canManageSettings(), 403);
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
