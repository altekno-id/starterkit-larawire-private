<?php

namespace App\Contracts\Starter;

use App\Models\Starter\UserLogin;

interface UserLoginInterface
{
    public function findForLogin(string $field, string $credential): ?UserLogin;

    public function updateLastLogin(UserLogin $login, string $provider, ?string $ip): void;
}
