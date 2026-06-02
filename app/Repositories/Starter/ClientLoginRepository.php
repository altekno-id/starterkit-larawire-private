<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ClientLoginInterface;
use App\Models\Starter\Client;
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

    public function forClient(Client $client, array $with = [], string $orderBy = 'name'): Collection
    {
        return ClientLogin::query()
            ->with($with)
            ->whereBelongsTo($client)
            ->orderBy($orderBy)
            ->get();
    }

    public function findForClient(Client $client, int $id, array $with = []): ?ClientLogin
    {
        return ClientLogin::query()
            ->with($with)
            ->whereBelongsTo($client)
            ->whereKey($id)
            ->first();
    }

    public function createForClient(Client $client, array $data): ClientLogin
    {
        return $client->logins()->create($data);
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
