<?php

namespace App\Livewire\Starter\Landing;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::landing')]
#[Title('Starterpack')]
class LandingIndex extends Component
{
    public function render()
    {
        return view('starter.landing.index');
    }
}
