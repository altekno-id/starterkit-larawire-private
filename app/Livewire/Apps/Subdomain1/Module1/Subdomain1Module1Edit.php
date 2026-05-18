<?php

namespace App\Livewire\Apps\Subdomain1\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Subdomain1Module1Edit extends Component
{
    public ?string $id = null;

    public string $routeName = '';

    public string $pageTitle = '';

    public function mount(?string $id = null): void
    {
        $this->id = $id;
        $this->routeName = request()->route()?->getName() ?? 'Page';
        $this->pageTitle = 'Module 1 Edit';
    }

    public function render()
    {
        return view('apps.subdomain1.module1.subdomain1-module1-edit')->title($this->pageTitle);
    }
}
