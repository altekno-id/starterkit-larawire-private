<?php

namespace App\Livewire\Apps\Subdomain1\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class Subdomain1DashboardIndex extends Component
{
    public function render()
    {
        return view('apps.subdomain1.dashboard.subdomain1-dashboard-index')->title('Dashboard');
    }
}
