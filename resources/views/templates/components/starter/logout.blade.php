<?php

use App\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirect(StarterNavigation::authLoginUrl(url('/')));
    }
};
?>

<button type="button" class="dropdown-item text-danger" wire:click="logout" wire:loading.attr="disabled" wire:target="logout">
    <i class="ri-shut-down-line align-middle mr-1 text-danger"></i> Logout
</button>
