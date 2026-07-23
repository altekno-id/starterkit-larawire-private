<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ClientLoginInterface;
use App\Models\Starter\ClientLogin;
use Illuminate\Support\Collection;

class ClientLoginRepository implements ClientLoginInterface
{
    public function findByColumn(string $column, mixed $value, array $with = []): ?ClientLogin
    {
        return ClientLogin::query()
            ->with($with)
            ->where($column, $value)
            ->first();
    }

    public function all(array $with = [], string $orderBy = 'name'): Collection
    {
        return ClientLogin::query()
            ->with($with)
            ->orderBy($orderBy)
            ->get();
    }

    public function find(int $id, array $with = []): ?ClientLogin
    {
        return ClientLogin::query()
            ->with($with)
            ->whereKey($id)
            ->first();
    }

    public function create(array $data): ClientLogin
    {
        return ClientLogin::query()->create($data);
    }

    public function update(ClientLogin $login, array $data): ClientLogin
    {
        $login->forceFill($data)->save();

        return $login->refresh();
    }

    public function delete(ClientLogin $login): void
    {
        $login->delete();
    }
}
