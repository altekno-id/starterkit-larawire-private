<?php

namespace App\Livewire\Apps;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        return view('apps.dashboard');
    }
}
