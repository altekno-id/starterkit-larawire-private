<?php

namespace App\Livewire\Starter\Settings;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\AuditLogService;
use App\Services\Starter\StarterConfigService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SecuritySettings extends Component
{
    /**
     * @var array{
     *     remember_me_enabled: bool,
     *     lock_screen_enabled: bool,
     *     lock_screen_timeout_minutes: int,
     *     login_max_attempts: int,
     *     login_decay_seconds: int,
     *     max_image_size_kb: int
     * }
     */
    public array $securityForm = [
        'remember_me_enabled' => true,
        'lock_screen_enabled' => true,
        'lock_screen_timeout_minutes' => 15,
        'login_max_attempts' => 5,
        'login_decay_seconds' => 60,
        'max_image_size_kb' => 2048,
    ];

    public function mount(StarterConfigService $configs): void
    {
        $this->authorizeSettings();
        $this->securityForm = [
            'remember_me_enabled' => $configs->boolean('security.remember_me_enabled'),
            'lock_screen_enabled' => $configs->boolean('security.lock_screen_enabled'),
            'lock_screen_timeout_minutes' => $configs->integer('security.lock_screen_timeout_minutes'),
            'login_max_attempts' => $configs->integer('security.login_max_attempts'),
            'login_decay_seconds' => $configs->integer('security.login_decay_seconds'),
            'max_image_size_kb' => $configs->integer('uploads.max_image_size_kb'),
        ];
    }

    public function save(StarterConfigService $configs, AuditLogService $auditLogs): void
    {
        $this->authorizeSettings();

        $validated = $this->validate([
            'securityForm.remember_me_enabled' => ['boolean'],
            'securityForm.lock_screen_enabled' => ['boolean'],
            'securityForm.lock_screen_timeout_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'securityForm.login_max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'securityForm.login_decay_seconds' => ['required', 'integer', 'min:30', 'max:3600'],
            'securityForm.max_image_size_kb' => ['required', 'integer', 'min:256', 'max:10240'],
        ], [], [
            'securityForm.remember_me_enabled' => 'remember me',
            'securityForm.lock_screen_enabled' => 'lock screen',
            'securityForm.lock_screen_timeout_minutes' => 'waktu lock screen',
            'securityForm.login_max_attempts' => 'batas percobaan login',
            'securityForm.login_decay_seconds' => 'durasi pembatasan login',
            'securityForm.max_image_size_kb' => 'ukuran maksimum gambar',
        ])['securityForm'];

        $auditLogs->withinAction('config.security.update', 'Mengubah konfigurasi keamanan', function () use ($configs, $validated): void {
            DB::transaction(function () use ($configs, $validated): void {
                $configs->update([
                    'security.remember_me_enabled' => (bool) $validated['remember_me_enabled'],
                    'security.lock_screen_enabled' => (bool) $validated['lock_screen_enabled'],
                    'security.lock_screen_timeout_minutes' => (int) $validated['lock_screen_timeout_minutes'],
                    'security.login_max_attempts' => (int) $validated['login_max_attempts'],
                    'security.login_decay_seconds' => (int) $validated['login_decay_seconds'],
                    'uploads.max_image_size_kb' => (int) $validated['max_image_size_kb'],
                ]);

                if (! $validated['remember_me_enabled']) {
                    ClientLogin::query()
                        ->whereNotNull('remember_token')
                        ->eachById(fn (ClientLogin $login) => $login->update(['remember_token' => null]));
                }
            });
        });

        $this->dispatch('starter-toast', type: 'success', message: 'Konfigurasi keamanan berhasil disimpan.');
    }

    public function render()
    {
        $this->authorizeSettings();

        return view('starter.settings.security-settings');
    }

    private function authorizeSettings(): void
    {
        $login = auth()->user();

        abort_unless(
            $login instanceof ClientLogin
                && ($login->loadMissing('role')->role?->canManageSettings() ?? false),
            403,
        );
    }
}
