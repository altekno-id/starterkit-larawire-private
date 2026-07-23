<?php

namespace App\Livewire\Apps\App2\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class App2DashboardIndex extends Component
{
    public function render()
    {
        $dashboardTitle = request()->routeIs('app2.dashboard.summary2') ? 'Summary 2' : 'Summary 1';

        return view('apps.app2.dashboard.app2-dashboard-index', compact('dashboardTitle'))->title($dashboardTitle);
    }
}
