<?php

namespace App\Livewire\Apps\Web\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class WebModule1Index extends Component
{
    public string $routeName = '';

    public string $pageTitle = '';

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName() ?? 'Page';
        $this->pageTitle = 'Module 1 Index';
    }

    public function render()
    {
        return view('apps.web.module1.web-module1-index')->title($this->pageTitle);
    }
}
