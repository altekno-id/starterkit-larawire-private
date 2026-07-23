<?php

namespace App\Livewire\Apps\App2\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class App2Module1Index extends Component
{
    public string $routeName = '';

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName() ?? 'Page';
    }

    public function render()
    {
        return view('apps.app2.module1.app2-module1-index')->title('Data Module 1');
    }
}
