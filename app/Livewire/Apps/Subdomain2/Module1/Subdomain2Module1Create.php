<?php

namespace App\Livewire\Apps\Subdomain2\Module1;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class Subdomain2Module1Create extends Component
{
    public string $routeName = '';

    public function mount(): void
    {
        $this->routeName = request()->route()?->getName() ?? 'Page';
    }

    public function render()
    {
        return view('apps.subdomain2.module1.subdomain2-module1-create')->title('Tambah Modul 1');
    }
}
