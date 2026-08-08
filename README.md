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

## Instalasi local/development

Siapkan project Laravel, Git, Composer, dan database. Terminal harus berada di
root project Laravel.

### 1. Pasang submodule

```bash
git submodule add https://github.com/altekno-id/starterkit-larawire-private.git starterkit-larawire-private
```

### 2. Atur domain local

Sesuaikan `APP_URL` dan koneksi database pada `.env`. Variabel starterkit
lainnya akan ditambahkan otomatis oleh installer.

```env
APP_URL=http://namaproject.test
```

### 3. Jalankan installer

```bash
php starterkit-larawire-private/installer/install.php --company="Nama Perusahaan"
```

Installer memasang dependency, menghubungkan starterkit ke Laravel, menyiapkan
environment, migration, asset, data perusahaan, dan akun Superuser.

> **Peringatan database:** installer menjalankan `migrate:fresh`. Seluruh tabel
> dan data pada database yang terhubung akan dihapus. Laravel fresh dilanjutkan
> otomatis setelah pemberitahuan. Laravel yang sudah memiliki code aplikasi
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

Sebelum menjalankan command, buat `.env` production dan isi database, domain,
`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_DOMAIN`, serta password
Superuser yang kuat. Document root domain dan seluruh subdomain harus mengarah
ke folder `public`.

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
