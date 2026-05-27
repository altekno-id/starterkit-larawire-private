<?php

namespace App\Livewire\Apps\Web\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dasbor')]
class WebDashboardIndex extends Component
{
    public function render()
    {
        return view('apps.web.dashboard.web-dashboard-index')->title('Dasbor');
    }
}
