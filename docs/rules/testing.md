# Testing dan Definition of Done

## Wajib

Setiap perubahan code harus menambah atau memperbarui test. Mayoritas test adalah Pest feature test.

Urutan:

1. Jalankan test file/filter terkecil selama iterasi.
2. Jalankan seluruh test area yang terdampak.
3. Jalankan Pint setelah perubahan PHP.
4. Untuk perubahan lintas infrastruktur, jalankan full suite.

```bash
php artisan test --compact tests/Feature/NamaTest.php
php artisan test --compact --filter="nama perilaku"
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

## Matriks sesuai perubahan

- Migration/model: schema, cast, relation, constraint, rollback relevan.
- Repository/interface: kontrak typed, scope query, pagination/filter/sort, eager loading, dan batas query/N+1.
- Service: happy path, validation/error, transaction, audit log.
- Authorization: role diizinkan, role ditolak, dan perlindungan Superuser.
- Livewire: render, validation, action, redirect, toast/modal event.
- Route/menu/module: route name, middleware, `starter:sync --dry-run`, akses role.
- API: uji switch mati tidak mendaftarkan gateway, switch aktif mendaftarkan
  `api.<APP_DOMAIN>/<app>`, nama route `api.<app>.*`, middleware/rate limit,
  authentication/authorization/validation endpoint, serta isi OpenAPI Scramble.
  Jika CORS memang disetujui, uji origin yang diizinkan dan ditolak.
- UI JavaScript: test server metadata/endpoint dan browser test perilaku nyata. Pastikan asset halaman tidak termuat pada halaman lain, form normal tidak mengirim request ketika mengetik, dan navigasi masuk → pindah → kembali tidak menggandakan script, listener, modal, dropdown, atau instance library.
- Navigasi subdomain: uji kondisi guest dan user yang sudah login dari landing,
  auth, serta App. Pastikan URL yang dapat berpindah origin melakukan full-page
  navigation tanpa request `fetch`/Livewire Navigate dan tanpa membutuhkan CORS.
- UI halaman: uji data kosong, sedikit, dan banyak sesuai relevansi. Pastikan pola komponen mengikuti contoh terdekat di `docs/template/<theme>`, informasi utama mudah dipindai, status tidak hanya dibedakan oleh warna, dan desktop/mobile tetap proporsional.
- PowerGrid: uji datasource Builder, filter setiap kolom relevan, search,
  sorting allowlist, pagination database, theme adapter aktif, checkbox halaman,
  selected/by-filter scope, reset selection, action individual/massal, serta
  query budget. Setiap kolom tanpa filter harus memiliki alasan yang diuji.
- Shared hosting/config: perintah production seperti `php artisan optimize` diuji hanya saat menyiapkan deployment pada environment production-like; jangan mengaktifkan config cache selama development rutin.
- Security/session: login throttle per akun dan IP, trusted/untrusted host, safe redirect, security header, revokasi session setelah perubahan password, backward compatibility session existing, serta `starter:security-check` local/production.
- Performance: pagination berjalan di database, query tidak bertambah per-row, input search dibatasi, bulk action tidak memuat seluruh tabel, dan layout/daftar kritis memiliki batas atas query bila relevan.
- Lifecycle CRUD/table: uji create, edit/update, arsip tanpa kehilangan row database, filter arsip, pulihkan, hard delete hanya dari arsip, cascade seluruh relasi milik target, serta perlindungan data shared/reference.
- Bulk action: uji checkbox halaman aktif, selected IDs tervalidasi, arsip/pulihkan/hard delete massal, aksi by-filter dengan search/filter/sort aktif, filter kosong/all-data guard, pagination besar, audit summary, transaksi/rollback, dan dialog metadata yang menjelaskan scope serta dampaknya.
- Guard arsitektur `StarterArchitectureTest` berlaku untuk starterkit dan project turunannya: Livewire/controller tidak boleh memiliki query/load relation/service locator, dan service tidak boleh membangun query model. Jangan menghapus atau melonggarkan guard hanya agar test lulus; deviasi arsitektur wajib memiliki alasan teknis dan persetujuan eksplisit.
- Test integrasi host wajib memastikan snapshot terlacak `starterkit-larawire-private` terdeteksi sebagai embedded source, tidak memiliki repository `.git` sendiri, namespace core dapat di-autoload, migration core dimuat dari `starterkit-larawire-private/database/migrations/starter`, folder migration app didaftarkan dinamis dari `database/migrations/apps/<subdomain>`, theme aktif terdaftar, route/auth/view core tersedia, serta asset starter/theme/PowerGrid berhasil dipublish.
- Repository starterkit tidak memiliki Laravel test harness sendiri. Regression core dijalankan melalui suite Laravel host; jangan menambahkan shell Laravel hanya untuk menjalankan test dari root canonical.

## Definition of done

- Acceptance criteria pekerjaan terpenuhi untuk perubahan non-trivial.
- Tidak ada TODO/TBD yang tidak dijelaskan.
- Test relevan lulus.
- Pint bersih.
- Sync dry-run diperiksa jika route/config app berubah.
- Browser test dilakukan jika UX berubah.
- Perubahan core starterkit telah menjadi commit terfokus dan dipush pada
  repository canonical sebelum disinkronkan; integrasi pada project pengguna
  juga diverifikasi, di-commit, dan dipush.
- Tidak ada perubahan scope, dependency, config production, atau keputusan bisnis yang disisipkan tanpa dicatat/disetujui.
- Request feature/perubahan feature/bug telah dikonfirmasi di chat sebelum
  memiliki tepat satu file
  `issues/<feature|bug>_<slug>_<YYYY_MM_DD_HHMMSS>.md`.
- File issue disetujui sebelum implementasi, cukup detail untuk programmer
  junior/model hemat, dan tidak memiliki keputusan material yang dibiarkan
  implisit.
- Setelah implementasi serta test selesai, file yang sama telah dipindahkan ke
  `issues/archives/done_<nama-file-asli>.md`; pekerjaan yang belum tuntas tetap
  berada di root `issues/`.
