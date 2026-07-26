# Security dan Konfigurasi Dinamis

## Konfigurasi

- Konfigurasi runtime yang boleh diubah admin disimpan di `starter_configs`.
- Akses selalu melalui `StarterConfigService`; jangan query tabel langsung dari feature.
- Default aman didefinisikan di service untuk kondisi sebelum migration/table tersedia.
- Perubahan config harus menghapus cache key terkait dan tercatat di audit log.
- Rahasia dan setting environment/infrastruktur tetap di `.env` + `config/*.php`, bukan database.

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
- Remember me mengikuti config dinamis.
- Middleware `starter.lock` menjadi pertahanan server.
- Runtime JavaScript mengunci otomatis tanpa reload penuh dan menyentuh session secara throttled saat ada aktivitas browser.
- Timeout minimal 60 detik dan maksimal 24 jam.
- Unlock memerlukan password, rate-limited, dan kembali ke URL aman sebelumnya.

## Upload

- Ambil limit dari `StarterConfigService::uploadImageMaxKilobytes()`.
- Validasi MIME, ukuran, dan jenis file di server.
- Simpan nama file generated; jangan percaya nama/path dari user.
- Jangan menyediakan field URL/path string untuk foto, avatar, atau logo. Form hanya mengirim file upload/reset intent; path file existing dan hasil penyimpanan baru tetap dimiliki server.
- Penghapusan file lama hanya boleh memakai path yang berasal dari record server dan sudah diverifikasi berada pada storage/direktori yang diizinkan.
- Preview/logo harus memakai `object-fit: contain` agar rasio tidak merusak layout.

## Baseline

- CSRF wajib untuk action web.
- Security header sederhana diterapkan middleware global tanpa Content Security Policy.
- HSTS hanya dikirim pada production HTTPS.
- Login password yang berhasil dihitung sebagai konfirmasi terbaru agar user tidak diminta mengulang password pada navigasi pertama.
- Area pengaturan sensitif wajib memakai middleware `password.confirm`, menerima login password yang baru berhasil sebagai konfirmasi terbaru, dan meminta verifikasi ulang setelah timeout.
- Security event mengikuti `audit-logging.md`.
- Production: `APP_DEBUG=false`, HTTPS, cookie secure, permission storage minimal.
- Jangan log password, token, secret, credential, atau isi file.
