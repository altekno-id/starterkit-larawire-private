# Konfirmasi, Spesifikasi, dan Eksekusi Request

Gunakan checklist ini untuk setiap request feature baru, perubahan feature, atau
bug yang membutuhkan perubahan code pada project yang dibangun dari starterkit.

## 0. Gerbang konfirmasi chat

Setiap prompt implementasi wajib dipahami dan dikonfirmasi di chat sebelum file
planning teknis dibuat. Tahap ini memastikan developer dan pelaksana memiliki
definisi masalah yang sama tanpa membuang waktu menulis spesifikasi yang salah.

1. Klasifikasikan request sebagai `Feature` untuk kemampuan baru/perubahan
   perilaku yang disengaja, atau `Bug` untuk perilaku existing yang tidak sesuai
   requirement.
2. Lakukan discovery awal baca-saja secukupnya untuk membuktikan repository,
   App, module, entry point, dan perilaku existing yang relevan. Jangan mengubah
   code, database, config, dependency, atau membuat file issue.
3. Untuk module baru, validasi kontrak navigasi minimum pada bagian
   **Requirement module baru** di bawah. Jika belum lengkap, arahkan developer
   melengkapi prompt dan jangan membuat issue atau code.
4. Kirim konfirmasi menggunakan format baku berikut. Isi secara konkret dari
   prompt dan temuan source; jangan sekadar menyalin kalimat developer.

```text
KONFIRMASI REQUEST

Jenis request:
- Feature baru | Perubahan feature | Bug

Area terdampak:
- Repository/aplikasi:
- App/subdomain:
- Module/halaman/flow:
- Konsumen terkait: Web/API/Android/iOS/lainnya

Pemahaman saya:
- [Uraikan kebutuhan atau masalah dengan bahasa bisnis yang jelas.]
- [Uraikan perilaku existing bila sudah terbukti dari source.]
- [Uraikan perilaku yang diharapkan setelah pekerjaan selesai.]

Scope yang akan dicakup:
- [...]

Di luar scope:
- [...]

Keputusan/asumsi yang perlu dikonfirmasi:
- Tidak ada | [...]

Hasil akhir yang diharapkan:
- [...]

Jika pemahaman ini sudah benar, balas "OK". Setelah itu saya akan menyusun
spesifikasi teknis detail di folder issues/ untuk direview sebelum coding.
```

5. Bila ada informasi yang menentukan business flow, authorization, data,
   integrasi, atau acceptance criteria tetapi belum jelas, cantumkan sebagai
   pertanyaan/keputusan. Jangan menawarkan `OK` seolah-olah spesifikasi sudah
   cukup bila keputusan material masih kosong.
6. Tunggu jawaban developer. Jangan membuat planning `.md` hanya karena prompt
   terdengar lengkap.
7. Jika developer mengoreksi atau menambah scope material, kirim ulang
   konfirmasi yang sudah direvisi dan tunggu `OK` baru.

Format boleh dipadatkan untuk request kecil, tetapi seluruh label yang relevan
tetap harus terlihat agar klasifikasi, area, pemahaman, scope, dan keputusan
mudah diaudit oleh developer maupun model AI lain.

## 1. Gerbang spesifikasi teknis

Hanya setelah developer menyatakan konfirmasi chat benar:

1. Lanjutkan discovery berbasis source existing dan baca rule pemilik yang
   relevan.
2. Buat tepat satu file pada root Laravel host:
   - feature/perubahan feature:
     `issues/feature_<nama_snake_case>_<YYYY_MM_DD_HHMMSS>.md`;
   - bug: `issues/bug_<nama_snake_case>_<YYYY_MM_DD_HHMMSS>.md`.
3. Gunakan nama yang menjelaskan outcome, bukan nama umum seperti `revisi`,
   `update`, atau `fix`. Timestamp memakai waktu lokal project/developer pada
   saat file dibuat dan menjadi serial unik seperti migration. Jangan menimpa
   file issue existing; bila nama bertabrakan, gunakan timestamp baru.
4. Spesifikasi harus sangat detail, implementable, dan junior-friendly. Tuliskan
   section berikut sesuai relevansi; untuk section wajib yang tidak relevan,
   tulis `Tidak relevan` beserta alasan singkat:
   - metadata: judul, jenis request, status `Menunggu review developer`, tanggal,
     repository/aplikasi, App/module, dan file issue;
   - ringkasan bisnis dan tujuan;
   - konfirmasi requirement yang telah disetujui;
   - temuan existing berbasis bukti beserta path/simbol terkait;
   - scope masuk dan scope keluar;
   - actor, authorization, serta matrix hak akses;
   - flow utama langkah demi langkah dan flow alternatif/kegagalan;
   - rancangan arsitektur dan ownership setiap layer;
   - daftar file yang dibuat/diubah beserta tanggung jawab perubahan;
   - rancangan data, relation, constraint, index, migration, backfill, dan
     compatibility data existing;
   - kontrak API/request/response/error/idempotency bila relevan;
   - validation, security, transaksi, concurrency, dan audit log;
   - rancangan UI/state/loading/empty/error/responsive/accessibility bila
     relevan;
   - performa, pagination, cache, batas resource, dan query budget;
   - dampak lintas aplikasi/integrasi serta urutan deployment;
   - langkah implementasi berurutan yang dapat diikuti programmer junior;
   - skenario test terperinci per layer;
   - acceptance criteria yang objektif dan dapat diuji;
   - perintah verifikasi, checklist manual, rollout, rollback, risiko, dan
     keputusan terbuka.
