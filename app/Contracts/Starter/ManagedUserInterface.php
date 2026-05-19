<?php

namespace App\Contracts\Starter;

use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use Illuminate\Support\Collection;

interface ManagedUserInterface
{
    /**
     * @return Collection<int, UserLogin>
     */
    public function forUser(User $user): Collection;

    public function findForUser(User $user, int $id): ?UserLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): UserLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UserLogin $login, array $data): UserLogin;

    public function delete(UserLogin $login): void;
}
