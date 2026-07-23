<?php

namespace App\Livewire\Apps\App1\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class App1DashboardIndex extends Component
{
    public function render()
    {
        $dashboardTitle = request()->routeIs('app1.dashboard.summary2') ? 'Summary 2' : 'Summary 1';

        return view('apps.app1.dashboard.app1-dashboard-index', compact('dashboardTitle'))->title($dashboardTitle);
    }
}
