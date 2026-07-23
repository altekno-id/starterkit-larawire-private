# Konteks Project

## Tujuan

Starterkit ini adalah fondasi aplikasi internal perusahaan/client, bukan SaaS. Satu instalasi mewakili satu perusahaan yang tersimpan di `starter_clients`. Tabel bisnis, user, role, dan log tidak memakai `client_id` karena tidak ada tenant selection dalam satu instalasi.

## Prinsip utama

- Multi-subdomain dipertahankan untuk memisahkan kelompok aplikasi.
- Satu login dapat memperoleh akses ke banyak app melalui module yang dimiliki role.
- Hak akses diberikan ke module, bukan permission CRUD per tombol.
- Jika kelompok user membutuhkan tampilan atau flow yang benar-benar berbeda, buat module terpisah dengan route dan view sendiri.
- Semua route milik app harus terdaftar pada module.
- Menu hanya navigasi menuju route module; menu bukan sumber otorisasi.
- Superuser memiliki akses penuh, bersifat sistem, dan hanya terlihat saat login sebagai Superuser.
- Pengaturan dan Log Aktivitas adalah menu global/statik. Keduanya dapat diberikan sebagai capability role.
- Tidak ada register publik, forgot password mandiri, paket, payment, atau tenant SaaS.

## Stack

- PHP 8.4
- Laravel 13
- Livewire 4
- Pest 4 / PHPUnit 12
- Tabler UI
- Session dan cache berbasis file
- Queue default `sync`, sesuai target shared hosting

## Data awal

`php artisan starter:setup` menyiapkan tepat satu perusahaan, role sistem Superuser, dan user Superuser. Metadata app disinkronkan oleh `starter:sync`. Data contoh bisnis tidak boleh ditambahkan ke instalasi awal.
