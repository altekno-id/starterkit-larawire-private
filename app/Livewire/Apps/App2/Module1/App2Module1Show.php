<?php

namespace App\Livewire\Apps\App2\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class App2Module1Show extends Component
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
        return view('apps.app2.module1.app2-module1-show')->title('Module 1 Detail');
    }
}
