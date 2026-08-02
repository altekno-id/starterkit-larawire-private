# Deployment Shared Hosting

## Prasyarat

- Folder `<laravel>/starterkit-larawire-private` adalah snapshot source dari
  repository canonical yang dilacak sebagai file biasa oleh repository host.
  Folder ini tidak memiliki `.git` sendiri dan tidak diabaikan. Production
  menerima snapshot melalui pull repository project; autoload/provider/bootstrap
  connector terpasang sesuai README canonical.
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
- `STARTER_THEME=tabler`
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

Deployment pertama:

```bash
composer install --no-dev --optimize-autoloader
php artisan starter:setup --company="Nama Perusahaan"
```

Deployment berikutnya:

```bash
git status --short
# Output wajib kosong. Jika tidak, hentikan deployment dan amankan perubahan server terlebih dahulu.
git pull --ff-only origin master
composer install --no-dev --optimize-autoloader
php artisan starter:sync
```

`composer install` aman dijalankan pada setiap deployment dan wajib ketika
composer.lock host berubah. Update source starterkit yang memperkenalkan
dependency baru harus lebih dahulu diterapkan pada composer.json/lock host
sesuai release note canonical sebelum Artisan dijalankan.

Jangan menjalankan `starter:setup --reset-password` pada deploy rutin.

`starter:setup` dan `starter:sync` mengorkestrasi security check, publish asset,
migration, registry App, asset Livewire, storage link best-effort, serta
`optimize` production. Setup juga membuat APP key hanya bila kosong dan
menyiapkan client/Superuser. Sync update rutin tidak mereset akun/password.

Pada awal proses, sync menghapus cache config dan route yang lama. Pada
environment production yang dibaca langsung dari `.env`, tahap terakhir selalu
menjalankan `optimize` lalu memastikan cache config dan route benar-benar
terbentuk. Karena itu deploy rutin cukup `git pull` dan `starter:sync` tanpa
command cache tambahan. Asset starter dan Livewire hanya dipublikasikan ulang
ketika sumbernya berubah.

Registry App membaca file route host melalui router terisolasi, bukan dari
route collection yang mungkin sudah dimuat dari cache saat Artisan boot. Dengan
demikian penambahan maupun penghapusan route setelah `git pull` diterapkan pada
sync pertama.

Composer `post-autoload-dump` membersihkan cache bootstrap sebelum command sync
dijalankan agar config dan route terbaru dimuat. Jika `starter:sync` atau
`starter:setup` menemukan cache bootstrap lama, command membersihkannya lalu
tetap melanjutkan dalam satu proses; cache production dibangun ulang pada tahap
terakhir. Asset starter hanya disalin kembali ketika isi sumber berubah.

`starter:security-check` tidak mengubah state dan memvalidasi APP key, session encryption, HTTP-only/SameSite cookie, kesesuaian `APP_URL`/`APP_DOMAIN`, serta requirement production seperti HTTPS, debug off, secure cookie, password default, extension `intl`, dan permission runtime.

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
- Di mesin development, tarik `master` repository canonical di luar project,
  salin isinya ke `starterkit-larawire-private/` dengan `rsync --delete` sambil
  mengecualikan `.git/`, lalu verifikasi, commit, dan push repository project.
  Production hanya menarik repository project, kemudian menjalankan Composer
  dan `starter:sync` dari root host.
- Checkout production tidak boleh dipakai untuk edit source. Sebelum pull,
  `git status --short` wajib kosong dan pull wajib memakai `--ff-only`; bila
  salah satu syarat gagal, hentikan deployment. Jangan memakai `git reset`,
  `checkout`, `stash`, merge, atau rebase otomatis di production.
- Migration, asset, registry, security check, dan cache production ditangani
  oleh `starter:sync`.
- Pastikan asset Livewire yang dipublish sesuai versi package setelah update dependency.
- Jangan menyimpan session/cache di lokasi di luar project untuk target shared hosting ini.
