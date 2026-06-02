<div>
    <div class="page-header d-print-none mt-0 mb-3" aria-label="Page header">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Starter / Settings</div>
                <h2 class="page-title">Client Profile</h2>
            </div>
        </div>
    </div>

    <form class="card" wire:submit="save">
        <div class="card-body">
            <h2 class="mb-4">Client Settings</h2>

            <h3 class="card-title">Logo</h3>
            <div class="row align-items-center">
                <div class="col-auto">
                    <span class="avatar avatar-xl" @if ($clientLogoPreviewUrl) style="background-image: url({{ $clientLogoPreviewUrl }})" @endif>
                        @unless ($clientLogoPreviewUrl)
                            {{ $clientInitials }}
                        @endunless
                    </span>
                </div>
                <div class="col-auto">
                    <label class="btn btn-outline-primary mb-0" for="client-photo-upload">
                        Change Logo
                    </label>
                    <input type="file" id="client-photo-upload" class="d-none @error('clientPhotoUpload') is-invalid @enderror" wire:model="clientPhotoUpload" accept="image/*">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-ghost-danger" data-bs-toggle="modal" data-bs-target="#delete-client-photo-modal">
                        Delete Logo
                    </button>
                </div>
                <div class="col-12">
                    <input type="hidden" wire:model="clientForm.logo">
                    @error('clientPhotoUpload') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    @error('clientForm.logo') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    <div class="text-secondary small mt-2" wire:loading wire:target="clientPhotoUpload">Uploading...</div>
                </div>
            </div>

            <h3 class="card-title mt-4">Business Profile</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Client Name</label>
                    <input type="text" class="form-control @error('clientForm.name') is-invalid @enderror" wire:model="clientForm.name">
                    @error('clientForm.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">PIC Name</label>
                    <input type="text" class="form-control @error('clientForm.pic_name') is-invalid @enderror" wire:model="clientForm.pic_name">
                    @error('clientForm.pic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control @error('clientForm.phone') is-invalid @enderror" wire:model="clientForm.phone">
                    @error('clientForm.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <h3 class="card-title mt-4">Contact</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Client Email</label>
                    <input type="email" class="form-control @error('clientForm.email') is-invalid @enderror" wire:model="clientForm.email">
                    @error('clientForm.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card-footer bg-transparent mt-auto">
            <div class="btn-list justify-content-end">
                <button type="submit" class="btn btn-primary">
                    @include('templates.layouts.icon', ['name' => 'check', 'class' => 'me-1'])
                    Save Client Profile
                </button>
            </div>
        </div>
    </form>

    @include('templates.components.danger-modal', [
        'id' => 'delete-client-photo-modal',
        'title' => 'Delete logo?',
        'message' => 'The current logo will be replaced with the default logo.',
        'confirmText' => 'Delete Logo',
        'confirmAction' => 'resetClientPhoto',
        'dismissOnConfirm' => true,
    ])
</div>
