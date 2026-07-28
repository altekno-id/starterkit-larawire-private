<?php

namespace Altekno\StarterKit\Contracts\Starter;

use Altekno\StarterKit\Models\Starter\ClientLogin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClientLoginInterface
{
    public function findByUsername(string $username): ?ClientLogin;

    /**
     * @return LengthAwarePaginator<int, ClientLogin>
     */
    public function paginateForViewer(
        ClientLogin $viewer,
        string $search,
        string $status,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator;

    public function findBasicById(int $id): ?ClientLogin;

    public function findForManagement(int $id): ?ClientLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): ClientLogin;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(ClientLogin $login, array $data): ClientLogin;

    public function refreshWithRole(ClientLogin $login): ClientLogin;

    public function loadRole(ClientLogin $login): ClientLogin;

    public function countForViewer(ClientLogin $viewer): int;

    public function revokeRememberTokens(): int;
}
