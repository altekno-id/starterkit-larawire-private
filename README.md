# Laravel Private Starterkit

Starterkit Laravel untuk aplikasi internal perusahaan. Fondasi login, user,
role, App, module, menu, audit, keamanan, API, serta UI berbasis **Tabler**
sudah disiapkan agar developer fokus pada fitur bisnis dan agentic coding.

Repository ini bukan aplikasi mandiri. Clone ke folder `starterkit` di dalam
Laravel fresh, lalu jalankan installer.

## Agentic-ready — tujuan rules

Starterkit ini terutama dirancang untuk pengembangan dengan agent AI, bukan
untuk menyalin pola code secara manual satu per satu. Developer cukup memberi
konteks fitur, flow bisnis, data, role, App, module, serta menu. Rules bawaan
menjadi kontrak teknis agar agent otomatis mengikuti keamanan, validasi,
authorization, audit, migration production-safe, pagination server-side,
performa query, pola Livewire/Alpine, UI, struktur folder, dan testing.

Saat instalasi, connector `AGENTS.md` otomatis dibuat di root Laravel. Connector
tersebut mengarahkan agent ke [`starterkit/AGENTS.md`](AGENTS.md) dan rule
terkait tanpa menduplikasi seluruh isi. Karena source of truth tetap berada di
folder starterkit, rules terbaru langsung tersedia setelah `git pull`.

Alur pengembangan feature:

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

## Template UI — Tabler

Varian starterkit ini menggunakan **Tabler** sebagai fondasi tampilan. Tabler
menyediakan pola layout admin responsif, sidebar, navbar, card, table, form,
badge, alert, dropdown, modal, pagination, empty state, halaman autentikasi,
dan komponen visual lain untuk membangun UI yang konsisten dan profesional.

- Layout, halaman starter, dan error page sudah terintegrasi dengan Tabler.
- Interaksi server memakai Livewire; interaksi ringan di browser memakai
  Alpine.js tanpa mengubah pola navigasi yang sudah berjalan.
- CSS, JavaScript, icon, dan asset inti Tabler disimpan lokal sehingga tidak
  bergantung pada CDN, Vite development server, atau konfigurasi production
  tambahan.
- [`docs/template/template.md`](docs/template/template.md) adalah atlas komponen
  untuk membantu agent AI menemukan contoh Tabler yang tepat tanpa membaca
  seluruh file HTML.
- Source contoh di [`docs/template`](docs/template) menjadi acuan sebelum
  memilih atau menyusun komponen baru. Komponen custom tetap mengikuti bahasa
  visual Tabler.

Tabler adalah lapisan presentasi starterkit ini. Laravel, Livewire, aturan
keamanan, performa, dan pemisahan App tetap menjadi fondasi arsitekturnya.

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

## Instalasi — mulai dari sini

> Wajib memakai Laravel fresh dan database baru. Installer menjalankan
> `migrate:fresh`, sehingga seluruh tabel dan data pada database akan dihapus.

Dari root Laravel:

```bash
git subtree add --prefix=starterkit \
  https://github.com/altekno-id/starterkit-larawire-private-tabler.git master --squash
php starterkit/installer/install.php --company="Nama Aplikasi"
```

Installer meminta:

1. konfirmasi reset database;
2. kode App pertama, misalnya `sales`—boleh dikosongkan;
3. nama App yang ditampilkan.

Setelah itu dependency, connector Laravel, connector AI `AGENTS.md`, `.env`,
APP key, locale, asset, migration, Superuser, landing, App pertama, sync, dan
security check disiapkan otomatis. Tidak ada file yang perlu disalin atau
disambungkan manual.

## Command utama

Jalankan dari root Laravel.

| Kebutuhan | Command |
|---|---|
| Instalasi awal | `php starterkit/installer/install.php --company="Nama Aplikasi"` |
| Reset database development | `php starterkit/installer/install.php --reset --company="Nama Aplikasi"` |
| Membuat App | `php artisan starter:make-app sales --name="Sales"` |
| Membuat App tanpa langsung sync | `php artisan starter:make-app sales --name="Sales" --no-sync` |
| Memeriksa perubahan registry | `php artisan starter:sync sales --dry-run` |
| Menerapkan update App | `php artisan starter:sync sales` |
| Menerapkan seluruh update | `php artisan starter:sync` |
| Publish ulang asset | `php artisan starter:publish-assets` |
| Cek keamanan lokal | `php artisan starter:security-check` |
| Simulasi cek production | `php artisan starter:security-check --production` |
| Deployment pertama | `php artisan starter:setup --company="Nama Aplikasi"` |

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
  storage link bila didukung, asset Livewire, dan cache production tanpa
  membuat ulang akun atau mereset password.

## Multi-domain itu seperti apa?

