# Laravel Private Starterkit

Repository source starterkit internal yang di-clone ke dalam project Laravel. Repository ini **bukan aplikasi Laravel mandiri**, bukan Composer package, dan bukan Git submodule.

## Isi repository

```text
starterkit/
├── src/                          # runtime PHP dengan namespace Altekno\StarterKit
├── config/                       # config auth dan starter
├── database/migrations/starter/  # schema inti starterkit
├── routes/starter/               # route autentikasi dan pengaturan
├── resources/views/starter/      # Livewire views, layout, error page
├── public/assets/                # asset core yang dipublish ke host
├── docs/                         # rules dan atlas referensi UI
└── examples/                     # snippet connector, bukan Laravel app
```

Tidak ada `artisan`, `bootstrap/`, `app/`, dependency vendor, database development, atau app contoh di repository ini. Seluruh command dijalankan dari root Laravel host.

## Instalasi

Dari root project Laravel:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
```

Folder `starterkit/` wajib diabaikan oleh Git host. Ikuti [panduan instalasi](docs/installation-git-clone.md) untuk memasang dependency runtime, autoload, provider, bootstrap, environment, asset, dan migration.

## Ownership

- Folder clone hanya boleh berubah untuk improvement universal starterkit.
- Feature, model, migration, UI, route, dan rule khusus project berada di repository Laravel host.
- Project memakai extension contract untuk menyisipkan UI tanpa mengubah Blade core.
- `master` selalu menjadi baseline kompatibel yang dipakai seluruh project tim.

## Update pada project pemakai

```bash
cd starterkit
git switch master
git pull --ff-only origin master

cd ..
composer dump-autoload
php artisan migrate --force
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan test --compact
```

`composer dump-autoload` dapat dikonfigurasi untuk otomatis menjalankan `starter:publish-assets`. Periksa hasil dry-run sebelum sync dengan `--force`.

Aturan utama developer dan AI berada di [AGENTS.md](AGENTS.md).
