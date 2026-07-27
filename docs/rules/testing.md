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
- UI JavaScript: test server metadata/endpoint dan browser test perilaku nyata.
- Shared hosting/config: perintah production seperti `php artisan optimize` diuji hanya saat menyiapkan deployment pada environment production-like; jangan mengaktifkan config cache selama development rutin.
- Security/session: login throttle per akun dan IP, trusted/untrusted host, safe redirect, security header, revokasi session setelah perubahan password, backward compatibility session existing, serta `starter:security-check` local/production.
- Performance: pagination berjalan di database, query tidak bertambah per-row, input search dibatasi, bulk action tidak memuat seluruh tabel, dan layout/daftar kritis memiliki batas atas query bila relevan.
- Guard arsitektur `StarterArchitectureTest` berlaku untuk starterkit dan project turunannya: Livewire/controller tidak boleh memiliki query/load relation/service locator, dan service tidak boleh membangun query model. Jangan menghapus atau melonggarkan guard hanya agar test lulus; deviasi arsitektur wajib memiliki alasan teknis dan persetujuan eksplisit.
- Guard yang sama memastikan file internal starter tetap berada di folder `Starter`, migration starter di `database/migrations/starter`, folder migration app didaftarkan dinamis dari `database/migrations/apps/<subdomain>`, Blade di `resources/views/starter`, route di `routes/starter`, asset di `public/assets/starter`, dan test di `tests/Feature/Starter`.

## Definition of done

- Acceptance criteria pekerjaan terpenuhi untuk perubahan non-trivial.
- Tidak ada TODO/TBD yang tidak dijelaskan.
- Test relevan lulus.
- Pint bersih.
- Sync dry-run diperiksa jika route/config app berubah.
- Browser test dilakukan jika UX berubah.
- Tidak ada perubahan scope, dependency, config production, atau keputusan bisnis yang disisipkan tanpa dicatat/disetujui.
- Tidak ada issue/archive atau dokumentasi planning repository yang dibuat tanpa permintaan eksplisit.
