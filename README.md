# Laravel Private Starterkit

Starterkit internal untuk membangun aplikasi perusahaan berbasis Laravel,
Livewire, dan template UI yang dibawa project. Fondasi autentikasi, hak akses,
audit, keamanan, performa, struktur code, dan tampilan dasar sudah disiapkan agar
developer dapat langsung fokus pada kebutuhan bisnis.

Repository ini bukan aplikasi Laravel mandiri. Repository di-clone sebagai
folder `starterkit` di dalam Laravel baru, kemudian installer menghubungkannya
secara otomatis.

## Cocok untuk project apa?

Starterkit ini dibuat untuk aplikasi private/internal milik satu perusahaan atau
satu client, bukan SaaS multi-tenant. Contohnya:

- aplikasi pengaduan;
- penerimaan siswa atau mahasiswa;
- kepegawaian dan presensi;
- pelayanan internal;
- dashboard operasional;
- beberapa sistem internal yang berbagi login.

Project dapat memiliki satu App saja maupun banyak App. Semua App berada dalam
satu instalasi Laravel, satu database, satu login, dan satu pengaturan global.
Setiap App tetap terpisah melalui subdomain, module, route, menu, hak akses, dan
folder code masing-masing.

Contoh single App:

```text
example.com                 Landing, login, profil, dan pengaturan global
layanan.example.com         App Layanan
```

Contoh multi App:

```text
example.com                 Landing, login, profil, dan pengaturan global
kepegawaian.example.com     App Kepegawaian
keuangan.example.com        App Keuangan
pelayanan.example.com       App Pelayanan
```

Login dan session dapat digunakan lintas subdomain selama seluruh domain
mengarah ke instalasi Laravel yang sama.

## Cara memahami struktur navigasi

Struktur project mengikuti urutan berikut:

```text
App
└── Module
    └── Menu
        └── Submenu
```

- **App** adalah area fitur tingkat atas dan biasanya mempunyai subdomain
  sendiri.
- **Module** adalah kelompok kemampuan di dalam App sekaligus batas pemberian
  hak akses kepada role.
- **Menu** adalah navigasi milik module. Menu dapat menuju halaman atau menjadi
  kelompok.
- **Submenu** adalah navigasi anak untuk membagi beberapa halaman dalam satu
  kelompok menu.

Contoh:

```text
App: layanan
└── Module: pengaduan
    └── Menu: Pengaduan
        ├── Submenu: Daftar Aduan
        └── Submenu: Buat Aduan
```

## Apa yang sudah tersedia?

Setelah instalasi, project mempunyai:

- login, logout, remember me, penggantian password, dan kunci layar;
- Superuser, user, role, module, menu, dan hak akses;
- pencatatan aktivitas serta event keamanan;
- registry App, module, route, menu, dan halaman awal role;
- landing project dan halaman error;
- komponen UI serta asset lokal siap pakai;
- Bahasa Indonesia dan format angka/currency;
- aturan migration yang aman untuk data existing;
- pola query, pagination, Livewire, Alpine, dan pengujian;
- deployment yang tetap ramah shared hosting.

## Dirancang untuk Agentic Coding

Starterkit ini lebih ditujukan untuk pengembangan menggunakan AI dalam agentic
code mode daripada penulisan seluruh code secara manual.

Developer cukup menjelaskan fitur, alur bisnis, data, dan role yang terlibat.
Agent AI membaca [`AGENTS.md`](AGENTS.md) serta rule yang relevan, kemudian
membuat spesifikasi teknis pada folder `issues/` di project Laravel. Setelah
spesifikasi disetujui, implementasi dapat dilanjutkan—termasuk memakai model
yang lebih hemat—tanpa mengulang instruksi umum tentang validation, security,
pagination, audit, performa, Livewire/Alpine, UI, atau penempatan file.

Code fitur project tidak boleh ditulis ke dalam folder `starterkit`. Folder
tersebut hanya berubah untuk improvement universal starterkit.

## Syarat wajib instalasi

Installer hanya boleh digunakan pada:

- Laravel yang benar-benar baru/fresh;
- database baru yang khusus untuk project tersebut;
- project yang belum mempunyai fitur atau migration bisnis;
- database yang belum mempunyai data penting.

Installer menggunakan `migrate:fresh`. Semua tabel dan seluruh data pada
database yang dipilih di `.env` akan dihapus sebelum schema dibuat ulang.

Jangan menjalankan installer pada project berjalan, database production, atau
database milik aplikasi lain. Update starterkit menggunakan alur update, bukan
instalasi ulang.

## Instalasi

### 1. Siapkan Laravel fresh

