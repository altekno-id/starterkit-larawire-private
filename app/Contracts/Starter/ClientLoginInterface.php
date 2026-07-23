<?php

namespace App\Contracts\Starter;

use App\Models\Starter\ClientLogin;
use Illuminate\Support\Collection;

interface ClientLoginInterface
{
    /**
     * @param  array<int, string>  $with
     */
    public function findByColumn(string $column, mixed $value, array $with = []): ?ClientLogin;

    /**
     * @param  array<int, string>  $with
     * @return Collection<int, ClientLogin>
     */
    public function all(array $with = [], string $orderBy = 'name'): Collection;

    /**
     * @param  array<int, string>  $with
     */
    public function find(int $id, array $with = []): ?ClientLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ClientLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ClientLogin $login, array $data): ClientLogin;

    public function delete(ClientLogin $login): void;
}
