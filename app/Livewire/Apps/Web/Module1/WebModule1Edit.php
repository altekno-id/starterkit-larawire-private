<?php

namespace App\Livewire\Apps\Web\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class WebModule1Edit extends Component
{
    public ?string $id = null;

    public string $routeName = '';

    public function mount(?string $id = null): void
    {
        $this->id = $id;
        $this->routeName = request()->route()?->getName() ?? 'Page';
    }

    public function render()
    {
        return view('apps.web.module1.web-module1-edit')->title('Edit Module 1');
    }
}
