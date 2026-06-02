<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\ClientInterface;
use App\Models\Starter\Client;

class ClientRepository implements ClientInterface
{
    public function update(Client $client, array $data): Client
    {
        $client->forceFill($data)->save();

        return $client->refresh();
    }
}
