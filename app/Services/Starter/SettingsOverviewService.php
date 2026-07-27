<?php

namespace App\Services\Starter;

use App\Contracts\Starter\AppInterface;
use App\Contracts\Starter\ClientInterface;
use App\Contracts\Starter\ClientLoginInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;

class SettingsOverviewService
{
    public function __construct(
        private readonly ClientInterface $clients,
        private readonly ClientRoleInterface $roles,
        private readonly ClientLoginInterface $users,
        private readonly AppInterface $apps,
    ) {}

    /**
     * @return array{client: Client, roleCount: int, userCount: int, appCount: int}
     */
    public function forViewer(ClientLogin $viewer): array
    {
        abort_unless($viewer->role?->canManageSettings() ?? false, 403);

        return [
            'client' => $this->clients->current(),
            'roleCount' => $this->roles->countForViewer($viewer),
            'userCount' => $this->users->countForViewer($viewer),
            'appCount' => $this->apps->countRegistered(),
        ];
    }
}
