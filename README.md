# Laravel Private Starterkit

Repository source starterkit internal yang di-clone ke dalam project Laravel.
Repository ini **bukan aplikasi Laravel mandiri**, bukan Composer package, dan
bukan Git submodule.

README ini adalah satu-satunya panduan penggunaan manual untuk developer.
Aturan implementasi developer dan AI tetap berada di [`AGENTS.md`](AGENTS.md)
beserta file tematik di `docs/rules/`.

## Isi repository

```text
starterkit/
├── installer/                    # bootstrap awal; abaikan saat development fitur
│   ├── install.php               # satu-satunya entry point instalasi
│   └── templates/                # template internal connector
├── src/                          # runtime PHP namespace Altekno\StarterKit
├── config/                       # config auth dan starter
├── database/migrations/starter/  # schema inti starterkit
├── routes/starter/               # route autentikasi dan pengaturan
├── resources/views/starter/      # Livewire view, layout, dan error page
├── public/assets/                # asset core yang dipublish ke host
├── docs/rules/                   # aturan implementasi developer dan AI
└── docs/template/                # atlas referensi UI
```

Tidak ada `artisan`, shell `bootstrap/`, folder `app/`, dependency `vendor`,
database development, landing project, atau app contoh di repository ini.
Seluruh command Composer dan Artisan dijalankan dari root Laravel host.

## Instalasi cepat

### 1. Siapkan Laravel host

Gunakan project Laravel yang sudah selesai dibuat dan memiliki dependency dasar
(`vendor/`). Pastikan `.env` tersedia, lalu isi:

- `APP_NAME`;
- `APP_URL` dengan URL aplikasi yang sebenarnya;
- koneksi database yang akan digunakan.

Untuk MySQL atau MariaDB, buat database lebih dahulu dan pastikan credential
`.env` dapat digunakan. Jika Laravel host berasal dari clone Git dan belum
memiliki `vendor/`, jalankan:

```bash
composer install
```

### 2. Clone dan jalankan installer

Jalankan tepat dari root Laravel host:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
php starterkit/installer/install.php --company="Nama Aplikasi"
```

Selesai. Pada instalasi Laravel standar tidak ada file core yang perlu dicopy
dan tidak ada file Laravel yang perlu diedit manual.

Jika `APP_DOMAIN` belum tersedia, installer menurunkannya dari host pada
`APP_URL`. Nilai environment yang sudah ada—termasuk credential, identitas
aplikasi, dan akun setup—tidak ditimpa.

## Opsi installer

```text
--company=          Nama internal aplikasi/client
--email=            Email notifikasi Superuser
--username=         Username Superuser
--skip-migration    Pasang connector tanpa mengakses database
```

Contoh ketika database belum siap:

```bash
php starterkit/installer/install.php --company="Nama Aplikasi" --skip-migration

