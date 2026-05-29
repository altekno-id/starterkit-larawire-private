<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\UserLoginInterface;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use Illuminate\Support\Collection;

class UserLoginRepository implements UserLoginInterface
{
    public function findByColumn(string $column, mixed $value, array $with = []): ?UserLogin
    {
        return UserLogin::query()
            ->with($with)
            ->where($column, $value)
            ->first();
    }

    public function forUser(User $user, array $with = [], string $orderBy = 'name'): Collection
    {
        return UserLogin::query()
            ->with($with)
            ->whereBelongsTo($user)
            ->orderBy($orderBy)
            ->get();
    }

    public function findForUser(User $user, int $id, array $with = []): ?UserLogin
    {
        return UserLogin::query()
            ->with($with)
            ->whereBelongsTo($user)
            ->whereKey($id)
            ->first();
    }

    public function createForUser(User $user, array $data): UserLogin
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
