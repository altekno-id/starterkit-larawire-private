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
        return view('apps.app2.dashboard.app2-dashboard-index')->title('Dashboard');
    }
}
