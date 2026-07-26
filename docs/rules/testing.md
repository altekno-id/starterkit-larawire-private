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
- Service: happy path, validation/error, transaction, audit log.
- Authorization: role diizinkan, role ditolak, dan perlindungan Superuser.
- Livewire: render, validation, action, redirect, toast/modal event.
- Route/menu/module: route name, middleware, `starter:sync --dry-run`, akses role.
- UI JavaScript: test server metadata/endpoint dan browser test perilaku nyata.
- Shared hosting/config: perintah production seperti `php artisan optimize` diuji hanya saat menyiapkan deployment pada environment production-like; jangan mengaktifkan config cache selama development rutin.

## Definition of done

- Acceptance criteria issue terpenuhi untuk pekerjaan non-trivial.
- Tidak ada TODO/TBD yang tidak dijelaskan.
- Test relevan lulus.
- Pint bersih.
- Sync dry-run diperiksa jika route/config app berubah.
- Browser test dilakukan jika UX berubah.
- Tidak ada perubahan scope, dependency, config production, atau keputusan bisnis yang disisipkan tanpa dicatat/disetujui.
- Issue memuat hasil aktual dan bukti verifikasi lalu dipindah ke archive sesuai workflow.
