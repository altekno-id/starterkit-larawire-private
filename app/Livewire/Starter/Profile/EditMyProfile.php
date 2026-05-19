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
     * @var array{name: string, username: string, email: string}
     */
    public array $accountForm = [
        'name' => '',
        'username' => '',
        'email' => '',
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
     * @var array{account_status: string, approved_at: string, subscription_status: string, payment_method: string, payment_reference: string, trial_ends_at: string, subscribed_at: string, subscription_ends_at: string, payment_approved_at: string}
     */
    public array $adminForm = [
        'account_status' => 'pending',
        'approved_at' => '',
        'subscription_status' => 'none',
        'payment_method' => '',
        'payment_reference' => '',
        'trial_ends_at' => '',
        'subscribed_at' => '',
        'subscription_ends_at' => '',
        'payment_approved_at' => '',
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
        ], [], [
            'accountForm.name' => 'nama login',
            'accountForm.username' => 'username',
            'accountForm.email' => 'email login',
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

        $this->dispatch('starter-toast', type: 'success', message: 'Profile berhasil disimpan.');
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
            'clientForm.name' => 'nama client',
            'clientForm.email' => 'email client',
            'clientForm.phone' => 'phone',
            'clientForm.pic_name' => 'PIC name',
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
        $this->dispatch('starter-toast', type: 'success', message: 'Client profile berhasil disimpan.');
    }

    public function saveAdminControls(): void
    {
        $this->authorizeClientManagement();

        $validated = $this->validate([
            'adminForm.account_status' => ['required', Rule::in(array_keys($this->accountStatusOptions()))],
            'adminForm.approved_at' => ['nullable', 'date'],
            'adminForm.subscription_status' => ['required', Rule::in(array_keys($this->subscriptionStatusOptions()))],
            'adminForm.payment_method' => ['nullable', 'string', 'max:30'],
            'adminForm.payment_reference' => ['nullable', 'string', 'max:255'],
            'adminForm.trial_ends_at' => ['nullable', 'date'],
            'adminForm.subscribed_at' => ['nullable', 'date'],
            'adminForm.subscription_ends_at' => ['nullable', 'date'],
            'adminForm.payment_approved_at' => ['nullable', 'date'],
        ], [], [
            'adminForm.account_status' => 'account status',
            'adminForm.approved_at' => 'approved at',
            'adminForm.subscription_status' => 'subscription status',
            'adminForm.payment_method' => 'payment method',
            'adminForm.payment_reference' => 'payment reference',
            'adminForm.trial_ends_at' => 'trial ends at',
            'adminForm.subscribed_at' => 'subscribed at',
            'adminForm.subscription_ends_at' => 'subscription ends at',
            'adminForm.payment_approved_at' => 'payment approved at',
        ])['adminForm'];

        app(ProfileService::class)->updateAdminControls($this->login(), [
            'account_status' => $validated['account_status'],
            'approved_at' => $validated['approved_at'] ?? null,
            'subscription_status' => $validated['subscription_status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'trial_ends_at' => $validated['trial_ends_at'] ?? null,
            'subscribed_at' => $validated['subscribed_at'] ?? null,
            'subscription_ends_at' => $validated['subscription_ends_at'] ?? null,
            'payment_approved_at' => $validated['payment_approved_at'] ?? null,
        ]);

        $this->fillFromLogin($this->login()->fresh()->loadMissing('user'));
        $this->dispatch('starter-toast', type: 'success', message: 'Kontrol admin berhasil disimpan.');
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
            'canManageClient' => $this->canManageClient($login),
            'accountStatusOptions' => $this->accountStatusOptions(),
            'subscriptionStatusOptions' => $this->subscriptionStatusOptions(),
        ])->title('Edit My Profile');
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

        $this->adminForm = [
            'account_status' => (string) $client->account_status,
            'approved_at' => $this->dateInputValue($client->approved_at),
            'subscription_status' => (string) $client->subscription_status,
            'payment_method' => (string) $client->payment_method,
            'payment_reference' => (string) $client->payment_reference,
            'trial_ends_at' => $this->dateInputValue($client->trial_ends_at),
            'subscribed_at' => $this->dateInputValue($client->subscribed_at),
            'subscription_ends_at' => $this->dateInputValue($client->subscription_ends_at),
            'payment_approved_at' => $this->dateInputValue($client->payment_approved_at),
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

    private function dateInputValue(mixed $value): string
    {
        return $value ? $value->format('Y-m-d\TH:i') : '';
    }

    /**
     * @return array<string, string>
     */
    private function accountStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'suspended' => 'Suspended',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function subscriptionStatusOptions(): array
    {
        return [
            'none' => 'None',
            'trialing' => 'Trialing',
            'pending_approval' => 'Pending Approval',
            'active' => 'Active',
            'past_due' => 'Past Due',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
        ];
    }
}
