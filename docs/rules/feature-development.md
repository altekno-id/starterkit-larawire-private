# Menambah atau Mengubah Feature

Gunakan checklist ini untuk setiap permintaan pembuatan atau perubahan feature
yang membutuhkan code pada project yang dibangun dari starterkit.

## 0. Gerbang spesifikasi teknis

Setiap prompt yang meminta pembuatan atau perubahan feature wajib berhenti pada
tahap spesifikasi sebelum mengubah code:

1. Lakukan discovery minimum berbasis source existing dan rule terkait.
2. Buat satu file `issues/<feature-slug>.md` di root Laravel host. Jangan membuat
   folder template, archive, atau issue tambahan untuk feature yang sama.
3. Isi ringkas tetapi implementable: tujuan, scope in/out, flow, ownership
   app/module, file/layer terdampak, data dan migration, authorization,
   validation, audit, performa, UI/interaksi, test, acceptance criteria, risiko,
   serta keputusan yang masih dibutuhkan.
4. Referensikan rule starterkit yang berlaku tanpa menyalin seluruh isinya.
5. Jangan membuat migration, model, service, repository, Livewire, route, view,
   atau code feature lain pada giliran yang sama.
6. Setelah file dibuat, beri respons bahwa detail teknis sudah siap untuk dibaca
   ulang. Jelaskan bahwa implementasi berikutnya dapat memakai model yang lebih
   hemat/rendah karena konteks teknis telah dikunci oleh file tersebut.
7. Tunggu persetujuan eksplisit user sebelum implementasi.

Gerbang ini berlaku untuk setiap feature, module, CRUD, workflow, halaman
bisnis, atau perubahan perilaku feature yang diminta user. Bugfix, diagnosis,
refactor kecil, maintenance, security patch, dan dokumentasi tidak membuat
issue otomatis kecuali diminta.

## 1. Discovery berbasis bukti

- Telusuri entry point, route, menu/module, Livewire/controller, service, interface/repository, model, migration, config, dan test existing yang berhubungan.
- Periksa schema dan arti data existing sebelum merancang migration atau mengubah validation.
- Pisahkan requirement terkonfirmasi, kondisi existing, proposal, dan pertanyaan terbuka selama discovery.
- Jangan mengasumsikan status, role, approval, nomor dokumen, ownership data, integrasi, atau failure behavior yang tidak dinyatakan user dan tidak ditemukan di project.
- Tetapkan scope in/out, acceptance criteria, authorization, dampak data, audit log, performa, dan rollback sebelum implementasi.
- Gunakan hanya file issue teknis yang diwajibkan bagian 0; jangan membuat
  planning atau archive tambahan.

## 2. Tentukan ownership

- Pilih app/subdomain pemilik feature.
- Pilih module existing atau buat module baru.
- Buat module baru jika flow, UI, atau audiensnya berbeda secara nyata; jangan membuat conditional role yang besar dalam satu page.
- Tetapkan route landing module dan role yang harus mendapat akses.

## 3. Persistence dan business flow

- Buat model dan migration app dengan Artisan bila ada tabel baru. Migration wajib berada di `database/migrations/apps/<app-key>/`; jangan gunakan `make:model -m` karena path migration app harus eksplisit.
- Ikuti seluruh aturan migration production-safe pada `code-style.md`; gunakan expand → backfill → contract untuk perubahan constraint/tipe yang berisiko.
- Gunakan pola penamaan tabel bisnis pada `code-style.md`; prefix `starter_` hanya untuk infrastruktur starterkit.
- Letakkan validasi dan authorization pada boundary action.
- Terapkan pola default `architecture.md`: contract/interface untuk boundary persistence, repository untuk seluruh query/persistence domain, dan service untuk business logic/transaksi/orchestration.
- Service membuka `DB::transaction()` untuk mutation yang harus atomik; repository tidak menentukan batas transaksi lintas use case.
- Integrasikan audit log sebelum menulis UI; baca `audit-logging.md`.

## 4. Livewire dan view

- Class: `app/Livewire/Apps/<AppStudly>/<ModuleStudly>/`.
- View: `resources/views/apps/<app-key>/<module-key>/`.
- CSS/JS custom halaman berada di `assets/<page>.css.blade.php` dan/atau `assets/<page>.js.blade.php` di samping view pemilik. Jangan memuat asset halaman dari layout global; ikuti lifecycle `@assets`/`@script` pada `ui-ux.md`.
- Gunakan `#[Layout('layouts::app')]`.
- State tetap di server; Alpine/JavaScript hanya untuk interaksi client yang tidak cocok menjadi request Livewire.
- Form normal memakai `wire:model.defer` dan `wire:submit`; live request hanya untuk pengecualian yang diizinkan `ui-ux.md`.
- Tentukan jenis dan volume data sebelum menata UI, lalu cari `docs/template/template.md` berdasarkan konteks dan komponen untuk membandingkan beberapa kandidat template UI. Buka satu sampai tiga HTML sumber yang paling relevan, termasuk contoh lintas konteks bila membantu; gunakan tabel untuk data banyak yang perlu dibandingkan, dan card/list/detail untuk ringkasan atau data sedikit. Jangan mendesain dari nol.
- Gunakan komponen/markup template UI aktif dari referensi project; baca `ui-ux.md`.
- Terapkan query, pagination, cache, dan batas resource dari `performance.md` tanpa menunggu user menyebutkannya.

## 5. Route

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

## 6. Config module dan menu

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
- Icon mengikuti icon set yang tersedia pada template UI aktif; jangan mengasumsikan nama atau prefix tertentu tanpa memeriksa source aktual.

## 7. Sync

```bash
php artisan starter:sync <app-key> --dry-run
php artisan starter:sync <app-key> --force
```

Dry run wajib diperiksa sebelum apply. Sync dapat menghapus metadata yang tidak lagi ada di config/route.

## 8. Verifikasi minimum

- Test business flow dan authorization.
- Test Livewire action/validation.
- Test route terdaftar dan route terlarang menghasilkan 403.
- Test audit log create/update/delete atau security event yang relevan.
- Test server-side pagination/query budget bila data feature dapat bertambah.
- Jalankan Pint dan test sesuai `testing.md`.
- Bila UI berubah, lakukan pengujian browser pada ukuran desktop dan mobile yang relevan.
- Uji empty state serta data sedikit dan banyak bila halaman menampilkan data; pastikan komponen yang dipilih tetap padat, informatif, dan mudah dipindai.
