<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ClientInterface;
use App\Models\Starter\Client;

class ClientRepository implements ClientInterface
{
    public function current(): Client
    {
        return Client::query()->firstOrFail();
    }

    public function updateProfile(Client $client, array $data): Client
    {
        $client->forceFill($data)->save();

        return $client;
    }
}
