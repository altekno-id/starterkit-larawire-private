<?php

namespace Altekno\StarterKit\Providers\Starter;

use Altekno\StarterKit\Console\Commands\Starter\AdminCommand;
use Altekno\StarterKit\Console\Commands\Starter\InstallCommand;
use Altekno\StarterKit\Console\Commands\Starter\MakeAppCommand;
use Altekno\StarterKit\Console\Commands\Starter\PublishAssetsCommand;
use Altekno\StarterKit\Console\Commands\Starter\SecurityCheckCommand;
use Altekno\StarterKit\Console\Commands\Starter\SetupCommand;
use Altekno\StarterKit\Console\Commands\Starter\SyncCommand;
use Altekno\StarterKit\Contracts\Starter\ActivityLogInterface;
use Altekno\StarterKit\Contracts\Starter\AppInterface;
use Altekno\StarterKit\Contracts\Starter\AppModInterface;
use Altekno\StarterKit\Contracts\Starter\AppRouteInterface;
use Altekno\StarterKit\Contracts\Starter\ClientInterface;
use Altekno\StarterKit\Contracts\Starter\ClientLoginInterface;
use Altekno\StarterKit\Contracts\Starter\ClientRoleInterface;
use Altekno\StarterKit\Contracts\Starter\StarterConfigInterface;
use Altekno\StarterKit\Http\Middleware\Starter\StarterAuthorize;
use Altekno\StarterKit\Http\Middleware\Starter\StarterEnsureActiveUser;
use Altekno\StarterKit\Http\Middleware\Starter\StarterForcePasswordChange;
use Altekno\StarterKit\Http\Middleware\Starter\StarterLockScreen;
use Altekno\StarterKit\Livewire\Starter\Auth\ConfirmPassword;
use Altekno\StarterKit\Livewire\Starter\Auth\LockScreen as LockScreenComponent;
use Altekno\StarterKit\Livewire\Starter\Auth\Login;
use Altekno\StarterKit\Livewire\Starter\Logs\ActivityLogIndex;
use Altekno\StarterKit\Livewire\Starter\Profile\EditMyProfile;
use Altekno\StarterKit\Livewire\Starter\Settings\ClientProfile;
use Altekno\StarterKit\Livewire\Starter\Settings\SecuritySettings;
use Altekno\StarterKit\Livewire\Starter\Settings\SettingsIndex;
use Altekno\StarterKit\Livewire\Starter\UserManagement\RoleForm;
use Altekno\StarterKit\Livewire\Starter\UserManagement\Roles;
use Altekno\StarterKit\Livewire\Starter\UserManagement\UserForm;
use Altekno\StarterKit\Livewire\Starter\UserManagement\Users;
use Altekno\StarterKit\Repositories\Starter\ActivityLogRepository;
use Altekno\StarterKit\Repositories\Starter\AppModRepository;
use Altekno\StarterKit\Repositories\Starter\AppRepository;
use Altekno\StarterKit\Repositories\Starter\AppRouteRepository;
use Altekno\StarterKit\Repositories\Starter\ClientLoginRepository;
use Altekno\StarterKit\Repositories\Starter\ClientRepository;
use Altekno\StarterKit\Repositories\Starter\ClientRoleRepository;
use Altekno\StarterKit\Repositories\Starter\StarterConfigRepository;
use Altekno\StarterKit\Services\Starter\AuditLogService;
use Altekno\StarterKit\Services\Starter\AuthenticatedLoginService;
use Altekno\StarterKit\Services\Starter\SecuritySettingsService;
use Altekno\StarterKit\Services\Starter\SettingsOverviewService;
use Altekno\StarterKit\Services\Starter\StarterConfigService;
use Altekno\StarterKit\Services\Starter\StarterContextService;
use Altekno\StarterKit\Services\Starter\UserManagementRoleService;
use Altekno\StarterKit\Services\Starter\UserManagementUserService;
use Altekno\StarterKit\Support\Starter\StarterPaths;
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
        $this->mergeConfigFrom(StarterPaths::path('config/starter.php'), 'starter');
        $this->configureEmbeddedApplication();
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

        if ($this->app->runningInConsole()) {
            $this->commands([
                AdminCommand::class,
                InstallCommand::class,
                MakeAppCommand::class,
                PublishAssetsCommand::class,
                SecurityCheckCommand::class,
                SetupCommand::class,
                SyncCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(StarterPaths::path('database/migrations/starter'));
        $this->loadAppMigrations();
        $this->registerLivewireComponents();
        View::addNamespace('errors', StarterPaths::path('resources/views/starter/errors'));

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

    private function registerLivewireComponents(): void
    {
        $components = [
            'starter.auth.confirm-password' => ConfirmPassword::class,
            'starter.auth.lock-screen' => LockScreenComponent::class,
            'starter.auth.login' => Login::class,
            'starter.logs.activity-log-index' => ActivityLogIndex::class,
            'starter.profile.edit-my-profile' => EditMyProfile::class,
            'starter.settings.client-profile' => ClientProfile::class,
            'starter.settings.security-settings' => SecuritySettings::class,
            'starter.settings.settings-index' => SettingsIndex::class,
            'starter.user-management.role-form' => RoleForm::class,
            'starter.user-management.roles' => Roles::class,
            'starter.user-management.user-form' => UserForm::class,
            'starter.user-management.users' => Users::class,
        ];

        foreach ($components as $name => $component) {
            Livewire::component($name, $component);
        }
    }

    private function registerViewPaths(): void
    {
        $viewPaths = (array) $this->app['config']->get('view.paths', [resource_path('views')]);
        $starterViewPaths = [
            StarterPaths::path('resources/views/starter'),
            StarterPaths::path('resources/views'),
        ];

        $this->app['config']->set('view.paths', array_values(array_unique([
            ...$viewPaths,
            ...$starterViewPaths,
        ])));
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

    private function configureEmbeddedApplication(): void
    {
        if (! StarterPaths::isEmbedded()) {
            return;
        }

        $config = $this->app['config'];
        $starterViews = StarterPaths::path('resources/views/starter');

        $config->set('app.domain', $config->get('starter.domain'));

        if ($config->get('starter.connector.configure_auth', true)) {
            $config->set('auth', require StarterPaths::path('config/auth.php'));
        }

        if ($config->get('starter.connector.configure_shared_session', true)) {
            $domain = trim((string) $config->get('starter.domain'), '.');
            $config->set('session.domain', in_array($domain, ['', 'localhost', '127.0.0.1'], true)
                ? null
                : ".{$domain}");
        }

        $componentLocations = (array) $config->get('livewire.component_locations', []);
        array_unshift($componentLocations, "{$starterViews}/templates/components");

        $config->set('livewire.component_locations', array_values(array_unique($componentLocations)));
        $config->set('livewire.component_namespaces.layouts', "{$starterViews}/templates/layouts");
        $config->set('livewire.component_layout', 'layouts::app');
        $config->set('livewire.temporary_file_upload.rules', ['required', 'file', 'max:10240']);
    }
}
