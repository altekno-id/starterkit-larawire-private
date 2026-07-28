# Laravel Private Starterkit

Starterkit internal untuk mempercepat pembuatan aplikasi perusahaan berbasis
Laravel, Livewire, dan template UI yang dibawa project. Starterkit ini
menyediakan fondasi aplikasi yang sudah konsisten dari sisi struktur code,
keamanan, performa, hak akses, audit, tampilan, dan cara pengembangan.

Repository ini bukan aplikasi yang dijalankan sendiri. Repository di-clone ke
dalam project Laravel sebagai folder `starterkit`, lalu installer akan
menghubungkannya secara otomatis.

## Untuk apa starterkit ini?

Starterkit ini cocok sebagai dasar aplikasi internal yang mempunyai satu atau
beberapa app/subdomain, misalnya dashboard, kepegawaian, pendaftaran, pelayanan,
atau pengaduan.

Setelah instalasi, project sudah mempunyai fondasi:

- login, logout, penggantian password, dan penguncian layar;
- Superuser, user, role, module, menu, dan hak akses;
- pencatatan aktivitas serta event keamanan;
- struktur app/subdomain yang terisolasi;
- komponen UI, layout, halaman error, dan asset siap pakai;
- Bahasa Indonesia, format angka, serta currency;
- aturan keamanan, performa, migration, pengujian, dan deployment shared hosting;
- satu app awal `app1` dengan module `dashboard`;
- menu Dashboard yang berisi Submenu 1 dan Submenu 2.

Landing awal juga dibuat otomatis dan dapat diganti sepenuhnya oleh developer.

## Dirancang untuk Agentic Coding

Starterkit ini lebih ditujukan untuk pengembangan memakai AI dalam agentic code
mode, bukan untuk menulis seluruh code secara manual.

Developer cukup menjelaskan kebutuhan bisnis atau fitur. Agent AI akan membaca
aturan di [`AGENTS.md`](AGENTS.md), mempelajari struktur yang relevan, lalu
menyiapkan detail teknis fitur pada folder `issues/` milik project. Setelah
detail tersebut disetujui, implementasi dapat dilanjutkan—termasuk menggunakan
model yang lebih hemat—tanpa developer harus mengulang instruksi umum tentang
security, pagination, audit, validation, Livewire/Alpine, atau penempatan file.

Code starterkit tetap terpisah dari code aplikasi. Feature project dibuat di
Laravel host, bukan dengan mengubah folder `starterkit`.

## Syarat wajib sebelum instalasi

Gunakan hanya pada:

- project Laravel yang benar-benar baru/fresh;
- database baru yang khusus untuk project tersebut;
- project yang belum mempunyai feature, migration bisnis, atau data penting.

Installer menjalankan `migrate:fresh`. Artinya **semua tabel dan seluruh data**
pada database yang terhubung di `.env` akan dihapus, kemudian schema dibuat
ulang dari awal.

Jangan menjalankan installer pada project yang sudah berjalan, database
production, atau database yang dipakai aplikasi lain. Untuk project yang sudah
memakai starterkit, gunakan alur update—bukan instalasi ulang.

## Instalasi

### 1. Siapkan Laravel fresh

Buat project Laravel baru, siapkan `.env`, lalu isi nama aplikasi, URL, dan
koneksi ke database baru yang masih boleh di-reset.

### 2. Clone starterkit

Jalankan dari root project Laravel:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
```

### 3. Jalankan satu command instalasi

```bash
php starterkit/installer/install.php --company="Nama Aplikasi"
```

Installer akan menampilkan peringatan reset database. Periksa kembali database
di `.env`, lalu ketik `y` hanya jika project dan database memang baru.
Jawaban selain `y` membatalkan instalasi sebelum file project atau database
diubah.

Setelah konfirmasi, installer menangani dependency, konektor, environment,
bahasa, asset, migration, akun Superuser, landing, app awal, dan pemeriksaan
keamanan secara otomatis. Tidak ada folder yang perlu dicopy dan tidak ada file
Laravel yang perlu diedit manual pada instalasi standar.

## Setelah instalasi

Buka URL aplikasi sesuai `APP_URL`, lalu gunakan tombol Login pada landing.
Kredensial Superuser mengikuti nilai berikut di `.env`:

```env
STARTER_SUPERUSER_USERNAME=superuser
STARTER_SUPERUSER_EMAIL=developer@example.test
STARTER_SUPERUSER_PASSWORD=
```

Gunakan password yang kuat dan jangan menyimpan password production di
`.env.example`.

Untuk mulai membuat feature, berikan kebutuhan bisnis kepada agent AI dari root
Laravel host. Agent akan memakai aturan starterkit, tetapi seluruh code feature
tetap ditempatkan di repository project.

## Update starterkit

Update tidak me-reset database. Dari root Laravel host:

```bash
cd starterkit
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

Periksa hasil `starter:sync --dry-run` sebelum menjalankan sync. Migration update
wajib aman untuk data existing.

## Deployment shared hosting

Starterkit tidak membutuhkan daemon, reverse proxy, atau konfigurasi server
khusus. Project tetap mengikuti deployment Laravel biasa:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan starter:publish-assets
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check --production
php artisan optimize
```

Pastikan production memakai HTTPS, `APP_DEBUG=false`, credential database yang
benar, password Superuser yang kuat, dan document root mengarah ke folder
`public`.

Config cache/`optimize` hanya digunakan di production. Selama development,
gunakan `php artisan optimize:clear` dan jangan mengaktifkan config cache.

## Batas penting

- Folder `starterkit` adalah core dan tidak dipakai untuk feature khusus project.
- Improvement universal starterkit dibuat melalui branch/PR repository ini.
- Landing, app, module, migration, route, view, test, dan extension project
  berada di Laravel host.
- Menu project mengikuti registry app/module/menu.
- Area tambahan pada header, dropdown profil, atau layout memakai extension di
  `resources/views/extensions/starter/`, sehingga Blade core tidak perlu diubah.
- Jangan menjalankan `starterkit:install` sebagai proses update atau deploy.

Jika installer menolak project, baca pesan yang ditampilkan. Penolakan biasanya
berarti target bukan Laravel fresh atau struktur bootstrap sudah dikustomisasi;
buat Laravel baru dan ulangi instalasi pada target yang bersih.
