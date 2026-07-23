<?php

namespace App\Livewire\Apps\App1\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class App1Module1Show extends Component
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
        return view('apps.app1.module1.app1-module1-show')->title('Detail Module 1');
    }
}