Contoh berikut adalah satu sistem CRM yang dipecah menjadi beberapa App sesuai
area bisnis. Setiap App memakai subdomain sendiri, tetapi semuanya tetap berada
dalam satu project Laravel, satu database, satu login, dan satu pengaturan
global.

```text
Sistem CRM
│
├── domainxx.com                             Area global
│   └── Landing, login, profil, user, role, pengaturan, dan log
│
├── sales.domainxx.com                       App Sales
│   ├── Modul Prospek
│   │   ├── Menu: Daftar Prospek
│   │   └── Menu: Sumber Prospek
│   └── Modul Penjualan
│       ├── Menu: Pipeline
│       ├── Menu: Penawaran
│       └── Menu: Transaksi
│
├── customer.domainxx.com                    App Customer
│   ├── Modul Pelanggan
│   │   ├── Menu: Data Pelanggan
│   │   └── Menu: Kontak
│   └── Modul Aktivitas
│       ├── Menu: Riwayat Interaksi
│       └── Menu: Catatan
│
├── marketing.domainxx.com                   App Marketing
│   ├── Modul Campaign
│   │   ├── Menu: Daftar Campaign
│   │   └── Menu: Segmentasi
│   └── Modul Analitik
│       └── Menu: Performa Campaign
│
├── support.domainxx.com                     App Customer Support
│   ├── Modul Tiket
│   │   ├── Menu: Daftar Tiket
│   │   └── Menu: Eskalasi
│   └── Modul Layanan
│       ├── Menu: SLA
│       └── Menu: Knowledge Base
│
└── api.domainxx.com                         Gateway API opsional
    ├── /sales
    ├── /customer
    ├── /marketing
    └── /support
```

Ringkasnya:

```text
Sistem CRM = satu project dan satu ekosistem bisnis
App = area bisnis besar yang memakai subdomain
Module (Modul) = kelompok fitur sekaligus batas akses role
Menu/Submenu = navigasi halaman milik modul
```

Setiap App mempunyai module, menu, route, tampilan, test, dan code sendiri.
User dapat mengakses satu atau beberapa App sesuai module yang diberikan kepada
role-nya.

Starterkit juga dapat dipakai hanya dengan satu App. Istilah “multi-domain” di
dokumentasi ini berarti multi-subdomain dalam satu root domain, bukan beberapa
project atau database yang terpisah.

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

## Reset database development

```bash
php starterkit/installer/install.php --reset --company="Nama Aplikasi"
```

Opsi `--reset` hanya dapat berjalan pada `APP_ENV=local|development` setelah
konfirmasi `y` dan `RESET`. Installer menjalankan `migrate:fresh`, setup data
inti, dan sync ulang dari source yang tetap ada.

Reset menghapus seluruh tabel/data database development, tetapi tidak pernah
menghapus source App, landing, migration, route, view, test, asset, upload,
atau issue feature. Production ditolak sebelum mutation. Backup data yang masih
dibutuhkan sebelum menjalankannya.

## Perubahan core starterkit

Repository starterkit adalah satu-satunya source of truth untuk perubahan core.
Setiap perbaikan universal wajib dikerjakan pada repository canonical starterkit
yang memiliki remote `origin`, diverifikasi melalui Laravel host, lalu dibuat
sebagai commit terfokus dan dipush sebelum disinkronkan ke project pengguna.

Jangan menyelesaikan perubahan core hanya di folder embedded `starterkit/`
milik project. Setelah commit upstream tersedia, update folder starterkit pada
project pengguna dari commit tersebut, jalankan verifikasi host, lalu commit dan
push perubahan integrasinya pada repository project.

## Update starterkit

Update tidak me-reset database:

```bash
git subtree pull --prefix=starterkit \
  https://github.com/altekno-id/starterkit-larawire-private-tabler.git master --squash
composer install --no-dev --optimize-autoloader
php artisan starter:sync
php artisan test --compact
git push origin master
```

Gunakan `php artisan starter:sync --dry-run` terlebih dahulu bila ingin
memeriksa perubahan registry tanpa migration, publish asset, atau mutation.

## Deployment shared hosting

Deployment pertama setelah source dan `.env` production tersedia:

```bash
composer install --no-dev --optimize-autoloader
php artisan starter:setup --company="Nama Perusahaan"
```

Deployment berikutnya:

```bash
git pull --ff-only origin master
composer install --no-dev --optimize-autoloader
php artisan starter:sync
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
  folder `starterkit`.
- Folder `starterkit` adalah core read-only untuk feature project; improvement
  universal dilakukan melalui repository starterkit.
- Installer normal hanya untuk Laravel fresh dan database baru.
- Installer menyediakan reset database khusus local/development, tetapi tidak
  boleh menghapus source atau upload project.
- Perubahan core starterkit wajib commit dan push pada repository starterkit
  sebelum disinkronkan, diuji, di-commit, dan dipush pada project pengguna.
