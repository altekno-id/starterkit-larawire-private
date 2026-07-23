<?php

namespace App\Livewire\Starter\Settings;

use App\Models\Starter\App as StarterApp;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Pengaturan')]
class SettingsIndex extends Component
{
    public string $section = 'roles';

    public function mount(): void
    {
        $section = (string) request()->query('section', 'roles');
        $this->section = in_array($section, ['roles', 'users', 'company', 'security'], true) ? $section : 'roles';
    }

    public function render()
    {
        $login = auth()->user();
        abort_unless(
            $login instanceof ClientLogin
                && ($login->loadMissing('role')->role?->canManageSettings() ?? false),
            403,
        );

        $client = Client::query()->first();
        abort_unless($client instanceof Client, 403);

        $canSeeSystemAccounts = $login->role?->isSuperuser() ?? false;
        $roleCountQuery = ClientRole::query();
        $userCountQuery = ClientLogin::query();

        if (! $canSeeSystemAccounts) {
            $roleCountQuery->where('is_system', false)->where('code', '!=', 'superuser');
            $userCountQuery->whereHas('role', fn ($query) => $query
                ->where('is_system', false)
                ->where('code', '!=', 'superuser'));
        }

        return view('starter.settings.settings-index', [
            'client' => $client,
            'roleCount' => $roleCountQuery->count(),
            'userCount' => $userCountQuery->count(),
            'appCount' => StarterApp::query()->count(),
        ]);
    }
}
