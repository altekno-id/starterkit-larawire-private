<?php

namespace App\Console\Commands\Starter;

use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Rules\StarterPasswordRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SetupCommand extends Command
{
    protected $signature = 'starter:setup
        {--company= : Internal company/client name}
        {--email= : Superuser notification email}
        {--username= : Superuser username}
        {--reset-password : Replace the existing Superuser password}';

    protected $description = 'Set up the private client and built-in Superuser account';

    public function handle(): int
    {
        $username = str($this->option('username') ?: config('starter.superuser.username'))->lower()->trim()->toString();
        $email = str($this->option('email') ?: config('starter.superuser.email'))->lower()->trim()->toString();
        $companyName = trim((string) ($this->option('company') ?: config('app.name')));
        $existingLogin = ClientLogin::query()->where('username', $username)->first();
        $password = null;

        if (! $existingLogin || $this->option('reset-password')) {
            $password = $this->password();
        }

        try {
            Validator::make([
                'username' => $username,
                'email' => $email,
            ], [
                'username' => ['required', 'alpha_dash:ascii', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
            ])->validate();
        } catch (ValidationException $exception) {
            $this->error(collect($exception->errors())->flatten()->implode(' '));

            return self::FAILURE;
        }

        DB::transaction(function () use ($companyName, $email, $username, $password, $existingLogin): void {
            $client = Client::query()->firstOrCreate([], [
                'name' => $companyName,
                'email' => $email,
                'pic_name' => 'Developer',
                'account_status' => 'approved',
                'approved_at' => now(),
            ]);

            if ($client->account_status !== 'approved') {
                $client->forceFill(['account_status' => 'approved', 'approved_at' => now()])->save();
            }

            $role = ClientRole::query()->updateOrCreate([
                'code' => 'superuser',
            ], [
                'name' => 'Superuser',
                'desc' => 'Role bawaan developer dengan akses penuh ke seluruh module.',
                'is_system' => true,
            ]);

            $role->mods()->detach();

            $login = $existingLogin ?? new ClientLogin;
            $login->forceFill([
                'client_role_id' => $role->id,
                'name' => $login->name ?: 'Superuser',
                'username' => $username,
                'email' => $email,
                'email_verified_at' => $login->email_verified_at ?: now(),
                'status' => 'active',
                'must_change_password' => false,
            ]);

            if ($password !== null) {
                $login->password = $password;
                $login->password_changed_at = now();
            }

            $login->save();
        });

        if ($this->call('starter:sync', ['--force' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->info("Private starter ready. Login username: {$username}");

        return self::SUCCESS;
    }

    private function password(): string
    {
        $password = (string) config('starter.superuser.password');

        if ($password === '' && app()->environment(['local', 'testing'])) {
            $password = 'rahasia123';
        }

        if ($password === '' && $this->input->isInteractive()) {
            $password = (string) $this->secret('Superuser password');
        }

        if ($password === '') {
            $this->fail('STARTER_SUPERUSER_PASSWORD is required when creating or resetting Superuser outside local/testing.');
        }

        if ($password === 'rahasia123') {
            if (! app()->environment(['local', 'testing'])) {
                $this->fail('The development Superuser password cannot be used outside local/testing.');
            }

            $this->warn('Using the local-only default password. Configure STARTER_SUPERUSER_PASSWORD before deployment.');

            return $password;
        }

        try {
            Validator::make(['password' => $password], ['password' => StarterPasswordRules::rules()])->validate();
        } catch (ValidationException $exception) {
            $this->fail(collect($exception->errors())->flatten()->implode(' '));
        }

        return $password;
    }
}
