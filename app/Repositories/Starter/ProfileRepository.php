<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ProfileInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;

class ProfileRepository implements ProfileInterface
{
    public function updateLogin(UserLogin $login, array $data): UserLogin
    {
        $login->forceFill($data)->save();

        return $login->refresh();
    }

    public function updateClient(User $client, array $data): User
    {
        $client->forceFill($data)->save();

        return $client->refresh();
    }

    public function updatePassword(UserLogin $login, string $password): void
    {
        $login->forceFill([
            'password' => $password,
        ])->save();
    }
}
