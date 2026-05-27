<?php

namespace App\Services\Starter\Profile;

use App\Contracts\Starter\ProfileInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function __construct(
        private readonly ProfileInterface $profiles
    ) {}

    /**
     * @param  array{name: string, username: string, email: string, profile_photo?: ?string}  $data
     */
    public function updateProfile(UserLogin $login, array $data): UserLogin
    {
        return $this->profiles->updateLogin($login, [
            'name' => $data['name'],
            'username' => str($data['username'])->lower()->trim()->toString(),
            'email' => str($data['email'])->lower()->trim()->toString(),
            'profile_photo' => $this->nullableTrim($data['profile_photo'] ?? null),
        ]);
    }

    /**
     * @param  array{name: string, email?: ?string, phone?: ?string, pic_name?: ?string, logo?: ?string}  $data
     */
    public function updateClientProfile(UserLogin $login, array $data): User
    {
        $this->ensureAdmin($login);

        return $this->profiles->updateClient($this->client($login), [
            'name' => trim($data['name']),
            'email' => $this->nullableTrim($data['email'] ?? null),
            'phone' => $this->nullableTrim($data['phone'] ?? null),
            'pic_name' => $this->nullableTrim($data['pic_name'] ?? null),
            'logo' => $this->nullableTrim($data['logo'] ?? null),
        ]);
    }

    public function changePassword(UserLogin $login, string $currentPassword, string $password): void
    {
        if (! $login->password || ! Hash::check($currentPassword, $login->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $this->profiles->updatePassword($login, $password);
    }

    private function client(UserLogin $login): User
    {
        $client = $login->user;

        abort_unless($client instanceof User, 403);

        return $client;
    }

    private function ensureAdmin(UserLogin $login): void
    {
        abort_unless($login->loadMissing('role')->role?->isAdmin(), 403);
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
