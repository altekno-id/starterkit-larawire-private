<?php

namespace App\Livewire\Starter\Profile;

use App\Models\Starter\ClientLogin;
use App\Rules\StarterPasswordRules;
use App\Services\Starter\NavigationAuthorizedRedirectService;
use App\Services\Starter\ProfileService;
use App\Services\Starter\StarterConfigService;
use App\Services\Starter\StarterContextService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::app')]
class EditMyProfile extends Component
{
    use WithFileUploads;

    private const DEFAULT_PROFILE_PHOTO = 'assets/mine/avatar.png';

    public string $activeTab = 'account-details';

    /** @var array{name: string, email: string} */
    public array $accountForm = [
        'name' => '',
        'email' => '',
    ];

    public mixed $profilePhotoUpload = null;

    public bool $profilePhotoReset = false;

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
        $login = $this->login();
        $this->activeTab = $login->must_change_password || request()->query('tab') === 'security'
            ? 'security'
            : 'account-details';
        $this->fillFromLogin($login);
    }

    public function saveAccount(): void
    {
        $this->activeTab = 'account-details';
        $login = $this->login();

        $validated = $this->validate([
            'accountForm.name' => ['required', 'string', 'max:255'],
            'accountForm.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('starter_client_logins', 'email')->ignore($login->id),
            ],
            'profilePhotoUpload' => ['nullable', 'image', 'max:'.app(StarterConfigService::class)->uploadImageMaxKilobytes()],
        ], [], [
            'accountForm.name' => 'display name',
            'accountForm.email' => 'email login',
            'profilePhotoUpload' => 'profile photo upload',
        ])['accountForm'];

        $oldProfilePhoto = (string) $login->profile_photo;
        $validated['profile_photo'] = $oldProfilePhoto;

        if ($this->profilePhotoUpload instanceof TemporaryUploadedFile) {
            $validated['profile_photo'] = 'storage/'.$this->profilePhotoUpload->store(
                "starter/profile-photos/{$login->id}",
                'public'
            );
        }

        $updatedLogin = app(ProfileService::class)
            ->updateProfile($login, $validated)
            ->loadMissing('role');

        if ($oldProfilePhoto && $oldProfilePhoto !== (string) $updatedLogin->profile_photo) {
            $this->deleteStoredProfilePhoto($oldProfilePhoto, $login->id);
        }

        $this->profilePhotoUpload = null;
        $this->profilePhotoReset = false;
        $this->fillFromLogin($updatedLogin);
        $this->dispatch('starter-account-updated',
            avatarUrl: app(StarterContextService::class)->avatarUrl($updatedLogin),
            name: $updatedLogin->name,
            roleName: $updatedLogin->role?->name ?? 'Role',
        );

        $this->dispatch('starter-toast', type: 'success', message: 'Profil berhasil disimpan.');
    }

    public function resetProfilePhoto(): void
    {
        $this->activeTab = 'account-details';
        $login = $this->login();
        $oldProfilePhoto = (string) $login->profile_photo;

        if ($oldProfilePhoto) {
            $updatedLogin = app(ProfileService::class)
                ->updateProfile($login, [
                    'name' => $login->name,
                    'email' => $login->email,
                    'profile_photo' => self::DEFAULT_PROFILE_PHOTO,
                ])
                ->loadMissing('role');

            $this->deleteStoredProfilePhoto($oldProfilePhoto, $login->id);

            $this->dispatch('starter-account-updated',
                avatarUrl: app(StarterContextService::class)->avatarUrl($updatedLogin),
                name: $updatedLogin->name,
                roleName: $updatedLogin->role?->name ?? 'Role',
            );

            $this->dispatch('starter-toast', type: 'success', message: 'Foto profil berhasil dikembalikan ke default.');
        }

        $this->profilePhotoUpload = null;
        $this->profilePhotoReset = true;
        $this->resetValidation('profilePhotoUpload');
    }

    public function changePassword(): mixed
    {
        $this->activeTab = 'security';
        $login = $this->login();
        $passwordChangeWasRequired = $login->must_change_password;

        $validated = $this->validate([
            'passwordForm.current_password' => ['required', 'string'],
            'passwordForm.password' => [...StarterPasswordRules::rules(), 'same:passwordForm.password_confirmation'],
            'passwordForm.password_confirmation' => ['required', 'string'],
        ], [], [
            'passwordForm.current_password' => 'password saat ini',
            'passwordForm.password' => 'password baru',
            'passwordForm.password_confirmation' => 'konfirmasi password',
        ])['passwordForm'];

        try {
            app(ProfileService::class)->changePassword(
                $login,
                $validated['current_password'],
                $validated['password'],
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors()['current_password'] ?? [] as $message) {
                $this->addError('passwordForm.current_password', $message);
            }

            $this->dispatch('starter-toast', type: 'danger', message: $this->firstValidationMessage($exception));

            return null;
        }

        $this->reset('passwordForm');

        if ($passwordChangeWasRequired) {
            session()->flash('starter-toast', [
                'type' => 'success',
                'message' => 'Password berhasil diubah. Anda sudah dapat menggunakan aplikasi.',
            ]);

            return $this->redirect(
                app(NavigationAuthorizedRedirectService::class)->firstAuthorizedUrl($login->fresh(['role'])),
            );
        }

        $this->dispatch('starter-toast', type: 'success', message: 'Password berhasil diubah.');

        return null;
    }

    public function showTab(string $tab): void
    {
        if ($this->login()->must_change_password) {
            $this->activeTab = 'security';

            return;
        }

        if (in_array($tab, ['account-details', 'security'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function render()
    {
        $login = $this->login()->loadMissing('role');

        return view('starter.profile.edit-my-profile', [
            'login' => $login,
            'loginAvatarUrl' => app(StarterContextService::class)->avatarUrl($login),
            'profilePhotoPreviewUrl' => $this->profilePhotoPreviewUrl($login),
        ])->title('Edit Profil Saya');
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof ClientLogin, 403);

        return $login;
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'Data tidak valid.';
    }

    private function fillFromLogin(ClientLogin $login): void
    {
        $this->profilePhotoReset = false;

        $this->accountForm = [
            'name' => (string) $login->name,
            'email' => (string) $login->email,
        ];
    }

    private function profilePhotoPreviewUrl(ClientLogin $login): string
    {
        if ($this->profilePhotoUpload instanceof TemporaryUploadedFile) {
            return $this->profilePhotoUpload->temporaryUrl();
        }

        if ($this->profilePhotoReset) {
            return asset(self::DEFAULT_PROFILE_PHOTO);
        }

        return app(StarterContextService::class)->avatarUrl($login);
    }

    private function deleteStoredProfilePhoto(string $profilePhoto, int $loginId): void
    {
        $ownedPrefix = "storage/starter/profile-photos/{$loginId}/";

        if (! str_starts_with($profilePhoto, $ownedPrefix)) {
            return;
        }

        Storage::disk('public')->delete(str($profilePhoto)->after('storage/')->toString());
    }
}
