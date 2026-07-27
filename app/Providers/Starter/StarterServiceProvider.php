<?php

namespace App\Providers\Starter;

use App\Contracts\Starter\ActivityLogInterface;
use App\Contracts\Starter\AppInterface;
use App\Contracts\Starter\AppModInterface;
use App\Contracts\Starter\AppRouteInterface;
use App\Contracts\Starter\ClientInterface;
use App\Contracts\Starter\ClientLoginInterface;
use App\Contracts\Starter\ClientRoleInterface;
use App\Contracts\Starter\StarterConfigInterface;
use App\Http\Middleware\Starter\StarterAuthorize;
use App\Http\Middleware\Starter\StarterEnsureActiveUser;
use App\Http\Middleware\Starter\StarterForcePasswordChange;
use App\Http\Middleware\Starter\StarterLockScreen;
use App\Repositories\Starter\ActivityLogRepository;
use App\Repositories\Starter\AppModRepository;
use App\Repositories\Starter\AppRepository;
use App\Repositories\Starter\AppRouteRepository;
use App\Repositories\Starter\ClientLoginRepository;
use App\Repositories\Starter\ClientRepository;
use App\Repositories\Starter\ClientRoleRepository;
use App\Repositories\Starter\StarterConfigRepository;
use App\Services\Starter\AuditLogService;
use App\Services\Starter\AuthenticatedLoginService;
use App\Services\Starter\SecuritySettingsService;
use App\Services\Starter\SettingsOverviewService;
use App\Services\Starter\StarterConfigService;
use App\Services\Starter\StarterContextService;
use App\Services\Starter\UserManagementRoleService;
use App\Services\Starter\UserManagementUserService;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class StarterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerViewPaths();

        $this->app->bind(AppInterface::class, AppRepository::class);
        $this->app->bind(AppModInterface::class, AppModRepository::class);
        $this->app->bind(AppRouteInterface::class, AppRouteRepository::class);
        $this->app->scoped(ActivityLogInterface::class, ActivityLogRepository::class);
        $this->app->bind(ClientInterface::class, ClientRepository::class);
        $this->app->bind(ClientLoginInterface::class, ClientLoginRepository::class);
        $this->app->bind(ClientRoleInterface::class, ClientRoleRepository::class);
        $this->app->bind(StarterConfigInterface::class, StarterConfigRepository::class);
        $this->app->scoped(AuditLogService::class);
        $this->app->scoped(AuthenticatedLoginService::class);
        $this->app->scoped(SecuritySettingsService::class);
        $this->app->scoped(SettingsOverviewService::class);
        $this->app->scoped(StarterConfigService::class);
        $this->app->scoped(StarterContextService::class);
        $this->app->scoped(UserManagementRoleService::class);
        $this->app->scoped(UserManagementUserService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/starter'));
        $this->loadAppMigrations();
        View::addNamespace('errors', resource_path('views/starter/errors'));

        Number::useLocale(str_replace('_', '-', (string) config('app.locale')));
        $strictDevelopment = $this->app->isLocal() || $this->app->runningUnitTests();
        Model::preventLazyLoading($strictDevelopment);
        Model::preventSilentlyDiscardingAttributes($strictDevelopment);

        Event::listen([
            'eloquent.created: *',
            'eloquent.updated: *',
            'eloquent.deleted: *',
        ], function (string $eventName, array $models): void {
            $event = str($eventName)->after('eloquent.')->before(':')->value();
            $model = $models[0] ?? null;

            if ($model instanceof Model) {
                app(AuditLogService::class)->recordModelEvent($event, $model);
            }
        });

        View::composer([
            'layouts::app',
            'starter.templates.layouts.app',
            'apps.*',
        ], function ($view): void {
            $view->with(app(StarterContextService::class)->data());
        });

        Livewire::addPersistentMiddleware([
            StarterEnsureActiveUser::class,
            StarterForcePasswordChange::class,
            StarterLockScreen::class,
            StarterAuthorize::class,
            RequirePassword::class,
        ]);
    }

    private function registerViewPaths(): void
    {
        $starterViewPath = resource_path('views/starter');
        $viewPaths = (array) $this->app['config']->get('view.paths', [resource_path('views')]);

        if (! in_array($starterViewPath, $viewPaths, true)) {
            array_unshift($viewPaths, $starterViewPath);
            $this->app['config']->set('view.paths', $viewPaths);
        }
    }

    private function loadAppMigrations(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $appsMigrationPath = database_path('migrations/apps');

        if (! File::isDirectory($appsMigrationPath)) {
            return;
        }

        $paths = collect(File::directories($appsMigrationPath))
            ->filter(fn (string $path): bool => preg_match(
                '/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/',
                basename($path),
            ) === 1)
            ->sort()
            ->values()
            ->all();

        if ($paths !== []) {
            $this->loadMigrationsFrom($paths);
        }
    }
}
