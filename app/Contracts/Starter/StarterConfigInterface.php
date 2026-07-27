<?php

namespace App\Contracts\Starter;

use App\Models\Starter\StarterConfig;

interface StarterConfigInterface
{
    public function findByKey(string $key): ?StarterConfig;

    public function findByKeyOrFail(string $key): StarterConfig;

    public function updateValue(StarterConfig $config, string $value): StarterConfig;
}