5. Jangan menulis pseudo-detail. Nama tabel, kolom, route, class, endpoint,
   event, atau file hanya boleh dinyatakan sebagai kondisi existing bila sudah
   ditemukan; tandai proposal secara eksplisit.
6. Referensikan rule starterkit yang berlaku tanpa menyalin seluruh isinya.
7. Jangan membuat migration, model, service, repository, Livewire, route, view,
   atau code implementasi lain pada tahap ini.
8. Setelah file dibuat, beri tahu developer bahwa file siap direview dan
   implementasi dapat diteruskan oleh programmer junior atau model yang lebih
   hemat karena kontrak teknisnya sudah dikunci.
9. Tunggu persetujuan eksplisit developer atas file tersebut.

Gerbang konfirmasi dan spesifikasi berlaku untuk setiap feature, module, CRUD,
workflow, halaman bisnis, perubahan perilaku, bugfix, maintenance perilaku, dan
security patch yang memerlukan perubahan code. Diagnosis baca-saja, status
report, konsultasi, serta dokumentasi murni tidak membuat issue otomatis.

## 2. Gerbang implementasi dan arsip

1. Implementasi hanya dimulai setelah developer menyetujui file issue atau
   secara eksplisit memerintahkan eksekusi file tersebut.
2. Pelaksana wajib membaca file secara lengkap, lalu mengikuti scope, langkah,
   test, dan acceptance criteria di dalamnya. Jangan mengandalkan konteks chat
   yang mungkin tidak tersedia bagi programmer junior/model lain.
3. Bila discovery implementasi menemukan konflik atau keputusan material baru,
   hentikan bagian terkait, laporkan ke developer, perbarui konfirmasi dan file
   setelah disetujui, lalu lanjutkan.
4. Jangan memperluas scope diam-diam. Temuan di luar scope dibuat sebagai
   request terpisah dan kembali melalui gerbang konfirmasi.
5. Setelah seluruh acceptance criteria terpenuhi dan verifikasi relevan lulus,
   pindahkan—bukan salin—file:
   `issues/<nama-file>.md` menjadi
   `issues/archives/done_<nama-file>.md`.
6. Prefix `done_` hanya menandakan implementasi tuntas. Jangan memindahkan file
   bila pekerjaan parsial, test gagal, keputusan masih terbuka, atau developer
   membatalkan pekerjaan.
7. Commit implementasi menyertakan perpindahan file issue agar code dan kontrak
   pengerjaan memiliki jejak audit yang sama.

### Requirement module baru

Developer wajib menyebutkan secara eksplisit:

- App/subdomain pemilik module;
- nama atau code module;
- struktur navigasi: menu single atau menu parent dengan child;
- label menu single, atau label parent dan seluruh label child secara berurutan;
- menu/child yang menjadi halaman awal.

Jika module juga membutuhkan API, developer wajib menyebut konsumen API
(mobile, server, atau browser), metode authentication, role/authorization,
endpoint dan operasi yang dibutuhkan, serta apakah browser lintas origin perlu
CORS. Informasi ini tidak boleh ditebak karena menentukan kontrak dan keamanan
API.

Jangan menebak struktur menu dari nama feature dan jangan langsung membuat
issue atau code jika informasi tersebut belum lengkap. Respons pertama harus
singkat, menjelaskan hubungan module sebagai batas akses dan menu sebagai
navigasi, menyebut data yang kurang, lalu memberikan pola prompt yang dapat
langsung dilengkapi developer.

Contoh menu single:

```text
Buat module laporan pada App keuangan.
Menu single: Laporan Keuangan.
Halaman awal: Laporan Keuangan.
[lanjutkan dengan kebutuhan bisnis, data, flow, dan role]
```

Contoh menu dengan child:

```text
Buat module transaksi pada App keuangan.
Parent menu: Transaksi.
Child: Daftar Transaksi, Tambah Transaksi.
Halaman awal: Daftar Transaksi.
[lanjutkan dengan kebutuhan bisnis, data, flow, dan role]
```

Jika developer hanya menulis “buat module transaksi”, respons yang benar adalah
mengarahkan format di atas dan meminta informasi yang kurang. Jangan memaksakan
module, route, menu, atau flow hasil asumsi.

## 3. Discovery berbasis bukti

