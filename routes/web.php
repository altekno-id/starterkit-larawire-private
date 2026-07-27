<?php

use App\Livewire\Landing\LandingIndex;
use Illuminate\Support\Facades\Route;

Route::livewire('/', LandingIndex::class)->name('landing');
