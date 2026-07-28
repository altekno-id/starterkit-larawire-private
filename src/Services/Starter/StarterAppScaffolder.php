<?php

namespace Altekno\StarterKit\Services\Starter;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class StarterAppScaffolder
{
    public function __construct(private readonly Filesystem $files) {}

    /**
     * @return list<string>
     */
    public function create(string $subdomain, string $name, ?string $description = null, string $icon = 'apps'): array
    {
        $className = Str::studly($subdomain);
        $files = $this->files($subdomain, $className, $name, $description, $icon);
        $collisions = array_keys(array_filter($files, fn (string $contents, string $path): bool => $this->files->exists($path), ARRAY_FILTER_USE_BOTH));

        if ($collisions !== []) {
            throw new RuntimeException('Scaffold target already exists: '.implode(', ', $collisions));
        }

        $created = [];

        try {
            foreach ($files as $path => $contents) {
                $this->files->ensureDirectoryExists(dirname($path));
                $this->files->put($path, $contents);
                $created[] = $path;
            }
        } catch (Throwable $exception) {
            $this->files->delete($created);

            throw $exception;
        }

        return $created;
    }

    /**
     * @return array<string, string>
     */
    private function files(string $subdomain, string $className, string $name, ?string $description, string $icon): array
    {
        $description ??= "Aplikasi internal {$name}.";
        $viewName = "apps.{$subdomain}.dashboard.{$subdomain}-dashboard-index";
        $config = var_export([
            'name' => $name,
            'desc' => $description,
            'icon' => $icon,
            'mods' => [
                'dashboard' => [
                    'name' => 'Dashboard',
                    'desc' => 'Ringkasan aplikasi.',
                    'menus' => [[
                        'label' => 'Dashboard',
                        'icon' => 'layout-dashboard',
                        'children' => [
                            [
                                'label' => 'Submenu 1',
                                'route' => "{$subdomain}.dashboard",
                                'landing' => true,
                            ],
                            [
                                'label' => 'Submenu 2',
                                'route' => "{$subdomain}.dashboard.submenu-two",
                            ],
                        ],
                    ]],
                ],
            ],
        ], true);

        return [
            config_path("apps/{$subdomain}.php") => "<?php\n\nreturn {$config};\n",
            base_path("routes/apps/{$subdomain}.php") => $this->routeContents($subdomain, $className),
            app_path("Livewire/Apps/{$className}/Dashboard/{$className}DashboardIndex.php") => <<<PHP
<?php

namespace App\Livewire\Apps\\{$className}\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class {$className}DashboardIndex extends Component
{
    public function render()
    {
        \$section = request()->routeIs('{$subdomain}.dashboard.submenu-two')
            ? 'Submenu 2'
            : 'Submenu 1';

        return view('{$viewName}', compact('section'))
            ->title('{$name} - '.\$section);
    }
}
PHP,
            resource_path("views/apps/{$subdomain}/dashboard/{$subdomain}-dashboard-index.blade.php") => <<<BLADE
<div>
    <div class="page-header">
        <div class="page-pretitle">{$name}</div>
        <h2 class="page-title">{{ \$section }}</h2>
    </div>
    <div class="card mt-3"><div class="card-body">{$description}</div></div>
</div>
BLADE,
            base_path("tests/Feature/Apps/{$className}/{$className}DashboardTest.php") => <<<PHP
<?php

use Illuminate\Support\Facades\Route;

it('registers the {$subdomain} dashboard route', function () {
    expect(Route::has('{$subdomain}.dashboard'))->toBeTrue()
        ->and(Route::has('{$subdomain}.dashboard.submenu-two'))->toBeTrue();
});
PHP,
        ];
    }

    private function routeContents(string $subdomain, string $className): string
    {
        return <<<PHP
<?php

use App\Livewire\Apps\\{$className}\Dashboard\\{$className}DashboardIndex;
use Altekno\StarterKit\Models\Starter\ClientLogin;
use Altekno\StarterKit\Services\Starter\NavigationAuthorizedRedirectService;
use Altekno\StarterKit\Support\Starter\StarterNavigation;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    \$redirect = request()->query('redirect', url('/'));

    return redirect(StarterNavigation::authLoginUrl(StarterNavigation::isSafeRedirect(\$redirect) ? \$redirect : url('/')));
});

Route::name('{$subdomain}.')->group(function () {
    Route::middleware(['auth:web', 'starter.active', 'starter.password-change', 'starter.lock'])->group(function () {
        Route::get('/', function (NavigationAuthorizedRedirectService \$redirects) {
            \$login = auth()->user();

            return \$login instanceof ClientLogin
                ? redirect(\$redirects->forAppAnchor(\$login, '{$subdomain}'))
                : redirect()->route('auth.login');
        })->name('anchor');

        Route::middleware('starter.authorize')->group(function () {
            Route::livewire('/dashboard/index', {$className}DashboardIndex::class)->name('dashboard');
            Route::livewire('/dashboard/submenu-2', {$className}DashboardIndex::class)->name('dashboard.submenu-two');
        });
    });
});
PHP;
    }
}
