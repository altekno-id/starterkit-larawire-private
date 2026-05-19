<?php

namespace App\Services\Starter\Profile;

use App\Contracts\Starter\ProfileInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function __construct(
        private readonly ProfileInterface $profiles
    ) {}

    /**
     * @param  array{name: string, username: string, email: string}  $data
     */
    public function updateProfile(UserLogin $login, array $data): UserLogin
    {
        return $this->profiles->updateLogin($login, [
            'name' => $data['name'],
            'username' => str($data['username'])->lower()->trim()->toString(),
            'email' => str($data['email'])->lower()->trim()->toString(),
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

    /**
     * @param  array{
     *     account_status: string,
     *     approved_at?: ?string,
     *     subscription_status: string,
     *     payment_method?: ?string,
     *     payment_reference?: ?string,
     *     trial_ends_at?: ?string,
     *     subscribed_at?: ?string,
     *     subscription_ends_at?: ?string,
     *     payment_approved_at?: ?string
     * }  $data
     */
    public function updateAdminControls(UserLogin $login, array $data): User
    {
        $this->ensureAdmin($login);

        return $this->profiles->updateClient($this->client($login), [
            'account_status' => $data['account_status'],
            'approved_at' => $this->nullableDate($data['approved_at'] ?? null),
            'subscription_status' => $data['subscription_status'],
            'payment_method' => $this->nullableTrim($data['payment_method'] ?? null),
            'payment_reference' => $this->nullableTrim($data['payment_reference'] ?? null),
            'trial_ends_at' => $this->nullableDate($data['trial_ends_at'] ?? null),
            'subscribed_at' => $this->nullableDate($data['subscribed_at'] ?? null),
            'subscription_ends_at' => $this->nullableDate($data['subscription_ends_at'] ?? null),
            'payment_approved_at' => $this->nullableDate($data['payment_approved_at'] ?? null),
        ]);
    }

    public function changePassword(UserLogin $login, string $currentPassword, string $password): void
    {
        if (! $login->password || ! Hash::check($currentPassword, $login->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
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

    private function nullableDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        return $value === '' ? null : Carbon::parse($value);
    }
}
