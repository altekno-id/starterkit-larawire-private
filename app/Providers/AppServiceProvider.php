<?php

namespace App\Providers;

use App\Contracts\Starter\AppInterface;
use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\AppRouteInterface;
use App\Contracts\Starter\ClientInterface;
use App\Contracts\Starter\ClientLoginInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Http\Middleware\StarterAuthorize;
use App\Repositories\Starter\AppRepository;
use App\Repositories\Starter\AppModRepository;
use App\Repositories\Starter\AppRouteRepository;
use App\Repositories\Starter\ClientRepository;
use App\Repositories\Starter\ClientLoginRepository;
use App\Repositories\Starter\ClientRoleRepository;
use App\Services\Starter\StarterContextService;
use App\Support\Starter\StarterNavigation;
use Illuminate\Auth\Notifications\ResetPassword;
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
        $this->app->bind(ClientInterface::class, ClientRepository::class);
        $this->app->bind(ClientLoginInterface::class, ClientLoginRepository::class);
        $this->app->bind(ClientRoleInterface::class, ClientRoleRepository::class);
        $this->app->scoped(StarterContextService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $query = http_build_query(['email' => $notifiable->getEmailForPasswordReset()]);

            return StarterNavigation::authUrl("reset-password/{$token}")."?{$query}";
        });

        View::composer([
            'layouts::app',
            'templates.layouts.app',
            'apps.*',
            'starter.profile.*',
            'starter.settings.*',
            'starter.user-management.*',
        ], function ($view): void {
            $view->with(app(StarterContextService::class)->data());
        });

        Livewire::addPersistentMiddleware([
            StarterAuthorize::class,
        ]);
    }
}
