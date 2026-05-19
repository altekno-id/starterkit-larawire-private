<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ManagedUserInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use Illuminate\Support\Collection;

class ManagedUserRepository implements ManagedUserInterface
{
    public function forUser(User $user): Collection
    {
        return UserLogin::query()
            ->with('role')
            ->whereBelongsTo($user)
            ->orderBy('name')
            ->get();
    }

    public function findForUser(User $user, int $id): ?UserLogin
    {
        return UserLogin::query()
            ->with('role')
            ->whereBelongsTo($user)
            ->whereKey($id)
            ->first();
    }

    public function create(User $user, array $data): UserLogin
    {
        return $user->logins()->create($data);
    }

    public function update(UserLogin $login, array $data): UserLogin
    {
        $login->forceFill($data)->save();

        return $login->refresh();
    }

    public function delete(UserLogin $login): void
    {
        $login->delete();
    }
}
