<?php

namespace App\Livewire\Apps\Subdomain2\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Subdomain2Module1Create extends Component
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
        return view('apps.subdomain2.module1.subdomain2-module1-create')->title($this->pageTitle);
    }
}
