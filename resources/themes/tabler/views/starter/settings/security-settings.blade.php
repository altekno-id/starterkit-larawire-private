<form wire:submit="save">
    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="border rounded h-100">
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-primary-lt text-primary">
                            @include('starter.templates.layouts.icon', ['name' => 'shield-lock', 'class' => 'icon'])
                        </span>
                        <div>
                            <h3 class="card-title mb-1">Sesi dan Lock Screen</h3>
                            <div class="text-secondary">Atur sesi tetap login dan penguncian otomatis.</div>
                        </div>
                    </div>
                </div>
                <div class="p-3">
                    <label class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" wire:model.defer="securityForm.remember_me_enabled">
                        <span class="form-check-label">
                            <span class="d-block fw-semibold">Aktifkan Remember Me</span>
                            <span class="d-block small text-secondary">User dapat memilih tetap login pada perangkat yang dipercaya.</span>
                        </span>
                    </label>

                    <label class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" wire:model.defer="securityForm.lock_screen_enabled">
                        <span class="form-check-label">
                            <span class="d-block fw-semibold">Aktifkan Lock Screen Otomatis</span>
                            <span class="d-block small text-secondary">Aplikasi dikunci tanpa mengakhiri sesi login.</span>
                        </span>
                    </label>

                    <div>
                        <label class="form-label" for="lock-timeout">Kunci setelah tidak aktif</label>
                        <div class="input-group">
                            <input
                                type="number"
                                class="form-control @error('securityForm.lock_screen_timeout_minutes') is-invalid @enderror"
                                id="lock-timeout"
                                min="1"
                                max="1440"
                                wire:model.defer="securityForm.lock_screen_timeout_minutes"
                                @disabled(! $securityForm['lock_screen_enabled'])
                            >
                            <span class="input-group-text">menit</span>
                            @error('securityForm.lock_screen_timeout_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-hint">Rentang 1–1.440 menit. Rekomendasi untuk komputer bersama: 10–15 menit.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="border rounded h-100">
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-orange-lt text-orange">
                            @include('starter.templates.layouts.icon', ['name' => 'lock', 'class' => 'icon'])
                        </span>
                        <div>
                            <h3 class="card-title mb-1">Proteksi Login</h3>
                            <div class="text-secondary">Batasi percobaan login untuk mengurangi serangan brute force.</div>
                        </div>
                    </div>
                </div>
                <div class="p-3">
                    <div class="mb-4">
                        <label class="form-label" for="login-attempts">Maksimum percobaan login</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('securityForm.login_max_attempts') is-invalid @enderror" id="login-attempts" min="1" max="20" wire:model.defer="securityForm.login_max_attempts">
                            <span class="input-group-text">kali</span>
                            @error('securityForm.login_max_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="login-decay">Durasi pembatasan</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('securityForm.login_decay_seconds') is-invalid @enderror" id="login-decay" min="30" max="3600" wire:model.defer="securityForm.login_decay_seconds">
                            <span class="input-group-text">detik</span>
                            @error('securityForm.login_decay_seconds')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-hint">Penghitung akan kembali normal setelah user berhasil login.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="border rounded">
                <div class="p-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <span class="avatar bg-azure-lt text-azure">
                            @include('starter.templates.layouts.icon', ['name' => 'file-plus', 'class' => 'icon'])
                        </span>
                        <div>
                            <h3 class="card-title mb-1">Kebijakan Upload</h3>
                            <div class="text-secondary">Batas ini berlaku untuk logo perusahaan dan foto profil.</div>
                        </div>
                    </div>
                </div>
                <div class="p-3">
                    <div class="row">
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-label" for="upload-max-size">Ukuran maksimum gambar</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('securityForm.max_image_size_kb') is-invalid @enderror" id="upload-max-size" min="256" max="10240" step="256" wire:model.defer="securityForm.max_image_size_kb">
                                <span class="input-group-text">KB</span>
                                @error('securityForm.max_image_size_kb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-hint">Maksimum 10.240 KB (10 MB). Sesuaikan juga batas PHP pada hosting.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end pt-4 mt-4 border-top">
        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">
                @include('starter.templates.layouts.icon', ['name' => 'check', 'class' => 'icon'])
                Simpan Konfigurasi
            </span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>
</form>