# Setelah database siap:
php artisan starterkit:install --company="Nama Aplikasi"
```

Untuk production, isi `STARTER_SUPERUSER_PASSWORD` dengan password kuat pada
`.env` sebelum instalasi database. Password development tidak boleh dipakai pada
production.

## Apa yang dilakukan installer

Installer memasang connector minimum secara otomatis:

| File/area Laravel host | Perubahan |
|---|---|
| `.gitignore` | Menambahkan `/starterkit/` |
| `composer.json` | Menambahkan namespace `Altekno\StarterKit\` dan auto-publish asset |
| `composer.lock` dan `vendor/` | Memasang `livewire/livewire`, `laravel-lang/common`, serta Pest untuk development bila belum tersedia |
| `bootstrap/providers.php` | Mendaftarkan `StarterServiceProvider` tanpa menghapus provider project |
| `bootstrap/app.php` | Menghubungkan route, middleware, dan exception starterkit |
| `.env` | Menambahkan key yang belum tersedia dan menormalkan nilai keamanan wajib |
| `.env.example` | Mencerminkan environment key tanpa nilai rahasia |
| `lang/<APP_LOCALE>` | Memasang bahasa sesuai `APP_LOCALE` melalui Laravel Lang |
| `tests/Pest.php` | Membuat bootstrap Pest bila project belum memilikinya |
| `public/assets/starter` dan `public/assets/tabler` | Mempublish asset core; bukan tempat edit |
| database | Menjalankan migration dan setup awal secara idempotent |
| landing project | Membuat landing minimum bila root landing belum tersedia |
| app registry | Membuat app `app1` dan module `dashboard` bila project belum memiliki app |

Installer juga melakukan:

- generate `APP_KEY` jika masih kosong;
- setup perusahaan dan akun Superuser;
- landing minimum bertuliskan “Ini landing page” dengan tombol Login;
- satu app awal `app1`;
- satu module `dashboard`;
- menu induk Dashboard dengan Submenu 1 dan Submenu 2;
- sinkronisasi registry app, module, route, dan menu;
- pemeriksaan konfigurasi keamanan.

## Mengapa instalasi pertama bukan command Artisan?

Tepat setelah clone, Composer Laravel host belum mengenal namespace starterkit
dan provider belum terdaftar. Artisan belum mungkin menemukan
`starterkit:install`.

`php starterkit/installer/install.php` memasang bootstrap teknis tersebut, lalu otomatis
menjalankan:

```bash
php artisan starterkit:install --company="Nama Aplikasi"
```

Setelah instalasi pertama, command Artisan tersedia dan idempotent untuk
melanjutkan instalasi yang terputus. Command tersebut melakukan sinkronisasi
registry dengan `--force`; update rutin tetap mengikuti alur dry-run pada bagian
Update starterkit.

Seluruh file bootstrap awal berada di `starterkit/installer/`. Setelah instalasi
berhasil, folder tersebut tidak digunakan oleh runtime request maupun
development feature dan harus diabaikan developer/AI. Folder tetap disimpan
agar clone Git bersih, instalasi dapat diaudit, dan setup dapat dipulihkan tanpa
mengunduh artifact lain.

## Keamanan terhadap project existing

Installer tidak memaksa menimpa project:

- connector yang sudah benar dilewati;
- dependency yang sudah tersedia tidak di-update tanpa kebutuhan;
- credential dan identitas environment existing dipertahankan;
- data dan akun existing tidak dihapus;
- migration dan setup memakai operasi Laravel yang idempotent;
- `bootstrap/app.php` yang sudah dikustomisasi tidak ditimpa otomatis.

Jika `bootstrap/app.php` berisi API, broadcasting, middleware, exception, atau
routing khusus yang tidak dikenali, installer berhenti sebelum menimpanya.
Gunakan fallback manual berikut, lalu jalankan installer kembali.

## Fallback manual untuk bootstrap yang sudah dikustomisasi

Bagian ini bukan alur instalasi normal.

### Composer

```bash
composer require livewire/livewire laravel-lang/common
composer require --dev pestphp/pest pestphp/pest-plugin-laravel --with-all-dependencies
```

Pastikan mapping berikut tersedia pada `composer.json` host:

```json
"Altekno\\StarterKit\\": "starterkit/src/"
```

Tambahkan command berikut pada `scripts.post-autoload-dump`:

```json
"@php artisan starter:publish-assets --ansi"
```

### Provider

Tambahkan provider berikut ke array `bootstrap/providers.php` tanpa menghapus
provider project:

```php
use Altekno\StarterKit\Providers\Starter\StarterServiceProvider;

return [
    StarterServiceProvider::class,
    App\Providers\AppServiceProvider::class,
];
```

### Bootstrap aplikasi

Gabungkan tiga connector dari
`starterkit/installer/templates/bootstrap-app.php` ke `bootstrap/app.php` host:

- `StarterBootstrap::registerRoutes()`;
- `StarterBootstrap::configureMiddleware($middleware)`;
- `StarterBootstrap::configureExceptions($exceptions)`.

Pertahankan konfigurasi API, broadcasting, exception, dan middleware project.
Route web project harus tetap didaftarkan pada `config('app.domain')` agar tidak
menangkap root route milik app subdomain.

### Environment

Pastikan key berikut tersedia pada `.env` dan `.env.example`. Nilai rahasia
hanya boleh berada di `.env`:

```env
APP_DOMAIN=example.test
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=null

