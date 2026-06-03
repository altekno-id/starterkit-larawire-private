<?php

namespace App\Livewire\Apps\App1\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class App1Module1Edit extends Component
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
        return view('apps.app1.module1.app1-module1-edit')->title('Edit Module 1');
    }
}
