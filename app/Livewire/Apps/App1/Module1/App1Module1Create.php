<?php

namespace App\Livewire\Apps\App1\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class App1Module1Create extends Component
{
    public string $routeName = '';

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName() ?? 'Page';
    }

    public function render()
    {
        return view('apps.app1.module1.app1-module1-create')->title('Create Module 1');
    }
}
