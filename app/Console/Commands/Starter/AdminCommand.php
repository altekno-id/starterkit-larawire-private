<?php

namespace App\Console\Commands\Starter;

use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
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
    protected $description = 'Create development clients, admin roles, and admin logins';

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

        if (Client::query()->count() === 0) {
            $this->createDefaultClients();
        }

        foreach ($this->clients() as $client) {
            $this->createAdmin($client);
            $this->info("Ensured admin login for client: {$client->name}");
        }

        return self::SUCCESS;
    }

    /**
     * Get all clients that should receive an admin login.
     *
     * @return Collection<int, Client>
     */
    private function clients(): Collection
    {
        return Client::query()->orderBy('id')->get();
    }

    /**
     * Create default development clients on fresh projects.
     */
    private function createDefaultClients(): void
    {
        foreach ($this->defaultClients() as $client) {
            Client::query()->create([
                'name' => $client['name'],
                'email' => $client['email'],
                'pic_name' => $client['pic_name'],
                'account_status' => 'approved',
                'approved_at' => now(),
                'subscription_status' => 'active',
                'subscribed_at' => now(),
            ]);
        }
    }

    /**
     * @return array<int, array{name: string, email: string, pic_name: string}>
     */
    private function defaultClients(): array
    {
        return [
            [
                'name' => 'Client 1',
                'email' => 'admin1@email.com',
                'pic_name' => 'Client 1 Admin',
            ],
            [
                'name' => 'Client 2',
                'email' => 'admin2@email.com',
                'pic_name' => 'Client 2 Admin',
            ],
        ];
    }

    /**
     * Create or update one admin login for one client.
     */
    private function createAdmin(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            $role = ClientRole::query()->updateOrCreate([
                'client_id' => $client->id,
                'code' => 'admin',
            ], [
                'name' => 'Admin',
                'desc' => 'Development admin with full access.',
            ]);

            // Empty module listing is intentionally treated as full access for admin roles.
            DB::table('rel_client_roles_app_mods')->where('client_role_id', $role->id)->delete();
            $this->syncAdminLandings($role);

            $login = ClientLogin::query()->firstOrNew([
                'email' => $this->adminEmail($client),
            ]);

            $login->fill([
                'client_id' => $client->id,
                'client_role_id' => $role->id,
                'name' => $login->name ?: 'Admin',
                'email_verified_at' => $login->email_verified_at ?: now(),
                'last_login_provider' => $login->last_login_provider ?: 'email',
            ]);

            if (! $login->exists || app()->environment(['local', 'testing'])) {
                $login->password = Hash::make($this->adminPassword());
            }

            $login->save();
        });
    }

    private function syncAdminLandings(ClientRole $role): void
    {
        $dashboardMenus = DB::table('app_menus')
            ->join('app_routes', 'app_menus.app_route_id', '=', 'app_routes.id')
            ->join('app_mods', 'app_menus.app_mod_id', '=', 'app_mods.id')
            ->join('apps', 'app_mods.app_id', '=', 'apps.id')
            ->where('app_menus.is_landing_candidate', true)
            ->select([
                'apps.id as app_id',
                'apps.subdomain',
                'app_routes.name',
                'app_menus.id as app_menu_id',
            ])
            ->get()
            ->filter(fn ($menu): bool => $menu->name === $menu->subdomain.'.dashboard');

        $now = now();

        foreach ($dashboardMenus as $menu) {
            DB::table('rel_client_roles_app_landings')->updateOrInsert([
                'client_role_id' => $role->id,
                'app_id' => $menu->app_id,
            ], [
                'app_menu_id' => $menu->app_menu_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Build a unique admin email for one client.
     */
    private function adminEmail(Client $client): string
    {
        return "admin{$client->id}@email.com";
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
