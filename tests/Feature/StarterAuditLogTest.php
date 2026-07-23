<?php

use App\Models\Starter\ActivityLog;
use App\Models\Starter\App;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use App\Services\Starter\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function auditActor(): ClientLogin
{
    $client = Client::query()->create([
        'name' => 'Audit Company',
        'account_status' => 'approved',
        'approved_at' => now(),
    ]);
    $role = ClientRole::query()->create([
        'code' => 'superuser',
        'name' => 'Superuser',
        'is_system' => true,
    ]);

    return ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => 'Audit Actor',
        'username' => 'audit-actor',
        'email' => 'audit@example.test',
        'password' => 'Secret12345',
        'status' => 'active',
    ]);
}

test('one universal table records create update and delete but not read', function () {
    $this->actingAs(auditActor());

    $app = App::query()->create(['name' => 'Audited App', 'subdomain' => 'audited']);
    $app->update(['name' => 'Updated App']);
    App::query()->findOrFail($app->id);
    $app->delete();

    expect(ActivityLog::query()->where('auditable_type', App::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);
    expect(ActivityLog::query()->where('event', 'read')->exists())->toBeFalse();
});

test('sensitive values are redacted from audit payloads', function () {
    $actor = auditActor();
    $this->actingAs($actor);

    $actor->update(['password' => 'AnotherSecret123']);

    $log = ActivityLog::query()
        ->where('auditable_type', ClientLogin::class)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($log->old_values['password'])->toBe('[REDACTED]')
        ->and($log->new_values['password'])->toBe('[REDACTED]')
        ->and(json_encode($log->new_values))->not->toContain('AnotherSecret123');
});

test('model reads do not create audit records', function () {
    $this->actingAs(auditActor());
    ActivityLog::query()->delete();

    Client::query()->firstOrFail();
    ClientRole::query()->get();

    expect(ActivityLog::query()->count())->toBe(0);
});

test('one business action groups changes to multiple tables', function () {
    $actor = auditActor();
    $this->actingAs($actor);

    app(AuditLogService::class)->withinAction('app.configure', 'Mengatur aplikasi Audit', function (): void {
        $app = App::query()->create(['name' => 'Grouped App', 'subdomain' => 'grouped']);
        $app->update(['name' => 'Grouped App Updated']);

        app(AuditLogService::class)->recordManual(
            'updated',
            App::class,
            $app->id,
            ['module_ids' => []],
            ['module_ids' => [10, 20]],
            tableName: 'pivot_client_roles_app_mods',
            auditableLabel: $app->name,
        );
    });

    $logs = ActivityLog::query()->orderBy('sequence')->get();

    expect($logs)->toHaveCount(3)
        ->and($logs->pluck('action_id')->unique())->toHaveCount(1)
        ->and($logs->pluck('action_key')->unique()->all())->toBe(['app.configure'])
        ->and($logs->pluck('sequence')->all())->toBe([1, 2, 3])
        ->and($logs->pluck('table_name')->all())->toBe([
            'starter_apps',
            'starter_apps',
            'pivot_client_roles_app_mods',
        ]);
});

test('rolled back database changes do not leave activity logs', function () {
    $this->actingAs(auditActor());

    try {
        app(AuditLogService::class)->withinAction('app.failed', 'Gagal membuat aplikasi', function (): void {
            DB::transaction(function (): void {
                App::query()->create(['name' => 'Rolled Back', 'subdomain' => 'rolled-back']);

                throw new RuntimeException('Rollback');
            });
        });
    } catch (RuntimeException) {
        //
    }

    expect(App::query()->where('subdomain', 'rolled-back')->exists())->toBeFalse()
        ->and(ActivityLog::query()->where('action_key', 'app.failed')->exists())->toBeFalse();
});
