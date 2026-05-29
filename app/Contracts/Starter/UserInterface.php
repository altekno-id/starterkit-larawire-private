<?php

namespace App\Contracts\Starter;

use App\Models\Starter\User;

interface UserInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;
}
