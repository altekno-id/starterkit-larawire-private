# Instalasi Starterkit melalui Git Clone

## Hasil akhir

Starterkit di-clone sebagai repository Git mandiri pada path tetap
`<laravel>/starterkit`. Repo Laravel host mengabaikan folder tersebut sehingga
feature project tidak bercampur dengan commit starterkit.

Starterkit bukan Composer package dan bukan Git submodule. Source core tetap
dibaca langsung dari clone dengan namespace `Altekno\StarterKit`.

## Instalasi standar: tanpa copy atau edit manual

### 1. Siapkan Laravel host

Gunakan project Laravel yang sudah selesai dibuat dan sudah memiliki dependency
dasar (`vendor/`). Dari root Laravel host, pastikan `.env` tersedia dan isi:

- `APP_NAME`;
- `APP_URL` dengan URL aplikasi yang benar;
- koneksi database yang akan digunakan.

Jika `APP_DOMAIN` belum tersedia, installer menurunkannya dari host pada
`APP_URL`. Nilai environment project yang sudah ada—termasuk credential,
identitas aplikasi, dan akun setup—tidak ditimpa. Untuk MySQL atau MariaDB,
database harus sudah dibuat dan credential pada `.env` harus dapat dipakai
sebelum migration dijalankan.

### 2. Clone dan install

Jalankan tepat dari root Laravel host:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
php starterkit/install.php --company="Nama Aplikasi"
```

Itu saja untuk instalasi standar. Tidak ada file core yang dicopy ke `app/`,
`resources/`, `routes/`, atau `database/` host.

`install.php` adalah bootstrap satu kali karena sebelum Composer autoload dan
provider terhubung, Laravel belum mungkin menemukan command dari folder clone.
Setelah bootstrap selesai, script otomatis menjalankan:

```bash
php artisan starterkit:install --company="Nama Aplikasi"
```

Perintah Artisan tersebut idempotent dan dapat dijalankan ulang untuk
menyelesaikan instalasi yang terputus. Command ini menjalankan sinkronisasi
registry dengan `--force`, sehingga update rutin tetap memakai alur dry-run pada
bagian Update Starterkit.

Opsi yang tersedia:

```text
--company=          Nama internal aplikasi/client
--email=            Email notifikasi Superuser
--username=         Username Superuser
--skip-migration    Lewati migration untuk sementara
```

Contoh ketika database belum siap:

```bash
php starterkit/install.php --company="Nama Aplikasi" --skip-migration

