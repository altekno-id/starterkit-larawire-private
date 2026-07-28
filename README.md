# Laravel Private Starterkit

Core starterkit internal yang dipasang sebagai clone Git di dalam project Laravel. Repository ini memiliki autentikasi private, app/module registry, authorization, audit log, pengaturan, layout, keamanan sesi, serta aturan pengembangan bersama.

Starterkit bukan tempat feature bisnis project. Semua feature project tetap berada di repository Laravel pemiliknya.

## Struktur penggunaan

```text
project/
├── dashboard/                     # Laravel milik project
│   ├── app/Apps/
│   ├── resources/views/apps/
│   ├── resources/views/extensions/starter/
│   └── starterkit/                # clone repository ini
└── .gitignore                     # mengabaikan dashboard/starterkit/
```

Clone starterkit:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git dashboard/starterkit
```

Ikuti [panduan instalasi Git clone](docs/installation-git-clone.md) untuk menghubungkan autoload, provider, bootstrap, asset, migration, dan setup awal.

## Ownership

- Folder `starterkit/` hanya boleh berubah untuk improvement universal starterkit melalui branch dan pull request repository ini.
- Feature, model, migration, UI, asset, route, dan rule khusus project tidak boleh ditulis di dalam clone starterkit.
- Project menambahkan app/module melalui struktur `Apps/<Subdomain>`, registry `config/apps`, dan route `routes/apps`.
- Project dapat memakai extension contract yang terdokumentasi tanpa mengubah Blade starterkit.

## Update

```bash
cd dashboard/starterkit
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

Setiap perubahan pada `master` wajib kompatibel dengan project pemakai dan migration existing.

## Pengembangan core

Repository ini tetap dapat dijalankan sebagai Laravel standalone untuk pengembangan dan regression test core:

```bash
composer install
php artisan test --compact
```

Aturan utama untuk developer dan AI berada di [AGENTS.md](AGENTS.md).
