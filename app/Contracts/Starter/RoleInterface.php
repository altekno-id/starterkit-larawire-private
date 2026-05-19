<?php

namespace App\Contracts\Starter;

use App\Models\Starter\AppMod;
use App\Models\Starter\User;
use App\Models\Starter\UserRole;
use Illuminate\Support\Collection;

interface RoleInterface
{
    /**
     * @return Collection<int, UserRole>
     */
    public function forUser(User $user): Collection;

    public function findForUser(User $user, int $id): ?UserRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): UserRole;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(UserRole $role, array $data): UserRole;

    /**
     * @param  array<int, int>  $moduleIds
     */
    public function syncModules(UserRole $role, array $moduleIds): void;

    public function delete(UserRole $role): void;

    /**
     * @return Collection<int, AppMod>
     */
    public function availableModules(): Collection;
}
