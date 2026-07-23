<?php

use App\Livewire\Starter\Logs\ActivityLogIndex;
use App\Models\Starter\ActivityLog;
use App\Models\Starter\App;
use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Models\Starter\ClientRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * @return array{client: Client, superuser: ClientLogin, viewer: ClientLogin, denied: ClientLogin}
 */
function activityLogUsers(): array
{
    $client = Client::query()->create([
        'name' => 'Activity Company',
        'account_status' => 'approved',
        'approved_at' => now(),
    ]);
    $superRole = ClientRole::query()->create([
        'code' => 'superuser',
        'name' => 'Superuser',
        'is_system' => true,
    ]);
    $viewerRole = ClientRole::query()->create([
        'code' => 'auditor',
        'name' => 'Auditor',
        'can_view_logs' => true,
    ]);
    $deniedRole = ClientRole::query()->create([
        'code' => 'operator',
        'name' => 'Operator',
    ]);

    $user = fn (ClientRole $role, string $name, string $username): ClientLogin => ClientLogin::query()->create([
        'client_role_id' => $role->id,
        'name' => $name,
        'username' => $username,
        'email' => "{$username}@example.test",
        'password' => 'Secret12345',
        'status' => 'active',
    ]);

    return [
        'client' => $client,
        'superuser' => $user($superRole, 'Developer Secret', 'superuser'),
        'viewer' => $user($viewerRole, 'Audit Viewer', 'audit-viewer'),
        'denied' => $user($deniedRole, 'Regular Operator', 'regular-operator'),
    ];
}

/**
 * @param  array<string, mixed>  $overrides
 */
function createActivityLog(ClientLogin $actor, array $overrides = []): ActivityLog
{
    return ActivityLog::query()->create(array_merge([
        'client_login_id' => $actor->id,
        'actor_name' => $actor->name,
        'actor_username' => $actor->username,
        'actor_role' => $actor->role->name,
        'actor_is_superuser' => $actor->role->isSuperuser(),
        'action_id' => (string) Str::ulid(),
        'request_id' => (string) Str::ulid(),
        'sequence' => 1,
        'action_key' => 'student.update',
        'action_label' => 'Mengubah data mahasiswa',
        'event' => 'updated',
        'table_name' => 'students',
        'auditable_type' => App::class,
        'auditable_id' => '10',
        'auditable_label' => 'Mahasiswa A',
        'old_values' => ['name' => 'Nama Lama'],
        'new_values' => ['name' => 'Nama Baru'],
        'metadata' => null,
        'app_key' => 'app1',
        'route_name' => 'app1.module1.index',
        'request_method' => 'POST',
        'request_path' => '/module-1/data',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest Browser',
        'source' => 'web',
        'created_at' => now(),
    ], $overrides));
}

test('log activity route and static menu follow dedicated permission', function () {
    $users = activityLogUsers();

    $this->actingAs($users['superuser'])
        ->get(route('starter.logs.index'))
        ->assertOk()
        ->assertSee('Log Aktivitas')
        ->assertSee('Filter Log')
        ->assertSee('Riwayat Aktivitas');

    $this->actingAs($users['viewer'])
        ->get(route('starter.logs.index'))
        ->assertOk()
        ->assertSee('Log Aktivitas');

    $this->actingAs($users['denied'])
        ->get(route('starter.logs.index'))
        ->assertForbidden();

    $this->actingAs($users['viewer'])
        ->get(route('starter.profile.edit'))
        ->assertOk()
        ->assertSee('Log Aktivitas')
        ->assertDontSee('href="'.route('starter.settings').'"', false);
});

test('activity table groups one action and live filters update its result', function () {
    $users = activityLogUsers();
    $actionId = (string) Str::ulid();

    createActivityLog($users['viewer'], [
        'action_id' => $actionId,
        'sequence' => 1,
        'event' => 'updated',
        'table_name' => 'students',
        'action_label' => 'Mengubah mahasiswa Budi',
        'auditable_label' => 'Budi',
    ]);
    createActivityLog($users['viewer'], [
        'action_id' => $actionId,
        'sequence' => 2,
        'event' => 'created',
        'table_name' => 'student_histories',
        'action_label' => 'Mengubah mahasiswa Budi',
        'auditable_label' => 'Riwayat Budi',
    ]);
    createActivityLog($users['viewer'], [
        'action_label' => 'Menghapus mahasiswa Siti',
        'event' => 'deleted',
        'table_name' => 'students',
        'auditable_label' => 'Siti',
    ]);

    $this->actingAs($users['viewer']);

    Livewire::test(ActivityLogIndex::class)
        ->assertSee('Mengubah mahasiswa Budi')
        ->assertSee('2 perubahan')
        ->assertSee('2 tabel')
        ->assertSee('Detail')
        ->assertDontSee('Action ID')
        ->set('search', 'Siti')
        ->assertSee('Menghapus mahasiswa Siti')
        ->assertDontSee('Mengubah mahasiswa Budi')
        ->set('search', '')
        ->set('eventFilter', 'created')
        ->assertSee('Mengubah mahasiswa Budi')
        ->assertDontSee('Menghapus mahasiswa Siti')
        ->set('eventFilter', '')
        ->call('showActionDetail', $actionId)
        ->assertSet('detailModalOpen', true)
        ->assertSee('Detail Aktivitas')
        ->assertSee('ID Referensi Audit')
        ->assertSee('Tampilkan referensi teknis')
        ->assertSee('ID Permintaan')
        ->assertSee('students')
        ->assertSee('student_histories')
        ->assertSee('Nama Lama')
        ->assertSee('Nama Baru');
});

test('superuser identity and system account changes are protected from delegated viewers', function () {
    $users = activityLogUsers();

    createActivityLog($users['superuser'], [
        'action_label' => 'Mengubah data perusahaan',
        'auditable_type' => Client::class,
        'auditable_id' => (string) $users['client']->id,
    ]);
    createActivityLog($users['superuser'], [
        'action_label' => 'Mengubah akun rahasia',
        'auditable_type' => ClientLogin::class,
        'auditable_id' => (string) $users['superuser']->id,
    ]);
    createActivityLog($users['viewer'], [
        'action_label' => 'Aktivitas auditor',
    ]);

    $this->actingAs($users['viewer']);

    Livewire::test(ActivityLogIndex::class)
        ->assertSee('Mengubah data perusahaan')
        ->assertSee('Sistem')
        ->assertSee('Aktivitas auditor')
        ->assertDontSee('Developer Secret')
        ->assertDontSee('Mengubah akun rahasia');
});
