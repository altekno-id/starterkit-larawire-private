<?php

namespace App\Contracts\Starter;

use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use Illuminate\Support\Collection;

interface UserLoginInterface
{
    /**
     * @param  array<int, string>  $with
     */
    public function findByColumn(string $column, mixed $value, array $with = []): ?UserLogin;

    /**
     * @param  array<int, string>  $with
     * @return Collection<int, UserLogin>
     */
    public function forUser(User $user, array $with = [], string $orderBy = 'name'): Collection;

    /**
     * @param  array<int, string>  $with
     */
    public function findForUser(User $user, int $id, array $with = []): ?UserLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForUser(User $user, array $data): UserLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UserLogin $login, array $data): UserLogin;

    public function delete(UserLogin $login): void;
}
