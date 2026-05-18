<?php

namespace App\Livewire\Apps\Subdomain1\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Subdomain1Module1Create extends Component
{
    public string $routeName = '';

    public string $pageTitle = '';

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName() ?? 'Page';
        $this->pageTitle = 'Module 1 Create';
    }

    public function render()
    {
        return view('apps.subdomain1.module1.subdomain1-module1-create')->title($this->pageTitle);
    }
}
