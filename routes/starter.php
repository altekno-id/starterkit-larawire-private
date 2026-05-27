<?php

use App\Livewire\Starter\Profile\EditMyProfile;
use App\Livewire\Starter\Settings\ClientProfile;
use App\Livewire\Starter\UserManagement\Roles;
use App\Livewire\Starter\UserManagement\Users;
use Illuminate\Support\Facades\Route;

Route::name('starter.')
    ->middleware('auth:web')
    ->group(function (): void {
        Route::livewire('/profile/edit', EditMyProfile::class)->name('profile.edit');

        Route::prefix('user-management')
            ->name('user-management.')
            ->middleware('starter.admin')
            ->group(function (): void {
                Route::livewire('/roles', Roles::class)->name('roles');
                Route::livewire('/users', Users::class)->name('users');
            });

        Route::livewire('/client-profile', ClientProfile::class)
            ->middleware('starter.admin')
            ->name('client-profile');
    });
