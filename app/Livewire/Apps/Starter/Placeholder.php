<?php

namespace App\Livewire\Apps\Starter;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Placeholder extends Component
{
    public ?string $id = null;

    public string $routeName = '';

    public string $pageTitle = '';

    public function mount(?string $id = null): void
    {
        $this->id = $id;
        $this->routeName = request()->route()?->getName() ?? 'Page';
        $this->pageTitle = str($this->routeName)->after('.')->replace('.', ' / ')->headline()->toString();
    }

    public function render()
    {
        return view('apps.starter.placeholder')->title($this->pageTitle);
    }
}
