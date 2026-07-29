# Deployment Shared Hosting

## Prasyarat

- Pada mode clone, folder `<laravel>/starterkit` tersedia dari repository core
  dan autoload/provider/bootstrap connector telah terpasang sesuai `README.md`
  root starterkit.
- PHP yang kompatibel dengan dependency project serta extension Laravel tersedia.
- Extension `intl` tersedia untuk format angka/currency berbasis locale.
- Document root diarahkan ke folder `public`.
- Root domain dan setiap subdomain app diarahkan ke instalasi yang sama.
- `storage/` dan `bootstrap/cache/` writable.
- Database MySQL tersedia.

## Environment utama

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<domain>`
- `APP_DOMAIN=<domain tanpa scheme>`
- `STARTER_API_ENABLED=false` kecuali gateway API memang akan dipakai
- database credential production
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`
- `SESSION_SECURE_COOKIE=true` pada HTTPS
- `STARTER_SUPERUSER_PASSWORD` wajib password kuat saat setup/reset
- `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, dan `APP_FAKER_LOCALE=id_ID`

Biarkan `SESSION_DOMAIN=null` untuk deployment normal. Konfigurasi session otomatis memakai `.<APP_DOMAIN>`, sehingga cookie dapat digunakan oleh root, auth, dan app subdomain. Override `SESSION_DOMAIN` hanya bila deployment membutuhkan scope cookie yang berbeda. Cookie lintas subdomain tetap wajib diuji pada domain nyata.

Jika API diaktifkan, arahkan DNS/vhost `api.<APP_DOMAIN>` ke folder `public`
yang sama seperti root dan App lain. Tidak ada konfigurasi Laravel tambahan:
route App API dan Scramble mengikuti switch environment tersebut. Dokumentasi
production tetap memerlukan login Superuser.

## Urutan deploy

```bash
composer install --no-dev --optimize-autoloader
php artisan starter:security-check
php artisan starter:publish-assets
php artisan migrate --force
php artisan starter:setup --company="Nama Perusahaan"
php artisan starter:sync --dry-run
php artisan starter:sync --force
php artisan storage:link
php artisan livewire:publish --assets --no-interaction
php artisan optimize
```

Jangan menjalankan `starter:setup --reset-password` pada deploy rutin.

`starter:security-check` tidak mengubah state dan memvalidasi APP key, session encryption, HTTP-only/SameSite cookie, kesesuaian `APP_URL`/`APP_DOMAIN`, serta requirement production seperti HTTPS, debug off, secure cookie, password default, extension `intl`, dan permission runtime. Pada production, `starter:setup` juga menjalankan check ini otomatis.

`php artisan optimize` hanya untuk production. Selama development jangan memakai `config:cache`; gunakan config langsung agar perubahan `.env` terbaca dan jalankan `php artisan optimize:clear` bila cache pernah dibuat.

Project harus tetap shared-hosting friendly: utamakan middleware/config Laravel dan asset statik existing; jangan menambah kebutuhan service, daemon, reverse proxy, atau konfigurasi web server khusus tanpa requirement feature dan persetujuan eksplisit.

## Audit dependency berkala

- User/developer menjalankan audit dependency secara manual dan berkala, terutama sebelum release atau setelah perubahan dependency:

  ```bash
  composer audit --locked --no-dev --format=summary --no-interaction
  ```

- Audit bukan langkah otomatis setiap feature, test rutin, atau deploy biasa agar tidak menambah kebutuhan network maupun token.
- AI hanya menjalankannya bila user meminta, pada security review, atau ketika dependency diubah. Jika ada temuan, laporkan ringkas package/advisory yang terdampak; jangan mengubah atau memperbarui dependency tanpa persetujuan eksplisit.

## Verifikasi

- `/up` sehat.
- root landing, auth subdomain, dan setiap app subdomain dapat dibuka.
- bila API aktif: root `api.<APP_DOMAIN>` hanya dapat dibuka sesuai policy
  dokumentasi, `/openapi.json` valid, dan endpoint setiap App memakai prefix
  `/<app>`.
- login, remember me, auto-lock/unlock, logout.
- session perangkat lama terputus setelah perubahan/reset password.
- role/module/menu sesuai hasil sync.
- upload logo dan file berjalan.
- log create/update/delete terbentuk.
- scheduler/cron ditambah hanya jika feature membutuhkannya.

## Update

- Backup database dan file upload sebelum migration berisiko.
- Pull `master` di folder clone starterkit, jalankan `composer dump-autoload`, lalu `starter:publish-assets` sebelum migration/test.
- Jalankan migration dan sync sesudah source code terpasang.
- Jalankan `php artisan optimize` kembali.
- Pastikan asset Livewire yang dipublish sesuai versi package setelah update dependency.
- Jangan menyimpan session/cache di lokasi di luar project untuk target shared hosting ini.
