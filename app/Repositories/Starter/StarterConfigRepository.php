<?php

namespace App\Repositories\Starter;

use App\Contracts\Starter\StarterConfigInterface;
use App\Models\Starter\StarterConfig;

class StarterConfigRepository implements StarterConfigInterface
{
    public function findByKey(string $key): ?StarterConfig
    {
        return StarterConfig::query()->where('key', $key)->first();
    }

    public function findByKeyOrFail(string $key): StarterConfig
    {
        return StarterConfig::query()->where('key', $key)->firstOrFail();
    }

    public function updateValue(StarterConfig $config, string $value): StarterConfig
    {
        $config->forceFill(['value' => $value])->save();

        return $config;
    }
}
