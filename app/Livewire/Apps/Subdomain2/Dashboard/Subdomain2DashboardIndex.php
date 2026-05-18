<?php

namespace App\Livewire\Apps\Subdomain2\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class Subdomain2DashboardIndex extends Component
{
    public function render()
    {
        return view('apps.subdomain2.dashboard.subdomain2-dashboard-index');
    }
}
