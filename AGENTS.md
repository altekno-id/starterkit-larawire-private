# AI Entry Point

Dokumen ini adalah kontrak eksekusi untuk setiap model AI yang mengembangkan project turunan dari starterkit ini. Jangan membaca seluruh `docs/` pada setiap tugas; baca konteks inti dan rule yang relevan.

## Konteks wajib

- Project private/internal company, bukan SaaS.
- Laravel 13, PHP 8.4, Livewire 4, Pest 4, dan Tabler.
- Hak akses berbasis module: satu role dapat mengakses banyak module; route dan menu mengikuti module.
- Superuser adalah akun sistem tersembunyi bagi non-superuser dan tidak boleh diubah/dihapus.
- Metadata app, module, route, dan menu didefinisikan di source code lalu disinkronkan ke database.
- UI menggunakan Bahasa Indonesia, kecuali istilah familiar seperti username, password, email, role, dan module.
- Selama development jangan aktifkan config cache; gunakan config cache hanya pada deployment production.

Baca [project-context](docs/rules/project-context.md) sekali saat belum mengenal project atau ketika konteks percakapan hilang.

## Kontrak anti-asumsi

- Sebelum merencanakan atau mengubah code, telusuri flow existing dari route/menu → Livewire/controller → service → model/migration → test/config terkait.
- Nyatakan sesuatu sebagai kondisi existing hanya setelah dibuktikan dari code, schema, config, test, atau output command. Jangan mengarang route, tabel, kolom, role, status, config key, integration, atau business rule.
- Pisahkan requirement terkonfirmasi, temuan existing, proposal teknis, dan pertanyaan terbuka. Proposal tidak boleh ditulis seolah-olah keputusan user.
- Jika keputusan bisnis/authorization/data yang tidak dapat ditemukan akan mengubah hasil secara material, hentikan bagian yang bergantung padanya dan minta keputusan; jangan memilih diam-diam.
- Gunakan source of truth existing dan sibling terdekat. Jangan membuat layer, abstraction, helper, config, atau dependency baru bila pola project yang ada sudah menyelesaikan kebutuhan.
- Verifikasi versi framework/package dari `composer.lock`, `package-lock.json`, atau file konfigurasi aktual sebelum memakai API; jangan mengasumsikan dokumentasi versi terbaru cocok dengan dependency terpasang.
- Jika requirement project bertentangan dengan rule inti, jelaskan rule yang terdampak, alasan, risiko, dan perubahan arsitekturnya. Deviasi hanya dilakukan setelah keputusan eksplisit dan rule/context terkait diperbarui pada perubahan yang sama.
- Terapkan perubahan terkecil yang menyelesaikan root cause. Pertahankan perubahan user dan bagian worktree lain yang tidak terkait.
- Jangan menambah package, base directory, service production, daemon, atau konfigurasi web server khusus tanpa kebutuhan terverifikasi dan persetujuan eksplisit.

## Router dokumen

| Jika tugas menyangkut | Baca hanya |
|---|---|
| Planning feature/bug/refactor non-trivial | `docs/rules/issue-workflow.md` |
| Menambah/mengubah feature dalam app | `docs/rules/feature-development.md` |
| App atau subdomain baru | `docs/rules/app-subdomain.md` |
| Route, menu, module, atau role | `docs/rules/access-control.md` |
| Model, create/update/delete, transaksi | `docs/rules/audit-logging.md` |
| Livewire, form, tabel, modal, loader | `docs/rules/ui-ux.md` |
| Konfigurasi, upload, login, lock screen | `docs/rules/security-and-config.md` |
| PHP/Laravel conventions | `docs/rules/code-style.md` |
| Locale, angka, tanggal, atau currency | `docs/rules/localization-and-formatting.md` |
| Pengujian dan definition of done | `docs/rules/testing.md` |
| Shared hosting/deployment | `docs/rules/deployment.md` |
| Hubungan layer dan source of truth | `docs/rules/architecture.md` |

## Cara kerja hemat token

1. Cari file terkait dengan `rg`; baca sibling terdekat sebelum mengubah code.
2. Untuk implementasi issue, baca issue aktif, file yang disebut di dalamnya, dan rule yang relevan.
3. Jangan membaca seluruh `docs/template/tabler-components`. Cari komponen dengan `rg`, lalu buka 1–3 referensi terdekat.
4. Jangan menyalin ulang aturan umum ke issue. Tulis keputusan bisnis dan kontrak implementasi yang spesifik pada pekerjaan tersebut.
5. Jangan membuat dokumentasi baru selain issue yang diwajibkan workflow atau perubahan rule yang memang diminta.

## Aturan eksekusi

- Feature, bugfix, refactor, atau perubahan teknis non-trivial dimulai dari issue berbasis bukti di `docs/issues`; lihat `docs/rules/issue-workflow.md`.
- Perubahan trivial seperti typo/dokumentasi murni dapat langsung dikerjakan bila tidak mengubah business flow, authorization, data, API, atau deployment.
- Jika user meminta langsung eksekusi, buat/update issue lalu lanjut implementasi tanpa menunggu approval tambahan selama tidak ada keputusan material yang belum jelas.
- Jika user hanya meminta planning/review/diagnosis, jangan mengubah code atau state di luar artefak planning yang diminta.
- Gunakan `php artisan make:* --no-interaction` untuk file Laravel.
- Migration wajib aman untuk data production dan data existing; jangan mengandalkan database kosong.
- Setiap perubahan code/perilaku wajib memiliki atau memperbarui test yang relevan; perubahan dokumentasi murni wajib melewati pemeriksaan link dan konsistensi.
- Setelah mengubah PHP, jalankan `vendor/bin/pint --dirty --format agent`.
- Jalankan test terfokus lebih dahulu, lalu suite yang relevan.
- Jangan menghapus test tanpa persetujuan.
