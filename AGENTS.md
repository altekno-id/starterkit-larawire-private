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

## Kontrak default feature

- User/developer cukup menjelaskan kebutuhan bisnis, flow, data, dan role yang relevan. Jangan meminta mereka mengulang standar teknis starterkit.
- Secara otomatis terapkan authorization, validation, proteksi injection/mass-assignment, server-side pagination untuk data yang dapat tumbuh, query efisien, audit log, transaksi, locale/format, pola Livewire/Alpine, UI state, migration production-safe, dan test sesuai rule pemilik.
- Seluruh feature untuk app/subdomain wajib mengikuti struktur `Apps/<Subdomain>` pada layer yang dipakai. Baca `docs/rules/architecture.md` sebelum membuat file feature app baru; jangan mencampurnya dengan folder Starter atau root project.
- Migration feature app wajib berada di `database/migrations/apps/<subdomain>/`, bukan root `database/migrations`; folder tersebut dimuat otomatis saat perintah Artisan migration berjalan.
- Bila sebuah standar tidak relevan, lewati tanpa menambah code seremonial. Bila requirement meminta deviasi, jelaskan risiko dan minta keputusan eksplisit.

## Kontrak anti-asumsi

- Sebelum merencanakan atau mengubah code, telusuri flow existing dari route/menu → Livewire/controller → service → interface/repository → model/migration → test/config terkait.
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
| Menambah/mengubah feature dalam app | `docs/rules/feature-development.md` |
| App atau subdomain baru | `docs/rules/app-subdomain.md` |
| Route, menu, module, atau role | `docs/rules/access-control.md` |
| Model, create/update/delete, transaksi | `docs/rules/audit-logging.md` |
| Livewire, form, tabel, modal, loader | `docs/rules/ui-ux.md` |
| Konfigurasi, upload, login, lock screen | `docs/rules/security-and-config.md` |
| PHP/Laravel conventions | `docs/rules/code-style.md` |
| Query, pagination, cache, bulk action, atau asset | `docs/rules/performance.md` |
| Locale, angka, tanggal, atau currency | `docs/rules/localization-and-formatting.md` |
| Pengujian dan definition of done | `docs/rules/testing.md` |
| Shared hosting/deployment | `docs/rules/deployment.md` |
| Hubungan layer dan source of truth | `docs/rules/architecture.md` |

## Cara kerja hemat token

1. Cari file terkait dengan `rg`; baca sibling terdekat sebelum mengubah code.
2. Untuk implementasi, baca file flow existing dan rule yang relevan; jangan membuat artefak planning di repository kecuali diminta.
3. Jangan membaca seluruh `docs/template/tabler-components`. Cari komponen dengan `rg`, lalu buka 1–3 referensi terdekat.
4. Jangan menyalin ulang aturan umum ke dokumen feature.
5. Jangan membuat issue/archive atau dokumentasi planning di repository kecuali diminta eksplisit.

## Aturan eksekusi

- Perubahan trivial seperti typo/dokumentasi murni dapat langsung dikerjakan bila tidak mengubah business flow, authorization, data, API, atau deployment.
- Jika user hanya meminta planning/review/diagnosis, jangan mengubah code atau state di luar artefak planning yang diminta.
- Gunakan `php artisan make:* --no-interaction` untuk file Laravel.
- Migration wajib aman untuk data production dan data existing; jangan mengandalkan database kosong.
- Setiap perubahan code/perilaku wajib memiliki atau memperbarui test yang relevan; perubahan dokumentasi murni wajib melewati pemeriksaan link dan konsistensi.
- Setelah mengubah PHP, jalankan `vendor/bin/pint --dirty --format agent`.
- Jalankan test terfokus lebih dahulu, lalu suite yang relevan.
- Jangan menghapus test tanpa persetujuan.

## Evolusi rules

- Sebelum mengeksekusi code, nilai apakah instruksi baru dari user/developer bersifat reusable dan cocok menjadi standar starterkit atau project turunannya.
- Jika cocok, minta konfirmasi singkat sebelum eksekusi. Setelah disetujui, perbarui rule pemilik yang paling relevan dalam perubahan yang sama tanpa menunggu permintaan lanjutan.
- Jangan menjadikan keputusan bisnis sekali pakai, detail satu feature, nilai rahasia, atau workaround sementara sebagai rule global.
- Tulis rule secara umum, ringkas, dapat dieksekusi, dan tidak menduplikasi rule lain. Jika bertentangan dengan rule existing, jelaskan konflik dan minta keputusan eksplisit sebelum menggantinya.
