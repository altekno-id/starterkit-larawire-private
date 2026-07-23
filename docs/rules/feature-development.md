# Menambah atau Mengubah Feature

Gunakan checklist ini setelah issue plan disetujui.

## 1. Tentukan ownership

- Pilih app/subdomain pemilik feature.
- Pilih module existing atau buat module baru.
- Buat module baru jika flow, UI, atau audiensnya berbeda secara nyata; jangan membuat conditional role yang besar dalam satu page.
- Tetapkan route landing module dan role yang harus mendapat akses.

## 2. Persistence dan business flow

- Buat migration/model/factory dengan Artisan bila ada tabel baru.
- Gunakan nama tabel bisnis yang jelas; prefix `starter_` hanya untuk infrastruktur starterkit.
- Letakkan validasi dan authorization pada boundary action.
- Letakkan flow lintas model dalam service dan `DB::transaction()`.
- Integrasikan audit log sebelum menulis UI; baca `audit-logging.md`.

## 3. Livewire dan view

- Class: `app/Livewire/Apps/<AppStudly>/<ModuleStudly>/`.
- View: `resources/views/apps/<app-key>/<module-key>/`.
- Gunakan `#[Layout('layouts::app')]`.
- State tetap di server; Alpine/JavaScript hanya untuk interaksi client yang tidak cocok menjadi request Livewire.
- Gunakan komponen/markup Tabler dari referensi project; baca `ui-ux.md`.

## 4. Route

Tambahkan ke `routes/apps/<app-key>.php` di dalam group:

```php
Route::middleware(['auth:web', 'starter.active', 'starter.password-change', 'starter.lock'])
    ->group(function (): void {
        Route::middleware('starter.authorize')->group(function (): void {
            Route::prefix('pegawai')->name('pegawai.')->group(function (): void {
                Route::livewire('/', PegawaiIndex::class)->name('index');
            });
        });
    });
```

Nama akhir wajib mengikuti `<app-key>.<module-code>.<action>`, misalnya `hr.pegawai.index`.

## 5. Config module dan menu

Tambahkan module di `config/apps/<app-key>.php`:

```php
'pegawai' => [
    'name' => 'Data Pegawai',
    'desc' => 'Kelola data pegawai.',
    'menus' => [
        [
            'label' => 'Data Pegawai',
            'icon' => 'users',
            'route' => 'hr.pegawai.index',
            'landing' => true,
        ],
    ],
],
```

- Setiap module harus memiliki `name`.
- `route` menu harus ada dan dimiliki module yang sama.
- `landing => true` wajib menunjuk route dan menentukan halaman awal module/app.
- Parent menu tanpa route dapat memakai `children`.
- Icon memakai nama Tabler tanpa prefix.

## 6. Sync

```bash
php artisan starter:sync <app-key> --dry-run
php artisan starter:sync <app-key> --force
```

Dry run wajib diperiksa sebelum apply. Sync dapat menghapus metadata yang tidak lagi ada di config/route.

## 7. Verifikasi minimum

- Test business flow dan authorization.
- Test Livewire action/validation.
- Test route terdaftar dan route terlarang menghasilkan 403.
- Test audit log create/update/delete.
- Jalankan Pint dan test sesuai `testing.md`.
- Bila UI berubah, lakukan pengujian browser pada ukuran desktop dan mobile yang relevan.
