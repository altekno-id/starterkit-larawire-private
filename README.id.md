[English](README.md) | [Bahasa Indonesia](README.id.md)

# Starterkit Larawire Private

> Dibangun dengan dukungan karya open-source dari
> [Laravel](https://laravel.com/),
> [Laravel Livewire](https://livewire.laravel.com/),
> [Laravel Lang](https://laravel-lang.com/),
> [Livewire PowerGrid](https://livewire-powergrid.com/),
> [Tabler](https://tabler.io/admin-template),
> [Dashcode](https://codeshaper.net/), dan
> [Scramble](https://scramble.dedoc.co/). Terima kasih kepada seluruh author
> dan contributor yang mengembangkan serta memelihara project tersebut.

## A. Tentang

### Apa itu Starterkit Larawire Private?

Starterkit Larawire Private adalah fondasi untuk membuat aplikasi internal
perusahaan menggunakan **Laravel** dan **Livewire**. Fungsinya mirip Laravel
Starter Kit untuk autentikasi, tetapi sudah dilengkapi lebih banyak kebutuhan
umum aplikasi bisnis internal.

Setelah instalasi, Anda sudah mendapatkan:

- login dengan username atau email, logout, lock screen, dan keamanan akun;
- pengelolaan user, role, module, dan hak akses dinamis;
- beberapa App dan subdomain bisnis dalam satu project Laravel;
- route dan menu berbasis kode yang disinkronkan ke database;
- pengaturan perusahaan dan log aktivitas;
- tema Tabler dan Dashcode dengan layout vertical dan horizontal;
- tabel server-side menggunakan Livewire PowerGrid;
- API Gateway opsional beserta dokumentasi Scramble;
- command untuk instalasi, setup production, update, publish asset, dan
  migration; serta
- rules siap pakai untuk membantu agent AI mengembangkan aplikasi turunannya.

Repository ini **bukan aplikasi Laravel yang dapat berjalan sendiri**.
Repository dipasang sebagai Git submodule bernama
`starterkit-larawire-private` di dalam project Laravel. Fitur bisnis dibuat di
project Laravel, sedangkan core yang dapat digunakan ulang berada di submodule.

Starterkit ini dirancang untuk satu perusahaan atau client pada setiap
instalasi. Starterkit ini bukan sistem SaaS multi-tenant.

### Teknologi yang digunakan

| Teknologi | Kegunaan |
|---|---|
| Laravel | Framework aplikasi, routing, database, validasi, dan keamanan. |
| Livewire | Halaman dan form interaktif tanpa membuat SPA terpisah. |
| Livewire PowerGrid | Tabel bisnis server-side, pencarian, filter, sorting, dan pagination. |
| Laravel Lang | Terjemahan bahasa Indonesia untuk validasi dan framework Laravel. |
| Tabler | Tema visual pertama dengan navigasi vertical dan horizontal. |
| Dashcode | Tema visual kedua dengan navigasi vertical dan horizontal. |
| Scramble | Dokumentasi dan OpenAPI untuk API Gateway opsional. |
| Git submodule | Memisahkan versi core starterkit dari aplikasi Laravel. |

Tema aktif hanya mengubah tampilan. Data, fitur, authorization, dan perilakunya
tetap sama, sedangkan setiap tema tetap menggunakan komponen dan gaya visual
bawaannya sendiri.

### Konsep dasar: App, subdomain, module, route, menu, dan role

Satu instalasi tetap memakai **satu project Laravel, satu database, dan satu
sistem login**. App hanya membagi project tersebut menjadi area bisnis yang
jelas.

| Istilah | Arti sederhananya |
|---|---|
| **App** | Area bisnis utama, misalnya Sales atau HR. |
| **Subdomain** | Alamat untuk membuka App, misalnya `sales.perusahaan.com`. |
| **Module** | Kelompok fitur sekaligus batas utama hak akses. |
| **Route** | URL dan aksi yang membuka suatu halaman. |
| **Menu** | Link navigasi yang menuju ke route. |
| **Role** | Kumpulan module yang boleh diakses oleh user. |

Alur hak aksesnya adalah:

```text
User -> Role -> Module yang diizinkan -> Route yang dapat dibuka
```

Menu membantu user berpindah halaman, tetapi menu bukan pengaman. Middleware
route pada server tetap memeriksa akses berdasarkan module milik role user.

### Contoh: aplikasi ERP internal

Misalnya Anda ingin membuat ERP internal untuk Sales, HR, Gudang, dan Karyawan.
Anda tidak perlu membuat empat project Laravel. Semuanya dapat berada dalam
satu project, lalu area bisnis yang besar dipisahkan menjadi App:

```text
ERP internal                                  Satu project dan satu database
├── perusahaan.com                            Login dan pengelolaan global
├── sales.perusahaan.com                      App Sales
│   ├── Module Customer
│   └── Module Sales Order
├── hr.perusahaan.com                         App HR
│   ├── Module Karyawan
│   └── Module Absensi
└── gudang.perusahaan.com                     App Gudang
    ├── Module Stok
    └── Module Mutasi Barang
```

Karyawan menjadi module di dalam App HR karena masih berada dalam area bisnis
yang sama. Jika Karyawan nantinya menjadi area sistem yang benar-benar terpisah,
Anda dapat membuat App `karyawan` pada `karyawan.perusahaan.com`.

Semua user login melalui `perusahaan.com`. User Sales dapat diberi module
Customer dan Sales Order saja. User HR dapat diberi module Karyawan dan
Absensi. Manager dapat diberi module dari beberapa App sekaligus.

Semua domain dan subdomain diarahkan ke folder **`public` yang sama** dari
project Laravel yang sama.

### Cara kerja App di dalam source code

Buat App baru dari root project Laravel:

```bash
php artisan starter:make-app sales --name="Sales"
```

Command tersebut membuat contoh yang sudah dapat digunakan dan langsung
menyinkronkannya. File yang paling penting adalah:

```text
config/apps/sales.php
routes/apps/sales.php
routes/apps/sales.api.php
app/Livewire/Apps/Sales/
resources/views/apps/sales/
database/migrations/apps/sales/
tests/Feature/Apps/Sales/
```

Urutan development sebuah App biasanya seperti ini:

1. Atur App, module, menu, icon, dan halaman awal di
   `config/apps/sales.php`.
2. Buat web route di `routes/apps/sales.php` dengan pola nama
   `<app>.<module>.<action>`.
3. Buat class Livewire di `app/Livewire/Apps/Sales/<Module>/`.
4. Buat Blade view di `resources/views/apps/sales/<module>/`.
5. Simpan migration bisnis di `database/migrations/apps/sales/`.
6. Tambahkan test di `tests/Feature/Apps/Sales/`.
7. Jalankan `php artisan starter:sync` setelah mengubah config App, module,
   menu, atau route.

Contohnya:

```text
kode module di config: sales_order
nama route:            sales.sales_order.index
```

Bagian kedua nama route harus sama dengan kode module. Menu di config menunjuk
ke nama route tersebut. Saat sinkronisasi, starterkit memeriksa hubungan ini
lalu menyimpan registry App, module, route, dan menu ke database. Jangan
mengubah tabel registry starterkit secara manual.

### Cara mengatur hak akses role secara dinamis

Struktur aplikasi dibuat melalui kode, tetapi hak akses dapat dikelola secara
dinamis melalui halaman **Pengaturan -> Roles**:

1. Buat atau edit role.
2. Pilih module yang boleh diakses oleh role.
3. Pilih halaman pertama untuk setiap App yang diberikan kepada role.
4. Jika diperlukan, berikan akses global ke Pengaturan atau Log Aktivitas.
5. Berikan role tersebut kepada user melalui Manajemen User.

Administrator tidak perlu mengubah source code ketika mengganti role atau hak
akses module milik user. Akses baru langsung dibaca dari database. Namun, jika
developer menambah atau menghapus module, route, atau menu melalui kode,
jalankan `starter:sync` terlebih dahulu agar struktur terbaru tersedia di
Manajemen Role.

Superuser bawaan adalah akun sistem yang dilindungi dan memiliki akses penuh.
User lain hanya mendapat akses melalui role yang diberikan kepadanya.

### Tema dan layout navigasi

Pilih tema dan layout melalui `.env`:

```env
STARTER_THEME=tabler
STARTER_LAYOUT=vertical
```

Tema yang tersedia adalah `tabler` dan `dashcode`. Keduanya mendukung layout
`vertical` dan `horizontal`. Jika config sebelumnya pernah di-cache, jalankan:

```bash
php artisan optimize:clear
```

### Mengembangkan aplikasi dengan agent AI

Installer menghubungkan `AGENTS.md` di project Laravel dengan rules milik
starterkit. Saat meminta fitur bisnis, jelaskan App, module, bentuk menu, alur
bisnis, dan role yang berkaitan. Contoh:

```text
Buat module Sales Order pada App sales. Menu-nya memiliki Pipeline dan
Transaksi. User Sales dapat mengelola data, sedangkan Manager hanya dapat
melihat laporan.
```

Rules membantu menjaga authorization, validasi, audit log, migration, UI,
performa, dan testing. Rules development project tersebut tidak menghalangi
maintenance langsung pada core starterkit.

## B. Instalasi

Semua command pada bagian ini dijalankan dari **root project Laravel**, bukan
dari dalam submodule starterkit.

### 1. Instalasi local pertama kali

Siapkan terlebih dahulu:

1. project Laravel fresh;
2. database baru atau kosong beserta koneksinya di `.env`;
3. domain local pada `APP_URL`; dan
4. Git dan Composer.

Contoh nilai `.env`:

```env
APP_URL=http://perusahaan.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_local
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian pasang submodule dan jalankan installer:

```bash
git submodule add https://github.com/altekno-id/starterkit-larawire-private.git starterkit-larawire-private
php starterkit-larawire-private/installer/install.php --company="Nama Perusahaan"
```

Installer otomatis menambahkan variabel environment starterkit lainnya,
memasang dependency, menghubungkan starterkit ke Laravel, menjalankan migration,
publish asset, membuat data perusahaan, dan membuat akun Superuser.

Saat instalasi, Anda dapat memasukkan kode App pertama seperti `sales` atau
mengosongkannya lalu membuat App nanti menggunakan `starter:make-app`. Jika App
dibuat, pastikan subdomain local-nya mengarah ke folder `public` Laravel yang
sama dengan `APP_URL`.

> **Peringatan database:** instalasi pertama menjalankan `migrate:fresh` yang
> menghapus seluruh tabel dan data pada database aktif. Struktur Laravel fresh
> dilanjutkan setelah menampilkan pemberitahuan. Jika code aplikasi sudah ada,
> installer menampilkan temuannya dan meminta konfirmasi sebelum mengubah apa
> pun.

Form login menerima username atau email Superuser yang tersimpan di `.env`.
Password development yang dibuat hanya boleh digunakan di local; gunakan
`STARTER_SUPERUSER_PASSWORD` yang kuat sebelum production.

### 2. Instal ulang di local

Pilih kondisi yang sesuai dengan kebutuhan Anda.

#### Menggunakan database baru atau kosong pada project yang sama

Ubah koneksi database di `.env`, lalu jalankan:

```bash
php artisan starter:setup --company="Nama Perusahaan"
```

Ini adalah cara efisien setelah berpindah dari SQLite ke MySQL atau mengganti
ke database kosong lainnya. Command menjalankan migration, membuat data
perusahaan dan Superuser, lalu menyinkronkan App yang sudah ada. Jangan jalankan
installer ulang untuk kondisi ini.

#### Mereset penuh database local yang sedang digunakan

```bash
php starterkit-larawire-private/installer/install.php --reset --company="Nama Perusahaan"
```

`--reset` hanya diizinkan ketika `APP_ENV` bernilai `local` atau `development`.
Installer meminta dua kali konfirmasi, menjalankan `migrate:fresh`, dan membuat
ulang data starter tanpa menghapus source code dan upload. Jangan pernah
menjalankannya di production.

### 3. Sinkronisasi struktur App di local

Setelah mengubah `config/apps/*.php`, `routes/apps/*.php`, module, menu, atau
halaman awal, jalankan:

```bash
php artisan starter:sync
```

Command ini menjalankan migration yang belum diterapkan, publish asset yang
dibutuhkan, memvalidasi struktur App, serta menyinkronkan metadata App, module,
route, dan menu. Command tidak mereset database dan tidak mengganti password
Superuser yang sudah ada.

Command `starter:make-app` sudah otomatis menyinkronkan App yang baru dibuat.

### 4. Update starterkit di local

Jalankan dari root project Laravel:

```bash
git submodule update --init --remote starterkit-larawire-private
composer install
php artisan starter:sync
```

Repository Laravel akan menampilkan pointer submodule yang berubah sebagai Git
change. Review lalu commit dan push perubahan tersebut bersama project Laravel
agar environment lain menggunakan versi starterkit yang sama.

### 5. Deployment production dengan cepat

Domain utama, seluruh subdomain App, dan subdomain API opsional harus diarahkan
ke folder `public` Laravel yang sama:

| Alamat | Document root |
|---|---|
| `perusahaan.com` | `/home/user/erp/public` |
| `sales.perusahaan.com` | `/home/user/erp/public` |
| `hr.perusahaan.com` | `/home/user/erp/public` |
| `api.perusahaan.com` (opsional) | `/home/user/erp/public` |

Isi koneksi database production dan setidaknya nilai environment berikut
sebelum setup:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://perusahaan.com
APP_DOMAIN=perusahaan.com
SESSION_DOMAIN=.perusahaan.com
SESSION_SECURE_COOKIE=true
STARTER_SUPERUSER_PASSWORD=gunakan-password-yang-kuat
```

Jangan commit file `.env` production.

#### Deployment production pertama kali

```bash
git clone --recurse-submodules <repository-laravel> <folder-project>
cd <folder-project>
composer install --no-dev --optimize-autoloader
php artisan starter:setup --company="Nama Perusahaan"
```

Gunakan `starter:setup` jika database production masih baru atau kosong. Jika
database starterkit berasal dari restore, jalankan
`php artisan starter:sync` sebagai gantinya.

#### Update production berkala

```bash
git pull --recurse-submodules
composer install --no-dev --optimize-autoloader
php artisan starter:sync
```

`starter:sync` menangani migration yang belum dijalankan, publish asset,
registry App, storage link, dan cache production. Command `migrate`,
`storage:link`, atau `optimize` tidak perlu dijalankan secara terpisah.

Sebelum update production, pastikan worktree bersih dan backup database serta
file upload jika release memiliki migration yang berisiko.

## C. API Gateway Opsional

Lewati bagian ini jika aplikasi tidak membutuhkan API. Gateway tidak aktif
secara default dan tidak mendaftarkan endpoint maupun dokumentasi API selama
dinonaktifkan.

### Mengaktifkan gateway

Ubah nilai berikut di `.env`:

```env
STARTER_API_ENABLED=true
```

Jika config pernah di-cache, jalankan `php artisan optimize:clear` di local atau
`php artisan starter:sync` saat deployment.

Setiap App memiliki route API sendiri di:

```text
routes/apps/<app>.api.php
```

Untuk App Sales, tulis path relatif terhadap App karena domain dan prefix App
ditambahkan otomatis oleh starterkit. Contoh ini menganggap Laravel Sanctum
sudah dipasang dan dikonfigurasi oleh aplikasi:

```php
Route::get('/orders', SalesOrderIndexController::class)
    ->middleware('auth:sanctum')
    ->name('orders.index');
```

Endpoint tersebut menjadi:

```text
https://api.perusahaan.com/sales/orders
```

Ketentuan shared API Gateway:

- arahkan `api.perusahaan.com` ke folder `public` Laravel yang sama;
- simpan code API di file `.api.php` milik App terkait;
- pilih dan konfigurasi metode autentikasi API yang dibutuhkan aplikasi;
  gateway tidak membuat token API secara otomatis;
- berikan authentication dan authorization yang jelas pada setiap endpoint
  bisnis;
- gunakan API middleware dan rate limiting bawaan;
- jangan masukkan route API ke menu web atau sinkronisasi role-module;
- biarkan CORS tertutup kecuali ada frontend tepercaya yang benar-benar
  membutuhkannya; dan
- jangan gunakan wildcard origin untuk endpoint yang memakai autentikasi.

Scramble menyediakan dokumentasi API di `https://api.perusahaan.com/` dan
OpenAPI di `https://api.perusahaan.com/openapi.json`. Pada production,
dokumentasi hanya dapat dibuka oleh Superuser yang sudah login.

## Hal penting

- Satu instalasi mewakili satu perusahaan/client, bukan banyak tenant SaaS.
- Seluruh App berbagi login, database, user, role, dan pengaturan global.
- Module memberikan hak akses; menu hanya menyediakan navigasi.
- Fitur bisnis berada di project Laravel, bukan di submodule starterkit.
- Jangan commit `.env`, password, credential database, token, atau `vendor`.
