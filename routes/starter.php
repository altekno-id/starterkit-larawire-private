<?php

use App\Livewire\Starter\Logs\ActivityLogIndex;
use App\Livewire\Starter\Profile\EditMyProfile;
use App\Livewire\Starter\Settings\SettingsIndex;
use App\Livewire\Starter\UserManagement\RoleForm;
use App\Livewire\Starter\UserManagement\UserForm;
use Illuminate\Support\Facades\Route;

Route::name('starter.')
    ->middleware(['auth:web', 'starter.active', 'starter.password-change'])
    ->group(function (): void {
        Route::livewire('/profile/edit', EditMyProfile::class)->name('profile.edit');

        Route::livewire('/activity-logs', ActivityLogIndex::class)
            ->middleware('starter.logs')
            ->name('logs.index');

        Route::livewire('/settings', SettingsIndex::class)
            ->middleware('starter.admin')
            ->name('settings');

        Route::prefix('settings/roles')
            ->name('settings.roles.')
            ->middleware('starter.admin')
            ->group(function (): void {
                Route::livewire('/create', RoleForm::class)->name('create');
                Route::livewire('/{roleId}/edit', RoleForm::class)->name('edit');
            });

        Route::prefix('user-management')
            ->name('user-management.')
            ->middleware('starter.admin')
            ->group(function (): void {
                Route::get('/roles', fn () => redirect()->route('starter.settings', ['section' => 'roles']))->name('roles');
                Route::livewire('/users/create', UserForm::class)->name('users.create');
                Route::livewire('/users/{userLoginId}/edit', UserForm::class)->name('users.edit');
                Route::get('/users', fn () => redirect()->route('starter.settings', ['section' => 'users']))->name('users');
            });

        Route::get('/client-profile', fn () => redirect()->route('starter.settings', ['section' => 'company']))
            ->middleware('starter.admin')
            ->name('client-profile');
    });
