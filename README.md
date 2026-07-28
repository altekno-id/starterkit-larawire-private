# Laravel Private Starterkit

Repository source starterkit internal yang di-clone ke dalam project Laravel. Repository ini **bukan aplikasi Laravel mandiri**, bukan Composer package, dan bukan Git submodule.

## Isi repository

```text
starterkit/
├── install.php                   # bootstrap installer pertama
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

### Instalasi baru yang direkomendasikan

Dari root project Laravel yang sudah dibuat dan sudah memiliki `vendor/`:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
php starterkit/install.php --company="Nama Aplikasi"
```

Selesai. Tidak ada folder core yang perlu dicopy dan tidak ada file Laravel
yang perlu diedit manual pada instalasi standar. Bootstrap installer akan:

- mengabaikan `/starterkit/` dari Git project;
- memasang dependency runtime yang belum tersedia;
- menghubungkan Composer autoload, provider, route, middleware, dan exception;
- menambahkan environment key yang belum ada dengan default aman ke `.env` dan
  `.env.example`, tanpa menimpa credential atau identitas project;
- menjalankan `composer dump-autoload`, generate `APP_KEY` bila masih kosong;
- meneruskan proses ke `php artisan starterkit:install`;
- memasang bahasa sesuai `APP_LOCALE`, publish asset, migration, setup Superuser,
  sinkronisasi registry, dan pemeriksaan keamanan.

Sebelum menjalankan installer, isi koneksi database pada `.env` bila project
tidak memakai database default Laravel. `APP_URL` juga harus memakai URL lokal
atau production yang sebenarnya; `APP_DOMAIN` diturunkan otomatis dari URL itu
jika key tersebut belum tersedia.

Perintah `php artisan starterkit:install` tersedia setelah bootstrap pertama dan
idempotent untuk melanjutkan instalasi yang terputus. Command ini melakukan
sinkronisasi registry dengan `--force`; update rutin tetap mengikuti prosedur
dry-run di bawah. Jika `bootstrap/app.php` sudah dikustomisasi, installer akan
berhenti tanpa menimpanya dan menunjukkan connector yang perlu digabungkan.

Panduan lengkap, daftar file yang disentuh, troubleshooting, instalasi manual,
dan update tersedia di [Instalasi Starterkit melalui Git Clone](docs/installation-git-clone.md).

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
php artisan starter:publish-assets
php artisan migrate --force
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check
php artisan test --compact
```

`composer dump-autoload` otomatis menjalankan `starter:publish-assets`. Periksa
hasil dry-run sebelum sync dengan `--force`.

Aturan utama developer dan AI berada di [AGENTS.md](AGENTS.md).
