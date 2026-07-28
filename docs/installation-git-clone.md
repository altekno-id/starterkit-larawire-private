# Instalasi Starterkit melalui Git Clone

## Tujuan

Starterkit di-clone sebagai repository Git mandiri pada path tetap `<laravel>/starterkit`. Repo Laravel host mengabaikan folder tersebut sehingga commit feature project tidak pernah bercampur dengan commit starterkit.

Tidak ada Composer package atau submodule. Composer milik Laravel host hanya dipakai untuk memuat namespace terisolasi `Altekno\StarterKit` dari folder clone.

## 1. Clone

Dari root Laravel host:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
```

Tambahkan ke `.gitignore` repository project:

```gitignore
/starterkit/
```

## 2. Dependency dan autoload

Dependency minimum host harus mencakup dependency runtime yang tercatat pada `starterkit/composer.json`, terutama Livewire dan paket locale.

Tambahkan mapping `Altekno\\StarterKit\\` pada `composer.json` Laravel host:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Altekno\\StarterKit\\": "starterkit/src/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
}
```

Jalankan:

```bash
composer dump-autoload
```

Project memakai namespace `App\...`, sedangkan seluruh class inti memakai `Altekno\StarterKit\...`. Pemisahan ini mencegah benturan class dan membuat batas kepemilikan jelas.

## 3. Provider

Tambahkan provider starter pada `bootstrap/providers.php`:

```php
<?php

use App\Providers\AppServiceProvider;
use Altekno\StarterKit\Providers\Starter\StarterServiceProvider;

return [
    StarterServiceProvider::class,
    AppServiceProvider::class,
];
```

## 4. Bootstrap

Hubungkan route, middleware, dan exception starter melalui `bootstrap/app.php`:

```php
use Altekno\StarterKit\Support\Starter\StarterBootstrap;
```

Pada `withRouting(..., using:)`:

```php
StarterBootstrap::registerRoutes();
```

Pada middleware dan exception:

```php
->withMiddleware(function (Middleware $middleware): void {
    StarterBootstrap::configureMiddleware($middleware);
})
->withExceptions(function (Exceptions $exceptions): void {
    StarterBootstrap::configureExceptions($exceptions);
})
```

Route root project tetap didaftarkan dari `routes/web.php` milik host. Contoh utuh dapat dilihat pada `starterkit/bootstrap/app.php`.

## 5. Environment

Gunakan environment Laravel host. Nilai minimum:

```env
APP_DOMAIN=example.test
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

STARTER_SUPERUSER_USERNAME=superuser
STARTER_SUPERUSER_EMAIL=developer@example.test
STARTER_SUPERUSER_PASSWORD=
```

`STARTER_SUPERUSER_PASSWORD` wajib kuat pada production. Default lokal hanya untuk development.

## 6. Install dan setup

```bash
php artisan starter:publish-assets
php artisan migrate
php artisan starter:setup --company="Nama Aplikasi"
php artisan starter:security-check
```

Migration starter dibaca langsung dari clone. Asset static core disinkronkan ke `public/assets/starter` dan `public/assets/tabler`; kedua folder tersebut dimiliki starterkit dan tidak boleh diubah project.

## 7. Project extension

Extension bersifat opsional dan seluruh filenya berada di repo project:

```text
resources/views/extensions/starter/
├── header-actions/index.blade.php
├── profile-menu/index.blade.php
└── layout/
    ├── head.blade.php
    └── body-end.blade.php
```

Kontrak:

- `header-actions/index.blade.php`: aksi global project pada top bar. Menerima variable `$compact` untuk mode mobile/desktop.
- `profile-menu/index.blade.php`: item project pada dropdown profil, sebelum divider Logout.
- `layout/head.blade.php`: metadata atau asset global project yang memang diperlukan seluruh shell.
- `layout/body-end.blade.php`: script global project yang memang diperlukan seluruh shell.

Sidebar tidak memakai raw Blade extension. Tambahkan navigasi melalui app/module/menu registry project agar authorization tetap konsisten.

Contoh item dropdown:

```blade
@can('open-project-help')
    <a href="{{ route('project.help') }}" class="dropdown-item" data-starter-navigate>
        Bantuan ADUKAN
    </a>
@endcan
```

Extension wajib mengotorisasi targetnya sendiri, mengikuti markup template aktif, dan tidak boleh mengubah state internal starterkit.

## 8. Update master

Setelah pull request starterkit di-merge:

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
php artisan test --compact
```

Periksa hasil dry-run sebelum menjalankan sync dengan `--force`. Jangan mengubah file clone langsung untuk kebutuhan satu project. Jika perubahan memang universal, buat branch di clone starterkit, push, ajukan PR, lalu merge ke `master`.
