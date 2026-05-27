<?php

namespace App\Livewire\Apps\Subdomain1\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Subdomain1Module1Index extends Component
{
    public string $routeName = '';

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName() ?? 'Page';
    }

    public function render()
    {
        return view('apps.subdomain1.module1.subdomain1-module1-index')->title('Module 1 Data');
    }
}
