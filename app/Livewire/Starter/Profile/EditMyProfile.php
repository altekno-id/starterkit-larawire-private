<?php

namespace App\Livewire\Starter\Profile;

use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Services\Starter\Profile\ProfileService;
use App\Services\Starter\StarterContextService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class EditMyProfile extends Component
{
    /**
     * @var array{name: string, username: string, email: string, profile_photo: string}
     */
    public array $accountForm = [
        'name' => '',
        'username' => '',
        'email' => '',
        'profile_photo' => '',
    ];

    /**
     * @var array{name: string, email: string, phone: string, pic_name: string, logo: string}
     */
    public array $clientForm = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'pic_name' => '',
        'logo' => '',
    ];

    /**
     * @var array{current_password: string, password: string, password_confirmation: string}
     */
    public array $passwordForm = [
        'current_password' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    public function mount(): void
    {
        $this->fillFromLogin($this->login()->loadMissing('user'));
    }

    public function saveAccount(): void
    {
        $login = $this->login();

        $validated = $this->validate([
            'accountForm.name' => ['required', 'string', 'max:255'],
            'accountForm.username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('user_logins', 'username')->ignore($login->id),
            ],
            'accountForm.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user_logins', 'email')->ignore($login->id),
            ],
            'accountForm.profile_photo' => ['nullable', 'string', 'max:255'],
        ], [], [
            'accountForm.name' => 'nama tampilan',
            'accountForm.username' => 'username',
            'accountForm.email' => 'email login',
            'accountForm.profile_photo' => 'foto profil',
        ])['accountForm'];

        $updatedLogin = app(ProfileService::class)
            ->updateProfile($login, $validated)
            ->loadMissing('role');

        $this->fillFromLogin($updatedLogin->loadMissing('user'));
        $this->dispatch('starter-account-updated',
            avatarUrl: app(StarterContextService::class)->avatarUrl($updatedLogin),
            name: $updatedLogin->name,
            roleName: $updatedLogin->role?->name ?? 'Role',
        );

        $this->dispatch('starter-toast', type: 'success', message: 'Profil berhasil disimpan.');
    }

    public function saveClientProfile(): void
    {
        $this->authorizeClientManagement();

        $validated = $this->validate([
            'clientForm.name' => ['required', 'string', 'max:255'],
            'clientForm.email' => ['nullable', 'email', 'max:255'],
            'clientForm.phone' => ['nullable', 'string', 'max:255'],
            'clientForm.pic_name' => ['nullable', 'string', 'max:255'],
            'clientForm.logo' => ['nullable', 'string', 'max:255'],
        ], [], [
            'clientForm.name' => 'nama klien',
            'clientForm.email' => 'email klien',
            'clientForm.phone' => 'telepon',
            'clientForm.pic_name' => 'nama PIC',
            'clientForm.logo' => 'logo',
        ])['clientForm'];

        app(ProfileService::class)->updateClientProfile($this->login(), [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'pic_name' => $validated['pic_name'] ?? null,
            'logo' => $validated['logo'] ?? null,
        ]);

        $this->fillFromLogin($this->login()->fresh()->loadMissing('user'));
        $this->dispatch('starter-toast', type: 'success', message: 'Profil klien berhasil disimpan.');
    }

    public function changePassword(): void
    {
        $validated = $this->validate([
            'passwordForm.current_password' => ['required', 'string'],
            'passwordForm.password' => ['required', 'string', 'min:5', 'same:passwordForm.password_confirmation'],
            'passwordForm.password_confirmation' => ['required', 'string'],
        ], [], [
            'passwordForm.current_password' => 'password saat ini',
            'passwordForm.password' => 'password baru',
            'passwordForm.password_confirmation' => 'konfirmasi password',
        ])['passwordForm'];

        try {
            app(ProfileService::class)->changePassword(
                $this->login(),
                $validated['current_password'],
                $validated['password'],
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors()['current_password'] ?? [] as $message) {
                $this->addError('passwordForm.current_password', $message);
            }

            $this->dispatch('starter-toast', type: 'danger', message: $this->firstValidationMessage($exception));

            return;
        }

        $this->reset('passwordForm');
        $this->dispatch('starter-toast', type: 'success', message: 'Password berhasil diganti.');
    }

    public function render()
    {
        $login = $this->login()->loadMissing(['user', 'role']);

        return view('starter.profile.edit-my-profile', [
            'login' => $login,
            'client' => $login->user,
            'loginAvatarUrl' => app(StarterContextService::class)->avatarUrl($login),
            'canManageClient' => $this->canManageClient($login),
            'accountStatusOptions' => $this->accountStatusOptions(),
            'subscriptionStatusOptions' => $this->subscriptionStatusOptions(),
        ])->title('Edit Profil Saya');
    }

    private function login(): UserLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof UserLogin, 403);

        return $login;
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'Data tidak valid.';
    }

    private function fillFromLogin(UserLogin $login): void
    {
        $this->accountForm = [
            'name' => (string) $login->name,
            'username' => (string) $login->username,
            'email' => (string) $login->email,
            'profile_photo' => (string) $login->profile_photo,
        ];

        $client = $login->user;

        if (! $client instanceof User) {
            return;
        }

        $this->clientForm = [
            'name' => (string) $client->name,
            'email' => (string) $client->email,
            'phone' => (string) $client->phone,
            'pic_name' => (string) $client->pic_name,
            'logo' => (string) $client->logo,
        ];

    }

    private function authorizeClientManagement(): void
    {
        abort_unless($this->canManageClient($this->login()), 403);
    }

    private function canManageClient(UserLogin $login): bool
    {
        return $login->loadMissing('role')->role?->isAdmin() ?? false;
    }

    /**
     * @return array<string, string>
     */
    private function accountStatusOptions(): array
    {
        return [
            'pending' => 'Tertunda',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'suspended' => 'Ditangguhkan',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function subscriptionStatusOptions(): array
    {
        return [
            'none' => 'Tidak Ada',
            'trialing' => 'Masa Trial',
            'pending_approval' => 'Menunggu Persetujuan',
            'active' => 'Aktif',
            'past_due' => 'Lewat Jatuh Tempo',
            'canceled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
        ];
    }
}
