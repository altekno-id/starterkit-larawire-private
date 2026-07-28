# Dokumentasi Developer dan AI

Dokumentasi ini dibuat berlapis agar AI atau developer tidak perlu memuat seluruh konteks project.

- `../AGENTS.md`: pintu masuk dan router bacaan.
- `installation-git-clone.md`: mulai dari clone sampai siap dipakai, daftar
  perubahan otomatis pada Laravel host, alternatif manual, extension contract,
  troubleshooting, dan update starterkit.
- `rules/`: aturan kecil berdasarkan topik.
- `template/`: referensi template UI; cari komponen yang dibutuhkan, jangan dibaca seluruhnya.

Source code tetap menjadi sumber kebenaran terakhir. Jika dokumentasi berbeda dengan code yang berjalan, verifikasi code dan perbarui dokumentasi dalam perubahan yang sama.

Repository starterkit bukan aplikasi Laravel standalone. Semua contoh command Laravel dijalankan dari root project host.

Untuk instalasi standar cukup:

```bash
git clone https://github.com/altekno-id/starterkit-larawire-private-tabler.git starterkit
php starterkit/install.php --company="Nama Aplikasi"
```

Command bootstrap tersebut diperlukan sekali karena sebelum autoload dipasang,
Laravel belum dapat menemukan command milik starterkit. Setelah selesai,
`php artisan starterkit:install` tersedia dan dapat dijalankan ulang.