- Telusuri entry point, route, menu/module, Livewire/controller, service, interface/repository, model, migration, config, dan test existing yang berhubungan.
- Periksa schema dan arti data existing sebelum merancang migration atau mengubah validation.
- Pisahkan requirement terkonfirmasi, kondisi existing, proposal, dan pertanyaan terbuka selama discovery.
- Jangan mengasumsikan status, role, approval, nomor dokumen, ownership data, integrasi, atau failure behavior yang tidak dinyatakan user dan tidak ditemukan di project.
- Tetapkan scope in/out, acceptance criteria, authorization, dampak data, audit log, performa, dan rollback sebelum implementasi.
- Gunakan hanya file issue teknis yang diwajibkan bagian 1. Jangan membuat
  planning duplikat; arsipkan file yang sama sesuai bagian 2 setelah selesai.

## 4. Tentukan ownership

- Pilih app/subdomain pemilik feature.
- Pilih module existing atau buat module baru.
- Buat module baru jika flow, UI, atau audiensnya berbeda secara nyata; jangan membuat conditional role yang besar dalam satu page.
- Tetapkan route landing module dan role yang harus mendapat akses.

## 5. Persistence dan business flow

- Buat model dan migration app dengan Artisan bila ada tabel baru. Migration wajib berada di `database/migrations/apps/<app-key>/`; jangan gunakan `make:model -m` karena path migration app harus eksplisit.
- Ikuti seluruh aturan migration production-safe pada `code-style.md`; gunakan expand → backfill → contract untuk perubahan constraint/tipe yang berisiko.
- Gunakan pola penamaan tabel bisnis pada `code-style.md`; prefix `starter_` hanya untuk infrastruktur starterkit.
- Letakkan validasi dan authorization pada boundary action.
- Terapkan pola default `architecture.md`: contract/interface untuk boundary persistence, repository untuk seluruh query/persistence domain, dan service untuk business logic/transaksi/orchestration.
- Service membuka `DB::transaction()` untuk mutation yang harus atomik; repository tidak menentukan batas transaksi lintas use case.
- Integrasikan audit log sebelum menulis UI; baca `audit-logging.md`.

## 6. Livewire dan view

- Class: `app/Livewire/Apps/<AppStudly>/<ModuleStudly>/`.
- View: `resources/views/apps/<app-key>/<module-key>/`.
- CSS/JS custom halaman berada di `assets/<page>.css.blade.php` dan/atau `assets/<page>.js.blade.php` di samping view pemilik. Jangan memuat asset halaman dari layout global; ikuti lifecycle `@assets`/`@script` pada `ui-ux.md`.
- Gunakan `#[Layout('layouts::app')]`.
- State tetap di server; Alpine/JavaScript hanya untuk interaksi client yang tidak cocok menjadi request Livewire.
- Form normal memakai `wire:model.defer` dan `wire:submit`; live request hanya untuk pengecualian yang diizinkan `ui-ux.md`.
- Tentukan jenis dan volume data sebelum menata UI, lalu cari `docs/template/template.md` berdasarkan konteks dan komponen untuk membandingkan beberapa kandidat template UI. Buka satu sampai tiga HTML sumber yang paling relevan, termasuk contoh lintas konteks bila membantu; gunakan tabel untuk data banyak yang perlu dibandingkan, dan card/list/detail untuk ringkasan atau data sedikit. Jangan mendesain dari nol.
- Gunakan komponen/markup template UI aktif dari referensi project; baca `ui-ux.md`.
- Terapkan query, pagination, cache, dan batas resource dari `performance.md` tanpa menunggu user menyebutkannya.

## 7. Route

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

Route API untuk module yang sama ditulis terpisah di
`routes/apps/<app-key>.api.php`. Tulis path relatif setelah prefix App dan nama
relatif setelah `api.<app-key>.`; jangan mengulang `/api` atau `<app-key>`:

```php
Route::prefix('pegawai')->name('pegawai.')->group(function (): void {
    Route::get('/', PegawaiIndexController::class)->name('index');
});
```

Contoh tersebut menjadi `api.example.com/hr/pegawai` dengan nama
`api.hr.pegawai.index`. Terapkan authentication, authorization, validation,
resource response, pagination, dan rate limit yang sesuai kontrak API.

## 8. Config module dan menu

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

## 9. Sync

```bash
php artisan starter:sync <app-key> --dry-run
php artisan starter:sync <app-key> --force
```

Dry run wajib diperiksa sebelum apply. Sync dapat menghapus metadata yang tidak lagi ada di config/route.

## 10. Verifikasi minimum

- Test business flow dan authorization.
- Test Livewire action/validation.
- Test route terdaftar dan route terlarang menghasilkan 403.
- Test audit log create/update/delete atau security event yang relevan.
- Test server-side pagination/query budget bila data feature dapat bertambah.
- Jalankan Pint dan test sesuai `testing.md`.
- Bila UI berubah, lakukan pengujian browser pada ukuran desktop dan mobile yang relevan.
- Uji empty state serta data sedikit dan banyak bila halaman menampilkan data; pastikan komponen yang dipilih tetap padat, informatif, dan mudah dipindai.
