> Starterkit ini dibangun dengan dukungan karya open-source dari
> [Laravel](https://laravel.com/),
> [Laravel Livewire](https://livewire.laravel.com/),
> [Laravel Lang](https://laravel-lang.com/),
> [Livewire PowerGrid](https://livewire-powergrid.com/), dan
> [Tabler](https://tabler.io/admin-template). Terima kasih kepada seluruh author
> dan contributor yang mengembangkan serta memelihara package tersebut.

# Apa itu Starterkit Larawire Private?

Starterkit Larawire Private adalah fondasi untuk membuat aplikasi internal
berbasis **Laravel** dan **Livewire**. Fungsinya mirip Laravel Starter Kit Auth:
developer mendapat autentikasi dan tampilan awal yang siap dipakai, lalu dapat
langsung mengerjakan fitur bisnis.

Starterkit ini juga menyediakan:

- login, logout, lock screen, profil, dan pengaturan keamanan akun;
- pengelolaan user, role, module, dan hak akses;
- satu atau beberapa App/subdomain dalam satu project Laravel;
- menu dan route yang disinkronkan dari kode;
- audit log, konfigurasi perusahaan, dan gateway API opsional;
- UI Tabler, Livewire, dan tabel Livewire PowerGrid;
- installer, setup production, update, migration, dan publish asset;
- rules yang membantu agent AI mengembangkan aplikasi secara konsisten.

Repository ini bukan aplikasi Laravel yang berdiri sendiri. Repository dipasang
sebagai Git submodule bernama `starterkit-larawire-private` di dalam project
Laravel.

## Memahami App, module, route, menu, dan role

Starterkit tetap memakai **satu project Laravel, satu database, dan satu sistem
login**. App hanya membagi fitur berdasarkan area bisnis.

Contoh:

```text
perusahaan.com          Login, profil, user, role, pengaturan, dan log
sales.perusahaan.com    App Sales
hr.perusahaan.com       App HR
```

| Istilah | Artinya |
|---|---|
| **App** | Area bisnis utama, misalnya Sales atau HR. |
| **Subdomain** | Alamat App, misalnya `sales.perusahaan.com`. |
| **Module** | Kelompok fitur sekaligus batas hak akses. |
| **Route** | Alamat halaman atau aksi yang dapat dibuka. |
| **Menu** | Link di sidebar yang menuju sebuah route. |
| **Role** | Kumpulan module yang boleh diakses oleh user. |

Alur hak aksesnya sederhana:

```text
User → Role → Module yang diizinkan → Route yang dapat dibuka
```

Menu hanya berfungsi sebagai navigasi. Keamanan tetap diperiksa oleh route dan
server. Superuser dapat mengakses seluruh module.

### Hubungan file config dan route

Setiap App mempunyai dua file utama dengan nama yang sama:

```text
config/apps/sales.php   Pengaturan App, module, menu, icon, dan landing page
routes/apps/sales.php   Route halaman pada App Sales
```

Config menu memakai array PHP yang fungsinya mirip file JSON. Setiap menu
menunjuk ke nama route pada file route App.

Nama route mengikuti pola:

```text
<app>.<module>.<action>
sales.prospect.index
```

Pada contoh tersebut:

- `sales` adalah App;
- `prospect` adalah module;
- `index` adalah halaman atau aksi.

Kode module pada config harus sama dengan bagian kedua nama route. Setelah
config atau route berubah, `php artisan starter:sync` memvalidasi hubungan
tersebut dan menyimpan metadata App, module, route, serta menu ke database.
Jangan mengubah tabel metadata starterkit secara manual.

## Studi kasus sederhana: aplikasi ERP perusahaan

Misalnya Anda ingin membuat aplikasi ERP internal dengan area Sales, HR,
Gudang, dan Karyawan. Semuanya tetap dibuat dalam **satu project Laravel**.
Area bisnis yang besar dijadikan App, sedangkan fitur di dalamnya dijadikan
module.

Struktur yang mudah dipahami adalah:

```text
ERP perusahaan                                  Satu project dan satu database
├── perusahaan.com                              Login dan pengaturan global
├── sales.perusahaan.com                        App Sales
│   ├── Module Customer
│   └── Module Penjualan
├── hr.perusahaan.com                           App HR
│   ├── Module Karyawan
│   └── Module Absensi
└── gudang.perusahaan.com                       App Gudang
    ├── Module Stok
    └── Module Mutasi Barang
```

Karyawan ditempatkan sebagai module di dalam App HR karena masih satu area
bisnis. Jika Karyawan nantinya harus menjadi area yang benar-benar terpisah,
Anda dapat membuat App `karyawan` dengan subdomain
`karyawan.perusahaan.com`.

Setelah starterkit selesai diinstal, App awal dapat dibuat dengan:

```bash
php artisan starter:make-app sales --name="Sales"
php artisan starter:make-app hr --name="HR"
php artisan starter:make-app gudang --name="Gudang"
```

Jika `APP_URL=http://perusahaan.test`, alamat local App tersebut menjadi
`sales.perusahaan.test`, `hr.perusahaan.test`, dan `gudang.perusahaan.test`.
Arahkan semuanya ke folder `public` project Laravel yang sama melalui aplikasi
web server local yang Anda gunakan.

Setelah itu, isi module dan menu masing-masing App pada `config/apps/`, lalu
buat halaman dan route pada `routes/apps/`. Role Sales dapat diberi module milik
App Sales saja, sedangkan role HR dapat diberi module Karyawan dan Absensi.
Semua user tetap login melalui akun yang sama.

## Instalasi local/development

Yang perlu disiapkan:

1. project Laravel fresh;
2. database baru/kosong dan koneksinya;
3. domain local pada `APP_URL`;
4. Git dan Composer.

Terminal harus berada di root project Laravel.

### 1. Pastikan Laravel masih fresh

Gunakan project Laravel yang baru dibuat. Installer boleh dipakai pada project
yang sudah berisi code, tetapi akan meminta konfirmasi karena seluruh data
database tetap dihapus.

### 2. Pasang submodule

```bash
git submodule add https://github.com/altekno-id/starterkit-larawire-private.git starterkit-larawire-private
```

### 3. Atur database dan APP_URL

Contoh menggunakan MySQL untuk project ERP local:

```env
APP_URL=http://perusahaan.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_local
DB_USERNAME=root
DB_PASSWORD=
```

Pastikan database `erp_local` sudah dibuat. Variabel starterkit lainnya akan
ditambahkan otomatis oleh installer.

### 4. Jalankan installer

```bash
php starterkit-larawire-private/installer/install.php --company="Nama Perusahaan"
```

Installer memasang dependency, menghubungkan starterkit ke Laravel, menyiapkan
environment, migration, asset, data perusahaan, dan akun Superuser.

> **Peringatan database:** installer menjalankan `migrate:fresh`. Seluruh tabel
> dan data pada database yang terhubung akan dihapus. Laravel fresh dilanjutkan
> otomatis setelah pemberitahuan. Laravel yang sudah memiliki kode aplikasi
> akan meminta konfirmasi terlebih dahulu.

Saat installer meminta App pertama, masukkan kode subdomain seperti `sales`
atau kosongkan jika App ingin dibuat nanti.

Login development awal mengikuti nilai berikut pada `.env`:

```env
STARTER_SUPERUSER_USERNAME=superuser
STARTER_SUPERUSER_PASSWORD=superuser123
```

Password default hanya boleh dipakai untuk local/development.

## Membuat App baru

Contoh berikut membuat App Sales pada `sales.<APP_DOMAIN>`:

```bash
php artisan starter:make-app sales --name="Sales"
```

Kode App hanya boleh berisi huruf kecil, angka, dan tanda hubung. Kode `api`
dicadangkan untuk gateway API. Command tersebut membuat dan langsung
menyinkronkan:

```text
config/apps/sales.php
routes/apps/sales.php
routes/apps/sales.api.php
app/Livewire/Apps/Sales/
resources/views/apps/sales/
tests/Feature/Apps/Sales/
```

Setelah App dibuat:

1. Ganti module dan menu contoh pada `config/apps/sales.php`.
2. Buat halaman dan route dengan pola nama `<app>.<module>.<action>`.
3. Letakkan migration bisnis pada `database/migrations/apps/sales/`.
4. Berikan module kepada role melalui Role Management dan pilih landing page
   awal App.
5. Arahkan subdomain ke folder `public` Laravel yang sama, lalu uji role yang
   boleh dan tidak boleh mengakses App.

## Mengambil update starterkit

Jalankan dari root Laravel:

```bash
git submodule update --init --remote starterkit-larawire-private
composer install
php artisan starter:sync
```

Update dan `starter:sync` tidak mereset database atau membuat ulang password
Superuser.

## Menggunakan database baru atau kosong

Jika starterkit sudah terpasang lalu koneksi `.env` diganti ke database baru,
jangan menjalankan installer ulang dan jangan menggunakan opsi reset.

```bash
php artisan starter:setup --company="Nama Perusahaan"
```

`starter:setup` menjalankan migration, membuat data perusahaan dan Superuser,
kemudian menjalankan `starter:sync` secara otomatis.

## Pengembangan dengan agent AI

Installer membuat `AGENTS.md` di root Laravel. File tersebut mengarahkan agent
AI ke rules starterkit agar authorization, validasi, audit, struktur code,
migration, UI, performa, dan testing mengikuti standar yang sama.

Untuk meminta fitur, jelaskan App, module, menu, role, dan kebutuhan bisnisnya.
Contoh:

```text
Buat module Penjualan pada App sales. Menu Penjualan memiliki submenu Pipeline
dan Transaksi. Role Sales dapat mengelola data, sedangkan Manager hanya dapat
melihat laporan.
```

Agent akan memeriksa project, meminta konfirmasi pemahaman, lalu membuat
spesifikasi di folder `issues/` sebelum mengubah kode aplikasi. Prosedur tersebut
berlaku untuk fitur project Laravel, bukan maintenance repository starterkit.

## Gateway API opsional

API tidak aktif secara default. Aktifkan hanya jika aplikasi membutuhkan API:

```env
STARTER_API_ENABLED=true
```

Route API setiap App berada di `routes/apps/<app>.api.php` dan tersedia melalui:

```text
api.<APP_DOMAIN>/<app>
```

Subdomain `api` dan seluruh subdomain App harus diarahkan ke folder `public`
Laravel yang sama.

## Deployment production

### Arahkan semua domain ke project Laravel yang sama

Domain utama dan seluruh subdomain App **tidak memakai project atau folder yang
berbeda**. Semuanya diarahkan ke folder `public` dari satu project Laravel.

Contoh lokasi project di server:

```text
/home/user/erp/
└── public/                  Document root untuk semua domain dan subdomain
```

Contoh pengarahannya:

| Domain | Document root |
|---|---|
| `perusahaan.com` | `/home/user/erp/public` |
| `sales.perusahaan.com` | `/home/user/erp/public` |
| `hr.perusahaan.com` | `/home/user/erp/public` |
| `gudang.perusahaan.com` | `/home/user/erp/public` |
| `api.perusahaan.com` | `/home/user/erp/public` |

Buat DNS setiap subdomain menuju server yang sama, lalu atur document root-nya
melalui web server atau panel hosting. Anda juga dapat memakai wildcard DNS
`*.perusahaan.com` jika didukung penyedia DNS dan hosting.

Siapkan `.env` production sebelum menjalankan setup:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://perusahaan.com
APP_DOMAIN=perusahaan.com
SESSION_DOMAIN=.perusahaan.com
SESSION_SECURE_COOKIE=true
```

Isi juga koneksi database dan `STARTER_SUPERUSER_PASSWORD` dengan password yang
kuat. Jangan commit `.env` production ke Git.

### Deployment pertama

Clone repository Laravel beserta submodule, lalu pasang dependency:

```bash
git clone --recurse-submodules <repository-laravel> <folder-project>
cd <folder-project>
composer install --no-dev --optimize-autoloader
php artisan starter:setup --company="Nama Perusahaan"
```

Gunakan `starter:setup` satu kali jika database production masih baru/kosong.
Command tersebut membuat data perusahaan dan akun Superuser. Jika database
sudah berisi data starterkit hasil restore, gunakan `php artisan starter:sync`.

### Update production berkala

```bash
git pull --recurse-submodules
composer install --no-dev --optimize-autoloader
php artisan starter:sync
```

`starter:sync` menangani migration baru, asset, registry App, storage link, dan
cache production. Tidak perlu menjalankan `migrate`, `storage:link`, atau
`optimize` secara terpisah.

## Hal penting

- Satu instalasi mewakili satu perusahaan/client, bukan SaaS multi-tenant.
- Seluruh App berbagi login, database, user, role, dan pengaturan global.
- Hak akses diberikan melalui module; menu hanya navigasi.
- Fitur bisnis berada di project Laravel, bukan di submodule starterkit.
- Jangan commit `.env`, password, credential database, atau isi `vendor`.
