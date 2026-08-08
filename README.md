> Starterkit ini dibangun dengan dukungan karya open-source dari
> [Laravel](https://laravel.com/),
> [Laravel Livewire](https://livewire.laravel.com/),
> [Laravel Lang](https://laravel-lang.com/),
> [Livewire PowerGrid](https://livewire-powergrid.com/), dan
> [Tabler](https://tabler.io/admin-template). Terima kasih kepada seluruh author
> dan contributor yang mengembangkan serta memelihara package tersebut.

# Apa itu Starterkit Larawire Private?

Starterkit Larawire Private adalah fondasi awal untuk membuat aplikasi
**Laravel** berbasis **Livewire**. Fungsinya mirip dengan Laravel Starter Kits
Auth bawaan Laravel yang menyediakan autentikasi siap pakai, tetapi starterkit
ini sudah diperluas untuk kebutuhan aplikasi internal anda.

Fitur yang sudah tersedia:

- autentikasi, login, logout, lock screen otomatis, dan pengaturan keamanan akun;
- pengelolaan user, role, module, dan hak akses;
- dukungan satu atau beberapa App/subdomain dengan satu login dan database;
- sinkronisasi App, module, route, dan menu dari konfigurasi code ke database;
- dashboard dan komponen UI berbasis Livewire, PowerGrid, serta tema Tabler;
- audit log aktivitas, konfigurasi aplikasi, dan gateway API;
- command instalasi, setup, sync, publish asset, dan pemeriksaan keamanan;
- rules pengembangan yang siap digunakan oleh agent AI.

Dengan fondasi tersebut, developer dapat langsung mengerjakan fitur bisnis
tanpa membangun ulang autentikasi, hak akses, navigasi, tampilan dasar, dan
infrastruktur aplikasi dari awal.

Repository ini bukan aplikasi mandiri. Pasang repository sebagai Git submodule
`starterkit-larawire-private` di dalam project Laravel. Repository Laravel melacak
commit starterkit melalui gitlink submodule sehingga core dapat diperbarui dan
dipush secara terpisah tanpa menyalin source manual.

## Cara kerja App, subdomain, module, route, menu, dan authorization

Starterkit tetap menjalankan **satu project Laravel, satu database, dan satu
login**. App hanya membagi sistem menjadi beberapa area bisnis. Setiap App dapat
memakai subdomain sendiri, misalnya `sales.perusahaan.com` untuk Sales dan
`hr.perusahaan.com` untuk HR. Jika sistem hanya membutuhkan satu App, mekanisme
yang sama tetap dapat digunakan tanpa membuat project Laravel lain.

Hubungan komponennya adalah:

| Komponen | Penjelasan sederhana |
|---|---|
| **App** | Area bisnis utama, misalnya Sales, HR, atau Keuangan. |
| **Subdomain** | Alamat App, misalnya `sales.perusahaan.com`. |
| **Module** | Kelompok fitur sekaligus batas hak akses, misalnya `prospect` atau `report`. |
| **Route** | Alamat halaman dan nama aksi yang benar-benar dapat dibuka. |
| **Menu** | Link navigasi menuju route; menu bukan pengaman akses. |
| **Role** | Kumpulan module yang boleh diakses oleh user. |

Strukturnya dapat dibayangkan seperti ini:

```text
Satu project Laravel
├── domain utama        → login, profil, user, role, pengaturan, dan log
├── App Sales           → sales.perusahaan.com
│   ├── Module Prospect → route dan menu Prospect
│   └── Module Report   → route dan menu Report
└── App HR              → hr.perusahaan.com
    └── Module Employee → route dan menu Employee
```

Pengelolaan akses dilakukan dari Role Management. Admin memilih module yang
dimiliki sebuah role dan landing page awal untuk setiap App, lalu role diberikan
kepada user. Satu role dapat memiliki beberapa module dari beberapa App. User
hanya dapat membuka route milik module tersebut, sedangkan Superuser dapat
mengakses seluruh module.

### Dua file yang menghubungkan halaman dan menu

Setiap App memiliki dua source of truth dengan nama App yang sama:

```text
config/apps/sales.php   → identitas App, module, menu, icon, dan landing page
routes/apps/sales.php   → URL halaman web dan nama route pada App Sales
```

File config menu berfungsi seperti konfigurasi JSON yang deklaratif, tetapi
format sebenarnya adalah **array PHP** agar dapat dibaca langsung oleh Laravel.
Contoh sederhananya:

```php
return [
    'name' => 'Sales',
    'mods' => [
        'prospect' => [
            'name' => 'Prospek',
            'menus' => [
                [
                    'label' => 'Daftar Prospek',
                    'route' => 'sales.prospect.index',
                    'landing' => true,
                ],
            ],
        ],
    ],
];
```

Route tujuan tersebut dibuat pada file route App:

```php
Route::name('sales.')->group(function () {
    Route::middleware([
        'auth:web',
        'starter.active',
        'starter.password-change',
        'starter.lock',
        'starter.authorize',
    ])->group(function () {
        Route::livewire('/prospects', ProspectIndex::class)
            ->name('prospect.index');
    });
});
```

Hasil akhirnya adalah route bernama `sales.prospect.index` pada subdomain
`sales.perusahaan.com`. Nama route selalu mengikuti pola:

```text
<app>.<module>.<action>
sales.prospect.index
```

Bagian kedua dari nama route, yaitu `prospect`, harus sama dengan kode module
di `config/apps/sales.php`. Saat halaman dibuka, middleware memastikan user
sudah login dan aktif, lalu `starter.authorize` memeriksa apakah role user
memiliki module `prospect`. Menyembunyikan menu saja tidak memberi keamanan;
route dan setiap aksi perubahan data tetap harus dilindungi di server.

Setelah config atau route diubah, jalankan `php artisan starter:sync`. Command
ini memvalidasi bahwa route menu benar-benar tersedia dan dimiliki module yang
sesuai, lalu menyinkronkan metadata App, module, route, dan menu ke database.
Karena itu, perubahan dilakukan pada file config dan route—bukan langsung pada
tabel metadata database.

## Instalasi di Local / Development

### Persyaratan sistem

- **PHP 8.2+**
- **Laravel 11.x**
- **Database** (MySQL / PostgreSQL / SQLite)

Pastikan terminal aktif di root project Laravel, lalu lakukan tiga langkah.

1. Pasang starterkit sebagai Git submodule:

```bash
git submodule add https://github.com/altekno-id/starterkit-larawire-private.git starterkit-larawire-private
```

2. Sesuaikan local domain pada `.env`; variabel starterkit lainnya ditambahkan
   otomatis oleh installer:

```env
APP_URL=http://namaproject.test
```

3. Jalankan installer:

```bash
php starterkit-larawire-private/installer/install.php --company="Nama Perusahaan"
```

Installer mendeteksi kondisi Laravel secara otomatis:

- **Laravel fresh:** hanya file bawaan Laravel yang ditemukan—termasuk
  `resources/views/welcome.blade.php`, `app/Http/Controllers/Controller.php`,
  `app/Models/User.php`, dan migration bawaan. Installer hanya menampilkan
  informasi bahwa `migrate:fresh` akan menghapus seluruh tabel dan data
  database, kemudian langsung melanjutkan tanpa meminta konfirmasi reset.
- **Laravel tidak fresh yang belum terpasang starterkit:** installer menampilkan
  file atau konfigurasi aplikasi yang terdeteksi, lalu meminta konfirmasi sebelum
  menjalankan `migrate:fresh`. Jika konfirmasi ditolak, installer berhenti
  sebelum mengubah source atau database.

Pada kedua kondisi, instalasi normal menjalankan `migrate:fresh`; seluruh tabel
dan data pada database di `.env` akan dihapus. Controller, model, view, route,
dan migration aplikasi yang sudah ada tetap dipertahankan. Installer selanjutnya
meminta kode App pertama—boleh dikosongkan—beserta nama tampilannya.

Dependency, connector Laravel, connector AI `AGENTS.md`, environment, APP key,
locale, asset, migration, Superuser, landing, App pertama, sync, dan security
check disiapkan otomatis. Tidak ada file yang perlu disalin atau disambungkan
manual.

## Mengganti ke database baru/kosong

Jika starterkit sudah terpasang pada project Laravel yang sama, lalu koneksi
`.env` diganti ke database baru atau kosong, jangan menjalankan `install.php`
ulang dan jangan memakai opsi `--reset`. Jalankan dari root Laravel:

```bash
php artisan starter:setup --company="Nama Perusahaan"
```

`starter:setup` menjalankan migration, membuat client dan akun Superuser, lalu
menjalankan `starter:sync` secara otomatis. Gunakan `starter:sync` tetap untuk
update migration, asset, dan registry App pada database yang sudah disiapkan;
command tersebut tidak membuat ulang akun Superuser.

## Pull update starterkit

Jika repository starterkit memiliki update, pastikan terminal aktif di root
project Laravel, lalu jalankan:

```bash
git submodule update --init --remote starterkit-larawire-private
composer install
php artisan starter:sync
```

Update ini tidak menjalankan reset database.

## Agentic-ready — tujuan rules

Starterkit ini terutama dirancang untuk pengembangan dengan agent AI, bukan
untuk menyalin pola code secara manual satu per satu. Developer cukup memberi
konteks fitur, flow bisnis, data, role, App, module, serta menu. Rules bawaan
menjadi kontrak teknis agar agent otomatis mengikuti keamanan, validasi,
authorization, audit, migration production-safe, pagination server-side,
performa query, pola Livewire/Alpine, UI, struktur folder, dan testing.

Saat instalasi, connector `AGENTS.md` otomatis dibuat di root Laravel. Connector
tersebut mengarahkan agent ke `starterkit-larawire-private/AGENTS.md` dan rule
terkait tanpa menduplikasi seluruh isi. Karena source of truth tetap berada di
submodule starterkit, rules terbaru tersedia setelah commit submodule diperbarui.

Alur pengembangan feature aplikasi Laravel turunan:

1. Developer menjelaskan kebutuhan bisnis/bug serta App, module, dan area yang
   relevan.
2. Agent memeriksa source secara baca-saja, lalu mengirim konfirmasi pemahaman
   terstruktur di chat. Belum ada file planning atau perubahan code.
3. Setelah developer menjawab `OK`, agent membuat satu spesifikasi teknis sangat
   detail dan junior-friendly:
   `issues/feature_<nama>_<YYYY_MM_DD_HHMMSS>.md` atau
   `issues/bug_<nama>_<YYYY_MM_DD_HHMMSS>.md`.
4. Agent berhenti agar developer dapat mereview file. Setelah disetujui,
   implementasi dapat diteruskan—termasuk oleh programmer junior atau model yang
   lebih hemat—karena keputusan dan standar teknis sudah terkunci.
5. Setelah implementasi dan verifikasi selesai, file dipindahkan menjadi
   `issues/archives/done_<nama-file-asli>.md`.

Contoh prompt yang cukup:

```text
Buat modul Penjualan pada App sales. Menu induk Penjualan memiliki submenu
Pipeline, Penawaran, dan Transaksi. Role Sales dapat mengelola datanya,
sedangkan Manager hanya melihat dan menyetujui penawaran.
```

Jika App, module, atau struktur menu belum disebut, agent wajib meminta
kelengkapannya sambil memberi contoh prompt yang benar. Gerbang konfirmasi dan
file issue berlaku untuk feature/perubahan feature/bug yang membutuhkan code;
diagnosis baca-saja, status report, konsultasi, dan dokumentasi murni tidak
membuat issue otomatis.

Prosedur confirmation dan file `issues/*.md` tersebut tidak berlaku untuk
maintenance repository canonical starterkit. Perubahan core yang diminta
developer langsung dieksekusi dan diverifikasi mengikuti
[`core-maintenance.md`](docs/rules/core-maintenance.md).

## Theme UI — Tabler

Theme default starterkit ini adalah **Tabler**. Logic private Livewire tetap
universal dan tidak dimiliki theme. Tabler
menyediakan pola layout admin responsif, sidebar, navbar, card, table, form,
badge, alert, dropdown, modal, pagination, empty state, halaman autentikasi,
dan komponen visual lain untuk membangun UI yang konsisten dan profesional.

- Layout, halaman starter, dan error page sudah terintegrasi dengan Tabler.
- Interaksi server memakai Livewire; interaksi ringan di browser memakai
  Alpine.js tanpa mengubah pola navigasi yang sudah berjalan.
- CSS, JavaScript, icon, dan asset inti Tabler disimpan lokal sehingga tidak
  bergantung pada CDN, Vite development server, atau konfigurasi production
  tambahan.
- [`docs/template/tabler/template.md`](docs/template/tabler/template.md) adalah atlas komponen
  untuk membantu agent AI menemukan contoh Tabler yang tepat tanpa membaca
  seluruh file HTML.
- Source contoh di [`docs/template/tabler`](docs/template/tabler) menjadi acuan sebelum
  memilih atau menyusun komponen baru. Komponen custom tetap mengikuti bahasa
  visual Tabler.

Tabler adalah lapisan presentasi starterkit ini. Laravel, Livewire, aturan
keamanan, performa, dan pemisahan App tetap menjadi fondasi arsitekturnya.

Theme aktif ditentukan saat instalasi:

```env
STARTER_THEME=tabler
```

Folder `docs/template/<theme>/` menyimpan bundle vendor lengkap—asset, HTML,
dan atlas UI—agar template baru dapat langsung di-copy-paste ke satu lokasi.
Blade runtime hasil adaptasi tetap berada di `resources/themes/<theme>/views`,
sedangkan asset runtime yang dipublish ke `public/assets` bersumber dari
`public/themes/<theme>/assets`. Untuk theme baru, paste vendor lengkap ke docs,
adaptasikan Blade/runtime asset, lalu daftarkan path dan adapter PowerGrid pada
`config/starter.php`.

## Standar tabel — Livewire PowerGrid

Semua tabel Livewire memakai `power-components/livewire-powergrid`. Search,
filter setiap kolom yang relevan, sorting, dan pagination berjalan di database.
Tabel manajemen mutable menyediakan checkbox, aksi individual/massal/by-filter,
arsip, pulihkan, dan hapus permanen. Log, pivot, metadata sistem, data turunan,
dan append-only mengikuti pengecualian yang dibuktikan pada flow dan test.

PowerGrid memakai adapter theme aktif. Theme Tabler menggunakan adapter
Bootstrap 5 yang disesuaikan dengan card, table, filter, pagination, checkbox,
empty state, dan modal Tabler; source vendor tidak diubah.

## Extension UI project

Blade core starterkit bersifat read-only bagi feature project. Tambahkan elemen
UI global melalui extension pada Laravel host agar update starterkit tidak
menimpa code project:

| Lokasi pada Laravel host | Area yang disediakan |
|---|---|
| `resources/views/extensions/starter/header-actions/index.blade.php` | Aksi top bar desktop dan mobile; variable `$compact` selalu tersedia |
| `resources/views/extensions/starter/profile-menu/index.blade.php` | Menu tambahan pada dropdown profil, tepat sebelum Logout |
| `resources/views/extensions/starter/layout/head.blade.php` | Tambahan global di `<head>` |
| `resources/views/extensions/starter/layout/body-end.blade.php` | Tambahan global sebelum penutup `<body>` |

Contoh menambah menu pada dropdown profil:

```blade
<a href="{{ route('notifications.index') }}"
   class="dropdown-item">
    @include('starter.templates.layouts.icon', [
        'name' => 'bell',
        'class' => 'icon dropdown-item-icon',
    ])
    Notifikasi Saya
</a>
```

Contoh aksi pada top bar yang tetap ringkas di mobile:

```blade
<a href="{{ route('notifications.index') }}"
   class="nav-link px-2"
   aria-label="Notifikasi">
    @include('starter.templates.layouts.icon', ['name' => 'bell'])
    @if (! $compact)
        <span class="ms-1">Notifikasi</span>
    @endif
</a>
```

Buat file extension hanya ketika diperlukan; starterkit mendeteksinya otomatis
tanpa registrasi atau config tambahan. Route dan action tetap wajib memiliki
authorization server-side; sembunyikan extension bila user tidak berhak
melihatnya. Gunakan navigasi browser penuh untuk link lintas subdomain; atribut
navigasi Livewire hanya boleh ditambahkan bila URL dijamin tetap pada origin
yang sama. Navigasi App/module pada sidebar tidak menggunakan raw Blade
extension—daftarkan melalui `config/apps/<app>.php` lalu jalankan `starter:sync`.

## Command utama

Jalankan dari root Laravel.

| Kebutuhan | Command |
|---|---|
| Instalasi awal | `php starterkit-larawire-private/installer/install.php --company="Nama Perusahaan"` |
| Menyiapkan database baru/kosong setelah starterkit terpasang | `php artisan starter:setup --company="Nama Perusahaan"` |
| Membuat App | `php artisan starter:make-app sales --name="Sales"` |
| Membuat App tanpa langsung sync | `php artisan starter:make-app sales --name="Sales" --no-sync` |
| Memeriksa perubahan registry | `php artisan starter:sync sales --dry-run` |
| Menerapkan update App | `php artisan starter:sync sales` |
| Menerapkan seluruh update | `php artisan starter:sync` |
| Publish ulang asset | `php artisan starter:publish-assets` |
| Cek keamanan lokal | `php artisan starter:security-check` |
| Simulasi cek production | `php artisan starter:security-check --production` |
| Deployment pertama | `php artisan starter:setup --company="Nama Perusahaan"` |

`starterkit:install` adalah command internal yang dipanggil installer. Jangan
menjalankannya langsung untuk update atau deployment.

Yang berjalan otomatis:

- `starter:make-app` langsung menjalankan sync, kecuali memakai `--no-sync`;
- `php artisan migrate` memuat migration core dan seluruh folder
  `database/migrations/apps/<app>/`;
- Composer `post-autoload-dump` membersihkan cache bootstrap dan menjalankan
  `starter:publish-assets`;
- `starter:setup` menyiapkan APP key bila kosong, security check, migration,
  client/Superuser, seluruh registry App, asset, storage link bila didukung,
  asset Livewire, dan cache production;
- `starter:sync` menangani security check, migration baru, asset, registry App,
  storage link bila didukung, asset Livewire, dan cache production dalam satu
  kali jalan tanpa membuat ulang akun atau mereset password. Cache bootstrap
  lama dibersihkan lalu cache production dibangun ulang pada tahap akhir; asset
  starter hanya disalin ulang saat isinya berubah.

## Struktur yang dibuat untuk setiap App

Command `starter:make-app` membuat:

```text
app/Livewire/Apps/Sales/
config/apps/sales.php
routes/apps/sales.php
routes/apps/sales.api.php
resources/views/apps/sales/
tests/Feature/Apps/Sales/
```

Migration bisnis App diletakkan di:

```text
database/migrations/apps/sales/
```

- `config/apps/sales.php`: definisi App, module, menu, dan landing.
- `routes/apps/sales.php`: route web `sales.domainxx.com`.
- `routes/apps/sales.api.php`: endpoint `api.domainxx.com/sales`.
- `starter:sync`: memproyeksikan config dan route web ke metadata database.

Kode `api` tidak dapat dipakai sebagai nama App karena dicadangkan untuk gateway
API.

## Gateway API

API nonaktif secara default:

```env
STARTER_API_ENABLED=false
```

Aktifkan bila diperlukan:

```env
STARTER_API_ENABLED=true
```

Hasilnya:

```text
api.domainxx.com                 Dokumentasi Scramble
api.domainxx.com/openapi.json    Dokumen OpenAPI
api.domainxx.com/sales           Endpoint App Sales
api.domainxx.com/customer        Endpoint App Customer
api.domainxx.com/marketing       Endpoint App Marketing
api.domainxx.com/support         Endpoint App Customer Support
```

Tidak ada prefix `/api`. Development dapat membuka dokumentasi langsung;
production hanya mengizinkan Superuser yang sudah login. DNS/subdomain `api`
tetap harus diarahkan melalui panel hosting ke folder `public` yang sama.

## Login awal

Installer menambahkan credential development ke `.env` dan `.env.example`:

```env
STARTER_SUPERUSER_USERNAME=superuser
STARTER_SUPERUSER_EMAIL=developer@example.test
STARTER_SUPERUSER_PASSWORD=superuser123
```

Password tersebut hanya untuk local/development/testing. Production wajib
memakai password kuat; `starter:security-check --production` akan menolak
password default.

## Instalasi tanpa App pertama

Input App pada installer boleh dikosongkan. Project tetap memiliki landing,
login, profil, pengaturan, user, role, dan log. Landing akan menampilkan panduan
membuat App pertama:

```bash
php artisan starter:make-app layanan --name="Layanan" --no-sync
php artisan starter:sync layanan --dry-run
php artisan starter:sync layanan --force
```

## Perubahan core starterkit

Repository starterkit adalah satu-satunya source of truth untuk perubahan core.
Perubahan universal dapat dikerjakan langsung dari submodule pada Laravel host.
Pastikan submodule berada di branch `master`, verifikasi melalui Laravel host,
commit dan push repository starterkit, lalu commit gitlink baru pada repository
Laravel.

```bash
git -C starterkit-larawire-private checkout master
git -C starterkit-larawire-private pull --ff-only origin master
# ubah dan verifikasi core dari Laravel host
git -C starterkit-larawire-private add .
git -C starterkit-larawire-private commit -m "fix: describe starterkit change"
git -C starterkit-larawire-private push origin master
git add starterkit-larawire-private
git commit -m "chore: update starterkit submodule"
git push origin master
```

## Instalasi di Production / Shared Hosting

Clone pertama repository Laravel di production dengan submodule:

```bash
git clone --recurse-submodules <repository-laravel> <folder-project>
cd <folder-project>
cp .env.example .env
# isi konfigurasi production pada .env
composer install --no-dev --optimize-autoloader
php artisan starter:setup --company="Nama Perusahaan"
```

Deployment berikutnya:

```bash
git pull --ff-only origin master
git submodule sync --recursive
git submodule update --init --recursive
composer install --no-dev --optimize-autoloader
php artisan starter:sync
```

Jika pull mengubah `composer.lock`, jalankan terlebih dahulu:

```bash
composer install --no-dev --optimize-autoloader
```

Production wajib memakai HTTPS, `APP_DEBUG=false`, password Superuser kuat, dan
document root menuju `public`. Root domain dan seluruh subdomain App/API
diarahkan ke folder `public` yang sama.

Jika PHP shared hosting memblokir pembuatan symlink, setup/sync tetap selesai
dan menampilkan perintah shell yang perlu dijalankan satu kali:

```bash
ln -s ../storage/app/public public/storage
```

Config cache/`optimize` hanya untuk production. Selama development gunakan
config langsung dan jalankan `php artisan optimize:clear` jika cache pernah
dibuat.

## Batas penting

- Satu instalasi mewakili satu perusahaan/client, bukan SaaS multi-tenant.
- Single App dan multi App sama-sama didukung.
- Seluruh App berbagi login, database, dan pengaturan global.
- Hak akses diberikan melalui module; menu hanya navigasi.
- Sidebar berasal dari registry App/module/menu.
- Landing dan fitur bisnis berada di Laravel host.
- Extension UI berada di `resources/views/extensions/starter/`.
- `AGENTS.md` root Laravel adalah connector AI; rules canonical berada di
  folder `starterkit-larawire-private`.
- Folder `starterkit-larawire-private` adalah Git submodule dan core read-only
  untuk feature project. Improvement universal di-commit dan dipush ke
  repository starterkit, lalu gitlink-nya di-commit pada repository Laravel.
- Installer mendeteksi Laravel fresh/non-fresh yang belum terpasang starterkit;
  host non-fresh wajib mengonfirmasi penghapusan seluruh data database sebelum
  instalasi dilanjutkan.
- Setelah starterkit terpasang, gunakan `starter:setup` untuk database baru atau
  kosong dan `starter:sync` untuk update rutin pada database yang sudah siap.
- Perubahan core starterkit wajib commit dan push pada repository starterkit
  sebelum gitlink baru diuji, di-commit, dan dipush pada project pengguna.
