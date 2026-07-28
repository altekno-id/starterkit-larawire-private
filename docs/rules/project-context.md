# Konteks Arsitektur Project Turunan

## Tujuan

Project yang dibangun dari starterkit ini adalah aplikasi internal untuk satu perusahaan/client, bukan SaaS. Satu instalasi mewakili satu perusahaan yang tersimpan di `starter_clients`. Tabel bisnis, user, role, dan log tidak memakai `client_id` karena tidak ada tenant selection dalam satu instalasi.

Seluruh file di `docs/rules/` adalah batas arsitektur dan standar implementasi project turunan. Perubahan terhadap prinsip inti hanya dilakukan bila requirement project memang berbeda dan dampaknya disetujui secara eksplisit.

## Prinsip utama

- Multi-subdomain dipertahankan untuk memisahkan kelompok aplikasi.
- Satu login dapat memperoleh akses ke banyak app melalui module yang dimiliki role.
- Hak akses diberikan ke module, bukan permission CRUD per tombol.
- Jika kelompok user membutuhkan tampilan atau flow yang benar-benar berbeda, buat module terpisah dengan route dan view sendiri.
- Semua route milik app harus terdaftar pada module.
- Menu hanya navigasi menuju route module; menu bukan sumber otorisasi.
- Superuser memiliki akses penuh, bersifat sistem, dan hanya terlihat saat login sebagai Superuser.
- Pengaturan dan Log Aktivitas adalah menu global/statik. Keduanya dapat diberikan sebagai capability role.
- Tidak ada register publik, forgot password mandiri, paket, payment, atau tenant SaaS kecuali project client secara eksplisit mengubah scope dan arsitekturnya.

## Stack

- PHP, Laravel, Livewire, serta Pest/PHPUnit mengikuti versi terbaru yang kompatibel dan terkunci pada dependency project.
- Template UI mengikuti aset aktif project; nama, icon set, dan API komponennya harus dibuktikan dari source/template yang tersedia.
- Session dan cache berbasis file
- Queue default `sync`, sesuai target shared hosting

## Data awal

`php artisan starter:setup` menyiapkan tepat satu perusahaan, role sistem Superuser, dan user Superuser. Metadata app disinkronkan oleh `starter:sync`. Data contoh bisnis tidak boleh ditambahkan ke instalasi production.
