# Laravel Private Starterkit

Starterkit Laravel untuk aplikasi internal perusahaan. Fondasi login, user,
role, App, module, menu, audit, keamanan, API, UI, dan aturan agentic coding
sudah disiapkan agar developer fokus pada fitur bisnis.

Repository ini bukan aplikasi mandiri. Clone ke folder `starterkit` di dalam
Laravel fresh, lalu jalankan installer.

## Instalasi — mulai dari sini

> Wajib memakai Laravel fresh dan database baru. Installer menjalankan
> `migrate:fresh`, sehingga seluruh tabel dan data pada database akan dihapus.

Dari root Laravel:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
php starterkit/installer/install.php --company="Nama Aplikasi"
```

Installer meminta:

1. konfirmasi reset database;
2. kode App pertama, misalnya `crm`—boleh dikosongkan;
3. nama App yang ditampilkan.

Setelah itu dependency, connector Laravel, `.env`, APP key, locale, asset,
migration, Superuser, landing, App pertama, sync, dan security check disiapkan
otomatis. Tidak ada file yang perlu disalin atau disambungkan manual.

## Command utama

Jalankan dari root Laravel.

| Kebutuhan | Command |
|---|---|
| Instalasi awal | `php starterkit/installer/install.php --company="Nama Aplikasi"` |
| Reinstall development | `php starterkit/installer/install.php --reset --company="Nama Aplikasi"` |
| Membuat App | `php artisan starter:make-app crm --name="CRM"` |
| Membuat App tanpa langsung sync | `php artisan starter:make-app crm --name="CRM" --no-sync` |
| Memeriksa perubahan registry | `php artisan starter:sync crm --dry-run` |
| Menerapkan registry | `php artisan starter:sync crm --force` |
| Sync seluruh App | `php artisan starter:sync --dry-run`, lalu `php artisan starter:sync --force` |
| Publish ulang asset | `php artisan starter:publish-assets` |
| Cek keamanan lokal | `php artisan starter:security-check` |
| Simulasi cek production | `php artisan starter:security-check --production` |
| Menyiapkan ulang data inti | `php artisan starter:setup --company="Nama Aplikasi"` |

`starterkit:install` adalah command internal yang dipanggil installer. Jangan
menjalankannya langsung untuk update atau deployment.

Yang berjalan otomatis:

- `starter:make-app` langsung menjalankan sync, kecuali memakai `--no-sync`;
- `php artisan migrate` memuat migration core dan seluruh folder
  `database/migrations/apps/<app>/`;
- Composer `post-autoload-dump` menjalankan `starter:publish-assets`;
- `starter:setup` menjalankan security check saat environment production.

## Multi-domain itu seperti apa?

Starterkit memakai satu root domain dengan beberapa subdomain App. Semuanya
tetap berada dalam satu project Laravel, satu database, satu login, dan satu
pengaturan global.

```text
domainxx.com
├── Landing, login, profil, user, role, pengaturan, dan log global
│
├── crm.domainxx.com                          App CRM
│   ├── Module Leads
│   │   └── Menu: Daftar Leads, Pipeline
│   └── Module Pelanggan
│       └── Menu: Data Pelanggan, Aktivitas
│
├── support.domainxx.com                      App Customer Support
│   └── Module Tiket
│       └── Menu: Daftar Tiket, SLA
│
├── marketing.domainxx.com                    App Marketing
│   └── Module Campaign
│       └── Menu: Campaign, Segmentasi
│
└── api.domainxx.com                          Gateway API opsional
    ├── /crm
    ├── /support
    └── /marketing
```

Ringkasnya:

```text
App = aplikasi/subdomain
Module = kelompok fitur sekaligus batas akses role
Menu/Submenu = navigasi halaman milik module
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
app/Livewire/Apps/Crm/
config/apps/crm.php
routes/apps/crm.php
routes/apps/crm.api.php
resources/views/apps/crm/
tests/Feature/Apps/Crm/
```

Migration bisnis App diletakkan di:

```text
database/migrations/apps/crm/
```

- `config/apps/crm.php`: definisi App, module, menu, dan landing.
- `routes/apps/crm.php`: route web `crm.domainxx.com`.
- `routes/apps/crm.api.php`: endpoint `api.domainxx.com/crm`.
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
api.domainxx.com/crm             Endpoint App CRM
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

## Reinstall khusus development

```bash
php starterkit/installer/install.php --reset --company="Nama Aplikasi"
```

Hanya dapat berjalan pada `APP_ENV=local` atau `APP_ENV=development`. Installer
meminta konfirmasi `y`, lalu kata `RESET`.

Mode ini menghapus seluruh database, source App, landing, extension UI,
migration/asset App, upload starter/App, dan issue feature. Production ditolak
sebelum file atau database diubah. Jangan gunakan reinstall sebagai cara
update.

## Update starterkit

Update tidak me-reset database:

```bash
cd starterkit
git pull --ff-only origin master

cd ..
composer require dedoc/scramble --no-interaction
php artisan starter:publish-assets
php artisan migrate --force
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check
php artisan test --compact
```

Periksa hasil `--dry-run` sebelum menerapkan sync.

## Deployment shared hosting

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan starter:publish-assets
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check --production
php artisan optimize
```

Production wajib memakai HTTPS, `APP_DEBUG=false`, password Superuser kuat, dan
document root menuju `public`. Root domain dan seluruh subdomain App/API
diarahkan ke folder `public` yang sama.

Config cache/`optimize` hanya untuk production. Selama development gunakan
config langsung dan jalankan `php artisan optimize:clear` jika cache pernah
dibuat.

## Pengembangan dengan AI

Starterkit ini terutama dirancang untuk agentic coding. Developer cukup
menjelaskan fitur, flow bisnis, data, role, App, module, dan struktur menu.

Agent AI membaca [`AGENTS.md`](AGENTS.md) dan rule terkait, lalu membuat detail
teknis di `issues/<fitur>.md`. Setelah disetujui, implementasi dapat dilanjutkan
dengan model yang lebih hemat tanpa mengulang instruksi keamanan, validasi,
pagination, audit, performa, UI, atau struktur folder.

Folder `starterkit` adalah core read-only untuk fitur project. Code bisnis
berada di Laravel host; perubahan universal starterkit dilakukan melalui
repository starterkit.

## Batas penting

- Satu instalasi mewakili satu perusahaan/client, bukan SaaS multi-tenant.
- Single App dan multi App sama-sama didukung.
- Seluruh App berbagi login, database, dan pengaturan global.
- Hak akses diberikan melalui module; menu hanya navigasi.
- Sidebar berasal dari registry App/module/menu.
- Landing dan fitur bisnis berada di Laravel host.
- Extension UI berada di `resources/views/extensions/starter/`.
- Installer normal hanya untuk Laravel fresh dan database baru.
- Reinstall hanya untuk development; update dan deploy tidak memakai installer.
