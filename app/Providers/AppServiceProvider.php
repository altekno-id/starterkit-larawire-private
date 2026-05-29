<?php

namespace App\Providers;

use App\Contracts\Starter\AppInterface;
use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\AppRouteInterface;
use App\Contracts\Starter\UserInterface;
use App\Contracts\Starter\UserLoginInterface;
use App\Contracts\Starter\UserRoleInterface;
use App\Http\Middleware\StarterAuthorize;
use App\Repositories\Starter\AppRepository;
use App\Repositories\Starter\AppModRepository;
use App\Repositories\Starter\AppRouteRepository;
use App\Repositories\Starter\UserRepository;
use App\Repositories\Starter\UserLoginRepository;
use App\Repositories\Starter\UserRoleRepository;
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
        $this->app->bind(AppInterface::class, AppRepository::class);
        $this->app->bind(AppModInterface::class, AppModRepository::class);
        $this->app->bind(AppRouteInterface::class, AppRouteRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(UserLoginInterface::class, UserLoginRepository::class);
        $this->app->bind(UserRoleInterface::class, UserRoleRepository::class);
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
            'starter.*',
        ], function ($view): void {
            $view->with(app(StarterContextService::class)->data());
        });

        Livewire::addPersistentMiddleware([
            StarterAuthorize::class,
        ]);
    }
}