STARTER_SUPERUSER_USERNAME=superuser
STARTER_SUPERUSER_EMAIL=developer@example.test
STARTER_SUPERUSER_PASSWORD=
AUTH_PASSWORD_RESET_TOKEN_TABLE=x_password_reset_tokens
```

Selesaikan instalasi:

```bash
composer dump-autoload
php artisan starterkit:install --company="Nama Aplikasi"
```

## Batas kepemilikan code

- Folder clone hanya boleh berubah untuk improvement universal starterkit.
- Feature project dilarang mengubah file di dalam clone.
- Feature, model, migration, UI, route, dan rule khusus project berada di
  repository Laravel host.
- Project memakai extension contract untuk menyisipkan UI tanpa mengubah Blade
  core.
- Branch `master` starterkit selalu menjadi baseline kompatibel untuk seluruh
  project tim.

Area feature milik Laravel host:

```text
app/
config/apps/
database/migrations/apps/
resources/views/apps/
resources/views/extensions/starter/
resources/views/landing/
routes/apps/
routes/web.php
tests/
```

Source core tidak dicopy ke area tersebut. Laravel membacanya langsung dari
clone sehingga update core cukup dilakukan melalui Git pull.

Pada Laravel host kosong, installer mengisi baseline berikut:

```text
Landing root
└── tombol Login

App app1
└── Module dashboard
    └── Dashboard
        ├── Submenu 1
        └── Submenu 2
```

Baseline hanya dibuat bila landing atau app project belum tersedia. Installer
tidak menimpa landing custom dan tidak menambahkan `app1` ketika project sudah
memiliki registry app.

## Extension UI project

Extension opsional berada di repository Laravel host:

```text
resources/views/extensions/starter/
├── header-actions/index.blade.php
├── profile-menu/index.blade.php
└── layout/
    ├── head.blade.php
    └── body-end.blade.php
```

- `header-actions/index.blade.php`: aksi project pada top bar; menerima
  `$compact` untuk mode mobile/desktop.
- `profile-menu/index.blade.php`: menu project pada dropdown profil sebelum
  divider Logout.
- `layout/head.blade.php`: metadata atau asset project yang benar-benar global.
- `layout/body-end.blade.php`: script project yang benar-benar global.

Sidebar tetap memakai app/module/menu registry agar authorization konsisten.
Setiap extension wajib mengotorisasi targetnya sendiri dan mengikuti markup
template aktif.

## Update starterkit

Setelah perubahan starterkit di-merge ke `master`, jalankan dari Laravel host:

```bash
cd starterkit
git switch master
git pull --ff-only origin master

cd ..
composer dump-autoload
php artisan starter:publish-assets
php artisan migrate --force
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check
php artisan test --compact
```

`composer dump-autoload` otomatis menjalankan `starter:publish-assets`, tetapi
command publish eksplisit tetap aman. Periksa hasil `starter:sync --dry-run`
sebelum menjalankan sync dengan `--force`.

Migration tidak boleh menghapus atau merusak data existing. Jangan mengubah file
clone untuk kebutuhan satu project; improvement universal dibuat melalui branch
dan pull request starterkit.

## Deployment shared hosting

Bootstrap installer dijalankan saat development atau provisioning, bukan pada
setiap request production. Upload Laravel host beserta clone `starterkit`,
tetapi jangan upload `.git`, `.env` lokal, `node_modules`, atau artifact
development.

Pada server:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan starter:publish-assets
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check --production
```

Pastikan:

- `APP_ENV=production`;
- `APP_DEBUG=false`;
- `APP_URL` menggunakan HTTPS dan sesuai `APP_DOMAIN`;
- `SESSION_SECURE_COOKIE=true`;
- `STARTER_SUPERUSER_PASSWORD` tidak memakai password development;
- `storage/` dan `bootstrap/cache/` writable;
- document root mengarah ke folder `public`;
- root domain dan app subdomain mengarah ke instalasi yang sama.

Starterkit tidak membutuhkan daemon, reverse proxy, symlink core, atau
konfigurasi server khusus. Config cache tidak wajib; selama development jangan
mengaktifkannya.

## Troubleshooting ringkas

### `starterkit:install` tidak ditemukan

Gunakan bootstrap pertama:

```bash
php starterkit/installer/install.php --company="Nama Aplikasi"
```

### Database belum siap

```bash
php starterkit/installer/install.php --company="Nama Aplikasi" --skip-migration
```

Setelah credential dan database siap:

```bash
php artisan starterkit:install --company="Nama Aplikasi"
```

### `bootstrap/app.php` ditolak installer

File tersebut sudah dikustomisasi. Gabungkan
`starterkit/installer/templates/bootstrap-app.php` sesuai bagian fallback
manual; installer sengaja tidak menimpa konfigurasi project yang tidak dikenali.

### Asset tidak sinkron setelah update

```bash
composer dump-autoload
php artisan starter:publish-assets
```

### Config development pernah tercache

```bash
php artisan optimize:clear
```

Jangan menjalankan `config:cache` atau `optimize` selama development rutin.
