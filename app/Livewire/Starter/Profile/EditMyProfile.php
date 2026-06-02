<?php

namespace App\Livewire\Starter\Profile;

use App\Models\Starter\ClientLogin;
use App\Services\Starter\ProfileService;
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

    /**
     * @var array{name: string, username: string, email: string, profile_photo: string}
     */
    public array $accountForm = [
        'name' => '',
        'username' => '',
        'email' => '',
        'profile_photo' => '',
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
        $this->fillFromLogin($this->login()->loadMissing('client'));
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
                Rule::unique('client_logins', 'username')->ignore($login->id),
            ],
            'accountForm.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('client_logins', 'email')->ignore($login->id),
            ],
            'accountForm.profile_photo' => ['nullable', 'string', 'max:255'],
            'profilePhotoUpload' => ['nullable', 'image', 'max:2048'],
        ], [], [
            'accountForm.name' => 'display name',
            'accountForm.username' => 'username',
            'accountForm.email' => 'email login',
            'accountForm.profile_photo' => 'profile photo',
            'profilePhotoUpload' => 'profile photo upload',
        ])['accountForm'];

        $oldProfilePhoto = (string) $login->profile_photo;

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
            $this->deleteStoredProfilePhoto($oldProfilePhoto);
        }

        $this->profilePhotoUpload = null;
        $this->profilePhotoReset = false;
        $this->fillFromLogin($updatedLogin->loadMissing('client'));
        $this->dispatch('starter-account-updated',
            avatarUrl: app(StarterContextService::class)->avatarUrl($updatedLogin),
            name: $updatedLogin->name,
            roleName: $updatedLogin->role?->name ?? 'Role',
        );

        $this->dispatch('starter-toast', type: 'success', message: 'Profile saved successfully.');
    }

    public function resetProfilePhoto(): void
    {
        $login = $this->login();
        $oldProfilePhoto = (string) $login->profile_photo;

        if ($oldProfilePhoto) {
            $updatedLogin = app(ProfileService::class)
                ->updateProfile($login, [
                    'name' => $login->name,
                    'username' => $login->username,
                    'email' => $login->email,
                    'profile_photo' => self::DEFAULT_PROFILE_PHOTO,
                ])
                ->loadMissing('role');

            $this->deleteStoredProfilePhoto($oldProfilePhoto);

            $this->dispatch('starter-account-updated',
                avatarUrl: app(StarterContextService::class)->avatarUrl($updatedLogin),
                name: $updatedLogin->name,
                roleName: $updatedLogin->role?->name ?? 'Role',
            );

            $this->dispatch('starter-toast', type: 'success', message: 'Profile photo reset to default.');
        }

        $this->profilePhotoUpload = null;
        $this->profilePhotoReset = true;
        $this->accountForm['profile_photo'] = self::DEFAULT_PROFILE_PHOTO;
        $this->resetValidation(['profilePhotoUpload', 'accountForm.profile_photo']);
    }

    public function changePassword(): void
    {
        $validated = $this->validate([
            'passwordForm.current_password' => ['required', 'string'],
            'passwordForm.password' => ['required', 'string', 'min:5', 'same:passwordForm.password_confirmation'],
            'passwordForm.password_confirmation' => ['required', 'string'],
        ], [], [
            'passwordForm.current_password' => 'current password',
            'passwordForm.password' => 'new password',
            'passwordForm.password_confirmation' => 'password confirmation',
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
        $this->dispatch('starter-toast', type: 'success', message: 'Password changed successfully.');
    }

    public function render()
    {
        $login = $this->login()->loadMissing(['client', 'role']);

        return view('starter.profile.edit-my-profile', [
            'login' => $login,
            'loginAvatarUrl' => app(StarterContextService::class)->avatarUrl($login),
            'profilePhotoPreviewUrl' => $this->profilePhotoPreviewUrl($login),
        ])->title('Edit My Profile');
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof ClientLogin, 403);

        return $login;
    }

    private function firstValidationMessage(ValidationException $exception): string
    {
        return collect($exception->errors())->flatten()->first() ?? 'Invalid data.';
    }

    private function fillFromLogin(ClientLogin $login): void
    {
        $this->profilePhotoReset = false;

        $this->accountForm = [
            'name' => (string) $login->name,
            'username' => (string) $login->username,
            'email' => (string) $login->email,
            'profile_photo' => (string) $login->profile_photo,
        ];

    }

    private function profilePhotoPreviewUrl(ClientLogin $login): string
    {
        if ($this->profilePhotoUpload instanceof TemporaryUploadedFile) {
            return $this->profilePhotoUpload->temporaryUrl();
        }

        $profilePhoto = trim((string) ($this->accountForm['profile_photo'] ?? ''));

        if ($profilePhoto !== '') {
            if (str_starts_with($profilePhoto, 'http://') || str_starts_with($profilePhoto, 'https://') || str_starts_with($profilePhoto, '//')) {
                return $profilePhoto;
            }

            return asset(ltrim($profilePhoto, '/'));
        }

        if ($this->profilePhotoReset) {
            return asset(self::DEFAULT_PROFILE_PHOTO);
        }

        return app(StarterContextService::class)->avatarUrl($login);
    }

    private function deleteStoredProfilePhoto(string $profilePhoto): void
    {
        if (! str_starts_with($profilePhoto, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(str($profilePhoto)->after('storage/')->toString());
    }

}
