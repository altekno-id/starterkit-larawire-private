<?php

namespace App\Contracts\Starter;

use App\Models\Starter\Client;

interface ClientInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Client $client, array $data): Client;
}