Buat Laravel baru. Isi `.env` dengan:

- nama aplikasi;
- URL root aplikasi;
- koneksi ke database baru.

Pastikan database yang dipilih memang boleh di-reset.

### 2. Clone starterkit

Jalankan dari root Laravel:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
```

### 3. Jalankan installer

```bash
php starterkit/installer/install.php --company="Nama Aplikasi"
```

Installer akan memandu developer melalui pertanyaan berikut:

1. Konfirmasi bahwa seluruh tabel dan data boleh di-reset. Hanya jawaban `y`
   yang melanjutkan proses.
2. Kode/subdomain App pertama, misalnya `keuangan` atau `kepegawaian`.
3. Nama App yang tampil pada aplikasi.

Input kode App boleh dikosongkan. Jika dikosongkan, installer tetap menyelesaikan
setup tanpa App pertama. Landing kemudian menampilkan panduan tentang App,
module, menu, submenu, contoh struktur, dan command pembuatannya.

App pertama yang dibuat installer membawa contoh navigasi yang sengaja mudah
diganti: `Contoh Menu` dengan child `Contoh Submenu 1` dan
`Contoh Submenu 2`.

Setelah pertanyaan selesai, installer menangani dependency, connector,
environment, APP key, bahasa, asset, migration, Superuser, landing, registry,
dan pemeriksaan keamanan. Pada Laravel standar tidak ada file yang perlu dicopy
atau diedit manual.

## Login awal

Credential development awal dicantumkan langsung pada `.env` dan
`.env.example` agar tidak membingungkan:

```env
STARTER_SUPERUSER_USERNAME=superuser
STARTER_SUPERUSER_EMAIL=developer@example.test
STARTER_SUPERUSER_PASSWORD=rahasia123
```

Password `rahasia123` hanya untuk local/testing. Sebelum instalasi atau deploy
production, ganti `STARTER_SUPERUSER_PASSWORD` di `.env` dengan password kuat.
Security check menolak password development tersebut pada production.

## Jika instalasi dilewati tanpa App

Project tetap dapat membuka landing, login, profil, pengaturan, manajemen user
dan role, serta log aktivitas. Dashboard bisnis belum tersedia.

Landing onboarding menyediakan petunjuk ringkas. App pertama juga dapat dibuat
kemudian dari root Laravel:

```bash
php artisan starter:make-app layanan --name="Layanan" --no-sync
php artisan starter:sync layanan --dry-run
php artisan starter:sync layanan --force
```

Gunakan `--no-sync` agar source hasil generator dapat diperiksa sebelum
diterapkan ke database.

## Setelah App dibuat

Generator membuat area terisolasi berikut:

```text
app/Livewire/Apps/<App>/
config/apps/<app>.php
routes/apps/<app>.php
resources/views/apps/<app>/
tests/Feature/Apps/<App>/
```

Migration fitur dibuat di:

```text
database/migrations/apps/<app>/
```

Konfigurasi `config/apps/<app>.php` menjadi source of truth untuk nama App,
module, menu, submenu, route, dan halaman awal. `starter:sync` menyamakan
metadata database dengan source tersebut; sync tidak membuat desain navigasi
yang belum didefinisikan di source.

## Update starterkit

Update tidak me-reset database:

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

Periksa hasil dry-run sebelum sync. Migration update wajib menjaga data
existing.

## Deployment shared hosting

Starterkit tidak membutuhkan daemon, reverse proxy, atau konfigurasi server
khusus. Deployment tetap mengikuti Laravel biasa:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan starter:publish-assets
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan starter:security-check --production
php artisan optimize
```

Production wajib menggunakan HTTPS, `APP_DEBUG=false`, credential database yang
benar, password Superuser kuat, dan document root menuju folder `public`.

Config cache/`optimize` hanya dipakai di production. Selama development jangan
mengaktifkan config cache; gunakan `php artisan optimize:clear` jika cache
pernah dibuat.

## Batas penting

- Satu instalasi mewakili satu perusahaan/client.
- Single App dan multi App sama-sama didukung.
- Folder `starterkit` adalah core read-only untuk fitur project.
- Landing dan seluruh fitur bisnis berada di Laravel host.
- Sidebar berasal dari registry App/module/menu, bukan menu Blade manual.
- Tambahan header, dropdown profil, atau layout memakai
  `resources/views/extensions/starter/`.
- Jangan menjalankan `starterkit:install` sebagai update atau deploy rutin.

Jika installer menolak project, target bukan Laravel fresh atau bootstrap sudah
terkustomisasi. Gunakan Laravel baru dan ulangi instalasi pada database yang
bersih.
