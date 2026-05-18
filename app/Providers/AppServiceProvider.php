<?php

namespace App\Providers;

use App\Contracts\Starter\UserLoginInterface;
use App\Http\Middleware\StarterAuthorize;
use App\Repositories\Starter\UserLoginRepository;
use App\Services\Starter\StarterContextService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserLoginInterface::class, UserLoginRepository::class);
        $this->app->scoped(StarterContextService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        View::composer([
            'layouts::app',
            'templates.layouts.app',
            'apps.*',
        ], function ($view): void {
            $view->with(app(StarterContextService::class)->data());
        });

        Livewire::addPersistentMiddleware([
            StarterAuthorize::class,
        ]);
    }
}
