<?php

use Illuminate\Support\Facades\Route;

Route::name('subdomain1.')->group(function () {
    Route::get('/', function () {
        return 'Subdomain 1 - Dashboard';
    })->name('dashboard');

    Route::prefix('module-1')->name('module1.')->group(function () {
        Route::get('/', function () {
            return 'Subdomain 1 - Modul 1 Data';
        })->name('index');

        Route::get('/create', function () {
            return 'Subdomain 1 - Modul 1 Form';
        })->name('create');

        Route::post('/', function () {
            return 'Subdomain 1 - Modul 1 Store';
        })->name('store');

        Route::get('/{id}', function (string $id) {
            return "Subdomain 1 - Modul 1 Show {$id}";
        })->name('show');

        Route::get('/{id}/edit', function (string $id) {
            return "Subdomain 1 - Modul 1 Edit {$id}";
        })->name('edit');

        Route::put('/{id}', function (string $id) {
            return "Subdomain 1 - Modul 1 Update {$id}";
        })->name('update');

        Route::delete('/{id}', function (string $id) {
            return "Subdomain 1 - Modul 1 Delete {$id}";
        })->name('destroy');
    });
});
