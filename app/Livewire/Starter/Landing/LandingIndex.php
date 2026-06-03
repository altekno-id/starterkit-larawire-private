<?php

namespace App\Livewire\Starter\Landing;

use App\Models\Starter\Package;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::landing')]
#[Title('Starterpack')]
class LandingIndex extends Component
{
    public function render()
    {
        return view('starter.landing.index', [
            'packages' => Schema::hasTable('x_packages')
                ? Package::query()->active()->get()
                : collect(),
        ]);
    }
}
