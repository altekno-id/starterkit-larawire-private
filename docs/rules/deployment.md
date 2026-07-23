# Deployment Shared Hosting

## Prasyarat

- PHP 8.4 dan extension Laravel tersedia.
- Document root diarahkan ke folder `public`.
- Root domain dan setiap subdomain app diarahkan ke instalasi yang sama.
- `storage/` dan `bootstrap/cache/` writable.
- Database MySQL tersedia.

## Environment utama

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<domain>`
- `APP_DOMAIN=<domain tanpa scheme>`
- database credential production
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`
- `SESSION_SECURE_COOKIE=true` pada HTTPS
- `STARTER_SUPERUSER_PASSWORD` wajib password kuat saat setup/reset

Cookie lintas subdomain harus diuji pada domain nyata. Gunakan `SESSION_DOMAIN` yang sesuai bila session perlu dibagi antara root, auth, dan app subdomain.

## Urutan deploy

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan starter:setup --company="Nama Perusahaan"
php artisan starter:sync --force
php artisan storage:link
php artisan optimize
```

Jangan menjalankan `starter:setup --reset-password` pada deploy rutin.

## Verifikasi

- `/up` sehat.
- root landing, auth subdomain, dan setiap app subdomain dapat dibuka.
- login, remember me, auto-lock/unlock, logout.
- role/module/menu sesuai hasil sync.
- upload logo dan file berjalan.
- log create/update/delete terbentuk.
- scheduler/cron ditambah hanya jika feature membutuhkannya.

## Update

- Backup database dan file upload sebelum migration berisiko.
- Jalankan migration dan sync sesudah source code terpasang.
- Jalankan `php artisan optimize` kembali.
- Jangan menyimpan session/cache di lokasi di luar project untuk target shared hosting ini.
