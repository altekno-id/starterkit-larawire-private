<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\UserInterface;
use App\Models\Starter\User;

class UserRepository implements UserInterface
{
    public function update(User $user, array $data): User
    {
        $user->forceFill($data)->save();

        return $user->refresh();
    }
}
