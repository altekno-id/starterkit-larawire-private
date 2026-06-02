<?php

namespace App\Livewire\Starter\Settings;

use App\Models\Starter\Client;
use App\Models\Starter\ClientLogin;
use App\Services\Starter\ProfileService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::app')]
class ClientProfile extends Component
{
    use WithFileUploads;

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

    public mixed $clientPhotoUpload = null;

    public bool $clientPhotoReset = false;

    public function mount(): void
    {
        $this->fillFromClient($this->client());
    }

    public function save(): void
    {
        $client = $this->client();

        $validated = $this->validate([
            'clientForm.name' => ['required', 'string', 'max:255'],
            'clientForm.email' => ['nullable', 'email', 'max:255'],
            'clientForm.phone' => ['nullable', 'string', 'max:255'],
            'clientForm.pic_name' => ['nullable', 'string', 'max:255'],
            'clientForm.logo' => ['nullable', 'string', 'max:255'],
            'clientPhotoUpload' => ['nullable', 'image', 'max:2048'],
        ], [], [
            'clientForm.name' => 'client name',
            'clientForm.email' => 'client email',
            'clientForm.phone' => 'phone',
            'clientForm.pic_name' => 'PIC name',
            'clientForm.logo' => 'logo',
            'clientPhotoUpload' => 'logo upload',
        ])['clientForm'];

        $oldLogo = (string) $client->logo;

        if ($this->clientPhotoUpload instanceof TemporaryUploadedFile) {
            $validated['logo'] = 'storage/'.$this->clientPhotoUpload->store(
                "starter/client-photos/{$client->id}",
                'public'
            );
        }

        $updatedClient = app(ProfileService::class)->updateClientProfile($this->login(), [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'pic_name' => $validated['pic_name'] ?? null,
            'logo' => $validated['logo'] ?? null,
        ]);

        if ($oldLogo && $oldLogo !== (string) $updatedClient->logo) {
            $this->deleteStoredClientPhoto($oldLogo);
        }

        $this->clientPhotoUpload = null;
        $this->clientPhotoReset = false;
        $this->fillFromClient($updatedClient);
        $this->dispatch('starter-toast', type: 'success', message: 'Client profile saved successfully.');
    }

    public function resetClientPhoto(): void
    {
        $client = $this->client();
        $oldLogo = (string) $client->logo;

        if ($oldLogo) {
            $updatedClient = app(ProfileService::class)->updateClientProfile($this->login(), [
                ...$this->clientForm,
                'logo' => null,
            ]);

            $this->deleteStoredClientPhoto($oldLogo);
            $this->fillFromClient($updatedClient);
            $this->dispatch('starter-toast', type: 'success', message: 'Logo reset to default.');
        }

        $this->clientPhotoUpload = null;
        $this->clientPhotoReset = true;
        $this->clientForm['logo'] = '';
        $this->resetValidation(['clientPhotoUpload', 'clientForm.logo']);
    }

    public function render()
    {
        return view('starter.settings.client-profile', [
            'client' => $this->client(),
            'clientLogoPreviewUrl' => $this->clientLogoPreviewUrl(),
            'clientInitials' => $this->clientInitials(),
        ])->title('Client Profile');
    }

    private function login(): ClientLogin
    {
        $login = auth()->user();

        abort_unless($login instanceof ClientLogin && ($login->loadMissing('role')->role?->isAdmin() ?? false), 403);

        return $login->loadMissing('client');
    }

    private function client(): Client
    {
        $client = $this->login()->client;

        abort_unless($client instanceof Client, 403);

        return $client;
    }

    private function fillFromClient(Client $client): void
    {
        $this->clientForm = [
            'name' => (string) $client->name,
            'email' => (string) $client->email,
            'phone' => (string) $client->phone,
            'pic_name' => (string) $client->pic_name,
            'logo' => (string) $client->logo,
        ];
    }

    private function clientLogoPreviewUrl(): ?string
    {
        if ($this->clientPhotoUpload instanceof TemporaryUploadedFile) {
            return $this->clientPhotoUpload->temporaryUrl();
        }

        $logo = trim((string) ($this->clientForm['logo'] ?? ''));

        if ($logo !== '') {
            if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '//')) {
                return $logo;
            }

            return asset(ltrim($logo, '/'));
        }

        if ($this->clientPhotoReset) {
            return null;
        }

        return null;
    }

    private function clientInitials(): string
    {
        return str($this->clientForm['name'] ?: 'Client')
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => str($part)->substr(0, 1)->upper()->toString())
            ->implode('');
    }

    private function deleteStoredClientPhoto(string $logo): void
    {
        if (! str_starts_with($logo, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(str($logo)->after('storage/')->toString());
    }

}