# setelah database siap
php artisan starterkit:install --company="Nama Aplikasi"
```

## Apa yang dilakukan installer

Installer hanya mengubah connector minimum pada Laravel host:

| File/area host | Perubahan otomatis |
|---|---|
| `.gitignore` | Menambahkan `/starterkit/` |
| `composer.json` | Memasang namespace `Altekno\StarterKit\` dan auto-publish asset |
| `composer.lock`/`vendor` | Memasang `livewire/livewire` dan `laravel-lang/common` bila belum ada |
| `bootstrap/providers.php` | Mendaftarkan `StarterServiceProvider` tanpa menghapus provider lain |
| `bootstrap/app.php` | Menghubungkan route, middleware, dan exception starterkit |
| `.env` | Menambahkan environment minimum tanpa menimpa credential/identitas project; nilai keamanan wajib dinormalkan |
| `.env.example` | Mencerminkan environment key yang sama tanpa rahasia |
| `lang/<APP_LOCALE>` | Memasang bahasa melalui Laravel Lang bila belum tersedia |
| `public/assets/starter` dan `public/assets/tabler` | Menyalin asset milik core; bukan tempat edit |
| database | Menjalankan migration aman dan setup awal secara idempotent |

Installer juga menjalankan pemeriksaan keamanan lokal. Pada production,
`STARTER_SUPERUSER_PASSWORD` wajib diisi password kuat dan seluruh pemeriksaan
production harus lulus.

## Mengapa bukan langsung `php artisan starterkit:install`?

Tepat setelah clone, Composer Laravel host belum mengenal namespace starterkit
dan provider belum terdaftar. Karena itu Artisan belum dapat menemukan command
`starterkit:install`.

`php starterkit/install.php` menyelesaikan bootstrap teknis tersebut, lalu
memanggil command Artisan yang sama secara otomatis. Ini memungkinkan instalasi
tanpa satu pun edit atau copy manual.

## Proteksi terhadap project yang sudah dikustomisasi

Installer bersifat idempotent:

- connector yang sudah benar tidak ditulis ulang;
- dependency yang sudah ada tidak di-update tanpa kebutuhan;
- data dan akun existing tidak dihapus;
- migration memakai mekanisme Laravel dan setup memakai operasi idempotent;
- `bootstrap/app.php` yang sudah memiliki konfigurasi khusus tidak ditimpa
  secara paksa.

Jika struktur `bootstrap/app.php` tidak dikenali sebagai Laravel baru atau
connector starterkit yang sudah lengkap, installer berhenti sebelum menimpanya.
Gunakan prosedur manual di bawah untuk menggabungkan konfigurasi project.

## Alternatif manual untuk bootstrap yang sudah dikustomisasi

Bagian ini bukan alur instalasi normal. Gunakan hanya ketika installer menolak
menimpa `bootstrap/app.php` karena project sudah memiliki API, broadcasting,
middleware, exception, atau routing khusus.

### 1. Dependency dan Composer autoload

```bash
composer require livewire/livewire laravel-lang/common
```

Gabungkan bagian yang relevan dari:

```text
starterkit/examples/composer-autoload.json
```

ke `composer.json` host. Pastikan tersedia:

```json
"Altekno\\StarterKit\\": "starterkit/src/"
```

dan script:

```json
"@php artisan starter:publish-assets --ansi"
```

pada `post-autoload-dump`.

### 2. Provider

Tambahkan `StarterServiceProvider::class` ke `bootstrap/providers.php` tanpa
menghapus provider project. Contoh utuh:

```text
starterkit/examples/providers.php
```

### 3. Bootstrap aplikasi

Gabungkan tiga connector dari:

```text
starterkit/examples/bootstrap-app.php
```

ke `bootstrap/app.php` host:

- `StarterBootstrap::registerRoutes()`;
- `StarterBootstrap::configureMiddleware($middleware)`;
- `StarterBootstrap::configureExceptions($exceptions)`.

Pertahankan API route, broadcasting, exception renderer, dan middleware project
yang sudah ada. Route web project tetap didaftarkan pada domain
`config('app.domain')` agar tidak menangkap root route milik app subdomain.

### 4. Environment

Gabungkan key dari:

```text
starterkit/examples/env.example
```

ke `.env` dan `.env.example`. Nilai rahasia hanya berada di `.env`.

### 5. Selesaikan instalasi

```bash
composer dump-autoload
php artisan starterkit:install --company="Nama Aplikasi"
```

## File project yang tetap dimiliki host

Feature project tidak dibuat di clone. Area berikut tetap berada di repository
Laravel host:

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

Source starterkit tidak dicopy ke area tersebut. Laravel membacanya langsung
dari clone, sehingga update core cukup dilakukan dengan Git pull.

## Extension UI project

Extension opsional berada di repo project:

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
- `profile-menu/index.blade.php`: item project pada dropdown profil sebelum
  divider Logout.
- `layout/head.blade.php`: metadata atau asset project yang benar-benar global.
- `layout/body-end.blade.php`: script project yang benar-benar global.

Sidebar memakai app/module/menu registry agar authorization tetap konsisten.
Extension wajib mengotorisasi targetnya sendiri dan mengikuti markup template
aktif.

## Update starterkit

Setelah update starterkit di-merge ke `master`:

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

Migration tidak menghapus data existing dan perubahan schema wajib mengikuti
rule production-safe. Periksa hasil dry-run sebelum menjalankan sync dengan
`--force`. Jangan mengubah file clone untuk kebutuhan satu project; improvement
universal dibuat melalui branch dan pull request starterkit.

## Deployment shared hosting

Bootstrap installer dijalankan saat development atau provisioning, bukan
menjadi kebutuhan pada setiap request production. Upload Laravel host beserta
clone `starterkit`, tetapi jangan upload `.git`, `.env` lokal, `node_modules`,
atau artifact development.

Pada server:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan starter:publish-assets
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check --production
```

Tidak ada daemon, extension server khusus, symlink core, atau config cache wajib.
Pastikan `storage/` dan `bootstrap/cache/` writable. Config cache hanya digunakan
ketika deployment production mendukungnya; selama development tidak perlu
diaktifkan.
