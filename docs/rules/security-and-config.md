# Security dan Konfigurasi Dinamis

## Konfigurasi

- Konfigurasi runtime yang boleh diubah admin disimpan di `starter_configs`.
- Akses selalu melalui `StarterConfigService`; jangan query tabel langsung dari feature.
- Default aman didefinisikan di service untuk kondisi sebelum migration/table tersedia.
- Perubahan config harus menghapus cache key terkait dan tercatat di audit log.
- Rahasia dan setting environment/infrastruktur tetap di `.env` + `config/*.php`, bukan database.
- Setiap penambahan atau perubahan environment key untuk development lokal wajib diterapkan ke `.env` dan dicerminkan pada `.env.example` dalam perubahan yang sama. `.env.example` hanya memuat placeholder/default aman dan penjelasan yang diperlukan, tidak pernah nilai rahasia dari `.env`.
- Jangan meminta developer menambahkan environment key secara manual bila perubahan tersebut dapat diterapkan langsung pada checkout lokal. Untuk production, dokumentasikan nilai atau pola nilainya melalui `.env.example` dan rule deployment.

Konfigurasi existing:

- Remember me aktif/tidak.
- Lock screen aktif/tidak dan timeout menit.
- Batas percobaan login dan decay.
- Maksimum upload image.

## Menambah config

1. Tambahkan row melalui migration/seeding yang idempotent.
2. Tambahkan fallback bertipe pada `StarterConfigService`.
3. Tambahkan accessor/clamp bila nilainya memiliki batas keamanan.
4. Tambahkan field UI Pengaturan bila boleh diubah admin.
5. Gunakan config tersebut di seluruh consumer.
6. Tambahkan validation, audit log, cache invalidation, dan test.

## Session dan lock screen

- Session dan cache memakai driver `file` untuk satu project/shared hosting.
- `SESSION_DOMAIN=null` adalah default untuk arsitektur ini. `config/session.php` otomatis memakai `.<APP_DOMAIN>` saat `APP_DOMAIN` bukan `localhost`, sehingga cookie session tersedia pada root domain dan seluruh app subdomain.
- Isi `SESSION_DOMAIN` secara eksplisit hanya jika deployment membutuhkan scope cookie yang berbeda dari `APP_DOMAIN`; jangan menduplikasi `.<APP_DOMAIN>` tanpa kebutuhan khusus.
- Setiap session login menyimpan `starter.auth_version` yang harus sama dengan `starter_client_logins.auth_version`. Session existing tanpa key hanya boleh diadopsi saat versi database masih initial `1`, sehingga deployment migration tidak memutus seluruh user tetapi reset password berikutnya tetap mencabut session lama.
- Setiap perubahan atau reset password wajib menaikkan `auth_version`, merotasi `remember_token`, dan mempertahankan hanya session pelaku yang baru memverifikasi password. Session perangkat lain dihentikan server-side pada request berikutnya.
- Remember me mengikuti config dinamis.
- Middleware `starter.lock` menjadi pertahanan server.
- Runtime JavaScript mengunci otomatis tanpa reload penuh dan menyentuh session secara throttled saat ada aktivitas browser.
- Timeout minimal 60 detik dan maksimal 24 jam.
- Unlock memerlukan password, rate-limited, dan kembali ke URL aman sebelumnya.

## Upload

- Ambil limit dari `StarterConfigService::uploadImageMaxKilobytes()`.
- Validasi MIME, ukuran, dan jenis file di server.
- Temporary upload memiliki absolute ceiling 10 MB dan validasi feature boleh lebih ketat. Gambar profil/logo dibatasi maksimal 4096×4096 piksel untuk melindungi resource shared hosting.
- Simpan nama file generated; jangan percaya nama/path dari user.
- Jangan menyediakan field URL/path string untuk foto, avatar, atau logo. Form hanya mengirim file upload/reset intent; path file existing dan hasil penyimpanan baru tetap dimiliki server.
- Jangan merender URL eksternal atau path arbitrary dari data legacy. Nilai existing boleh dipertahankan agar data production tidak dihapus diam-diam, tetapi UI hanya menggunakan path storage yang sesuai prefix kepemilikan record.
- Penghapusan file lama hanya boleh memakai path yang berasal dari record server dan sudah diverifikasi berada pada storage/direktori yang diizinkan.
- Preview/logo harus memakai `object-fit: contain` agar rasio tidak merusak layout.

## Baseline

- CSRF wajib untuk action web.
- Security header sederhana diterapkan middleware global tanpa Content Security Policy.
- Response terautentikasi, login, konfirmasi password, dan lock screen memakai `Cache-Control: no-store`.
- Trusted hosts memakai middleware Laravel dan diturunkan otomatis dari `APP_URL`, termasuk seluruh app subdomain. `APP_URL` host wajib sama dengan `APP_DOMAIN`.
- Redirect lintas subdomain hanya menerima HTTP/HTTPS, host root/subdomain yang dipercaya, tanpa userinfo, dan tanpa port yang menyimpang dari `APP_URL`.
- Redirect wajib memakai scheme yang sama dengan `APP_URL` agar production HTTPS tidak dapat diturunkan ke HTTP.
- HSTS hanya dikirim pada production HTTPS.
- Login failure dibatasi per kombinasi username/IP dan secara agregat per IP agar rotasi username tidak memenuhi cache atau melewati throttle. Username tidak disimpan mentah pada cache key, dan akun tidak dikenal tetap menjalankan password hash check untuk mengurangi timing enumeration.
- Login password yang berhasil dihitung sebagai konfirmasi terbaru agar user tidak diminta mengulang password pada navigasi pertama.
- Area pengaturan sensitif wajib memakai middleware `password.confirm`, menerima login password yang baru berhasil sebagai konfirmasi terbaru, dan meminta verifikasi ulang setelah timeout.
- Security event mengikuti `audit-logging.md`.
- Production: `APP_DEBUG=false`, HTTPS, cookie secure, permission storage minimal.
- `php artisan starter:security-check` wajib lulus sebelum deployment production. `starter:setup` menjalankannya otomatis pada environment production.
- Jangan log password, token, secret, credential, atau isi file.
- Password/credential pada state Livewire wajib memiliki batas panjang dan dibersihkan setelah action berhasil maupun gagal; jangan mempertahankannya untuk kenyamanan form.
- Semua input dianggap tidak tepercaya: validasi tipe/panjang/enum, gunakan authorization pada action, allowlist mass assignment, escape output, dan gunakan binding Eloquent/query builder. Raw SQL/HTML hanya boleh memakai nilai internal yang sudah dibuktikan aman.
