<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\UserLoginInterface;
use App\Models\Starter\UserLogin;

class UserLoginRepository implements UserLoginInterface
{
    public function findForLogin(string $field, string $credential): ?UserLogin
    {
        return UserLogin::query()
            ->with(['user', 'role'])
            ->where($field, $credential)
            ->first();
    }

    public function updateLastLogin(UserLogin $login, string $provider, ?string $ip): void
    {
        $login->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'last_login_provider' => $provider,
        ])->save();
    }
}
