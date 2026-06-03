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
        return view('apps.app1.dashboard.app1-dashboard-index')->title('Dashboard');
    }
}
