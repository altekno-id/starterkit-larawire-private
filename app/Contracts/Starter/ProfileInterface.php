<?php

namespace App\Contracts\Starter;

use App\Models\Starter\User;
use App\Models\Starter\UserLogin;

interface ProfileInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function updateLogin(UserLogin $login, array $data): UserLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateClient(User $client, array $data): User;

    public function updatePassword(UserLogin $login, string $password): void;
}
