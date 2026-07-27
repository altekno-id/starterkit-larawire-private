<?php

namespace App\Livewire\Starter\Settings;

use App\Services\Starter\AuthenticatedLoginService;
use App\Services\Starter\SettingsOverviewService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Pengaturan')]
class SettingsIndex extends Component
{
    public string $section = 'roles';

    private SettingsOverviewService $overview;

    private AuthenticatedLoginService $authenticatedLogins;

    public function boot(
        SettingsOverviewService $overview,
        AuthenticatedLoginService $authenticatedLogins,
    ): void {
        $this->overview = $overview;
        $this->authenticatedLogins = $authenticatedLogins;
    }

    public function mount(): void
    {
        $section = (string) request()->query('section', 'roles');
        $this->section = in_array($section, ['roles', 'users', 'company', 'security'], true) ? $section : 'roles';
    }

    public function render()
    {
        $login = $this->authenticatedLogins->settingsManager();

        return view(
            'starter.settings.settings-index',
            $this->overview->forViewer($login),
        );
    }
}
