<?php

namespace App\Console\Commands\Starter;

use App\Models\Starter\App;
use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Models\Starter\UserRole;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter:admin {subdomain? : Create admin only for one registered subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create development admin client, role, and login for every starter app';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $syncArguments = [
            '--force' => true,
        ];

        if ($this->argument('subdomain')) {
            $syncArguments['subdomain'] = $this->argument('subdomain');
        }

        $syncStatus = $this->call('starter:sync', $syncArguments);

        if ($syncStatus !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (User::query()->count() === 0) {
            $this->createDefaultClients();
        }

        foreach ($this->clients() as $client) {
            $this->createAdmin($client);
            $this->info("Ensured admin login for client: {$client->name}");
        }

        return self::SUCCESS;
    }

    /**
     * Get the subdomains that should receive a default client on fresh projects.
     *
     * @return array<int, string>
     */
    private function subdomains(): array
    {
        $subdomain = $this->argument('subdomain');

        return $subdomain ? [$subdomain] : $this->discoverSubdomains();
    }

    /**
     * Discover app configs from config/apps/*.php.
     *
     * @return array<int, string>
     */
    private function discoverSubdomains(): array
    {
        return collect(glob(config_path('apps/*.php')) ?: [])
            ->map(function (string $path): string {
                return pathinfo($path, PATHINFO_FILENAME);
            })
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Get all clients that should receive an admin login.
     *
     * @return Collection<int, User>
     */
    private function clients(): Collection
    {
        return User::query()->orderBy('id')->get();
    }

    /**
     * Create one default client for each registered app on fresh projects.
     */
    private function createDefaultClients(): void
    {
        foreach ($this->subdomains() as $subdomain) {
            $app = App::query()->where('subdomain', $subdomain)->first();

            User::query()->create([
                'name' => ($app?->name ?? str($subdomain)->headline()).' Development',
                'email' => "client@{$subdomain}.".config('app.domain'),
                'pic_name' => 'Admin',
                'account_status' => 'approved',
                'approved_at' => now(),
                'subscription_status' => 'active',
                'subscribed_at' => now(),
            ]);
        }
    }

    /**
     * Create or update one admin login for one client.
     */
    private function createAdmin(User $client): void
    {
        DB::transaction(function () use ($client): void {
            $role = UserRole::query()->updateOrCreate([
                'user_id' => $client->id,
                'code' => 'admin',
            ], [
                'name' => 'Admin',
                'desc' => 'Development admin with full access.',
            ]);

            // Empty module listing is intentionally treated as full access for admin roles.
            DB::table('rel_user_roles_app_mods')->where('user_role_id', $role->id)->delete();

            $login = UserLogin::query()->firstOrNew([
                'user_id' => $client->id,
                'username' => 'admin',
            ]);

            $login->fill([
                'user_role_id' => $role->id,
                'name' => $login->name ?: 'Admin',
                'email' => $login->email ?: $this->adminEmail($client),
                'email_verified_at' => $login->email_verified_at ?: now(),
                'last_login_provider' => $login->last_login_provider ?: 'username',
            ]);

            if (! $login->exists || app()->environment(['local', 'testing'])) {
                $login->password = Hash::make($this->adminPassword());
            }

            $login->save();
        });
    }

    /**
     * Build a unique admin email for one client.
     */
    private function adminEmail(User $client): string
    {
        return "admin+client{$client->id}@".config('app.domain');
    }

    /**
     * Get the admin password for local/testing resets or fresh production creation.
     */
    private function adminPassword(): string
    {
        if (app()->environment(['local', 'testing'])) {
            return env('STARTER_ADMIN_PASSWORD', 'admin');
        }

        $password = env('STARTER_ADMIN_PASSWORD');

        if (! $password) {
            $this->fail('STARTER_ADMIN_PASSWORD must be configured outside local/testing when creating missing admin logins.');
        }

        return $password;
    }
}
