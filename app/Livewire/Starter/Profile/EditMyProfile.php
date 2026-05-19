<?php

namespace App\Livewire\Starter\Profile;

use App\Models\Starter\User;
use App\Models\Starter\UserLogin;
use App\Services\Starter\Profile\ProfileService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class EditMyProfile extends Component
{
    public string $pageTitle = 'Edit My Profile';

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $clientName = '';

    public string $clientEmail = '';

    public string $clientPhone = '';

    public string $clientPicName = '';

    public string $clientLogo = '';

    public string $accountStatus = 'pending';

    public string $approvedAt = '';

    public string $subscriptionStatus = 'none';

    public string $paymentMethod = '';

    public string $paymentReference = '';

    public string $trialEndsAt = '';

    public string $subscribedAt = '';

    public string $subscriptionEndsAt = '';

    public string $paymentApprovedAt = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function mount(): void
    {
        $this->fillFromLogin($this->login()->loadMissing('user'));
    }

    public function saveAccount(): void
    {
        $login = $this->login();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('user_logins', 'username')->ignore($login->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user_logins', 'email')->ignore($login->id),
            ],
        ]);

        app(ProfileService::class)->updateProfile($login, $validated);

        session()->flash('status', 'Profile berhasil disimpan.');
    }

    public function saveClientProfile(): void
    {
        $this->authorizeClientManagement();

        $validated = $this->validate([
            'clientName' => ['required', 'string', 'max:255'],
            'clientEmail' => ['nullable', 'email', 'max:255'],
            'clientPhone' => ['nullable', 'string', 'max:255'],
            'clientPicName' => ['nullable', 'string', 'max:255'],
            'clientLogo' => ['nullable', 'string', 'max:255'],
        ]);

        app(ProfileService::class)->updateClientProfile($this->login(), [
            'name' => $validated['clientName'],
            'email' => $validated['clientEmail'] ?? null,
            'phone' => $validated['clientPhone'] ?? null,
            'pic_name' => $validated['clientPicName'] ?? null,
            'logo' => $validated['clientLogo'] ?? null,
        ]);

        $this->fillFromLogin($this->login()->fresh()->loadMissing('user'));
        session()->flash('status', 'Client profile berhasil disimpan.');
    }

    public function saveAdminControls(): void
    {
        $this->authorizeClientManagement();

        $validated = $this->validate([
            'accountStatus' => ['required', Rule::in(array_keys($this->accountStatusOptions()))],
            'approvedAt' => ['nullable', 'date'],
            'subscriptionStatus' => ['required', Rule::in(array_keys($this->subscriptionStatusOptions()))],
            'paymentMethod' => ['nullable', 'string', 'max:30'],
            'paymentReference' => ['nullable', 'string', 'max:255'],
            'trialEndsAt' => ['nullable', 'date'],
            'subscribedAt' => ['nullable', 'date'],
            'subscriptionEndsAt' => ['nullable', 'date'],
            'paymentApprovedAt' => ['nullable', 'date'],
        ]);

        app(ProfileService::class)->updateAdminControls($this->login(), [
            'account_status' => $validated['accountStatus'],
            'approved_at' => $validated['approvedAt'] ?? null,
            'subscription_status' => $validated['subscriptionStatus'],
            'payment_method' => $validated['paymentMethod'] ?? null,
            'payment_reference' => $validated['paymentReference'] ?? null,
            'trial_ends_at' => $validated['trialEndsAt'] ?? null,
            'subscribed_at' => $validated['subscribedAt'] ?? null,
            'subscription_ends_at' => $validated['subscriptionEndsAt'] ?? null,
            'payment_approved_at' => $validated['paymentApprovedAt'] ?? null,
        ]);

        $this->fillFromLogin($this->login()->fresh()->loadMissing('user'));
        session()->flash('status', 'Kontrol admin berhasil disimpan.');
    }

    public function changePassword(): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'string'],
            'password' => ['required', 'string', 'min:5', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ]);

        app(ProfileService::class)->changePassword(
            $this->login(),
            $validated['currentPassword'],
            $validated['password'],
        );

        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
        session()->flash('status', 'Password berhasil diganti.');
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
        ])->title($this->pageTitle);
    }

    private function login(): UserLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof UserLogin, 403);

        return $login;
    }

    private function fillFromLogin(UserLogin $login): void
    {
        $this->name = (string) $login->name;
        $this->username = (string) $login->username;
        $this->email = (string) $login->email;

        $client = $login->user;

        if (! $client instanceof User) {
            return;
        }

        $this->clientName = (string) $client->name;
        $this->clientEmail = (string) $client->email;
        $this->clientPhone = (string) $client->phone;
        $this->clientPicName = (string) $client->pic_name;
        $this->clientLogo = (string) $client->logo;
        $this->accountStatus = (string) $client->account_status;
        $this->approvedAt = $this->dateInputValue($client->approved_at);
        $this->subscriptionStatus = (string) $client->subscription_status;
        $this->paymentMethod = (string) $client->payment_method;
        $this->paymentReference = (string) $client->payment_reference;
        $this->trialEndsAt = $this->dateInputValue($client->trial_ends_at);
        $this->subscribedAt = $this->dateInputValue($client->subscribed_at);
        $this->subscriptionEndsAt = $this->dateInputValue($client->subscription_ends_at);
        $this->paymentApprovedAt = $this->dateInputValue($client->payment_approved_at);
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
