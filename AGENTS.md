# AI Entry Point

Dokumen ini adalah pintu masuk singkat. Jangan membaca seluruh `docs/` pada setiap tugas.

## Konteks wajib

- Project private/internal company, bukan SaaS.
- Laravel 13, PHP 8.4, Livewire 4, Pest 4, dan Tabler.
- Hak akses berbasis module: satu role dapat mengakses banyak module; route dan menu mengikuti module.
- Superuser adalah akun sistem tersembunyi bagi non-superuser dan tidak boleh diubah/dihapus.
- Metadata app, module, route, dan menu didefinisikan di source code lalu disinkronkan ke database.
- UI menggunakan Bahasa Indonesia, kecuali istilah familiar seperti username, password, email, role, dan module.

Baca [project-context](docs/rules/project-context.md) sekali saat belum mengenal project atau ketika konteks percakapan hilang.

## Router dokumen

| Jika tugas menyangkut | Baca hanya |
|---|---|
| Planning feature/bug project | `docs/rules/issue-workflow.md` lalu template terkait di `docs/issues/templates/` |
| Menambah/mengubah feature dalam app | `docs/rules/feature-development.md` |
| App atau subdomain baru | `docs/rules/app-subdomain.md` |
| Route, menu, module, atau role | `docs/rules/access-control.md` |
| Model, create/update/delete, transaksi | `docs/rules/audit-logging.md` |
| Livewire, form, tabel, modal, loader | `docs/rules/ui-ux.md` |
| Konfigurasi, upload, login, lock screen | `docs/rules/security-and-config.md` |
| PHP/Laravel conventions | `docs/rules/code-style.md` |
| Pengujian dan definition of done | `docs/rules/testing.md` |
| Shared hosting/deployment | `docs/rules/deployment.md` |
| Hubungan layer dan source of truth | `docs/rules/architecture.md` |

## Cara kerja hemat token

1. Cari file terkait dengan `rg`; baca sibling terdekat sebelum mengubah code.
2. Untuk implementasi issue, baca file issue aktif dan hanya rule yang ditautkan oleh issue itu.
3. Jangan membaca seluruh `docs/template/tabler-components`. Cari komponen dengan `rg`, lalu buka 1–3 referensi terdekat.
4. Jangan menyalin ulang aturan umum ke issue. Tautkan rule, tetapi tulis seluruh keputusan bisnis dan detail implementasi spesifik di issue.
5. Jangan membuat dokumentasi baru kecuali diminta atau diwajibkan workflow issue.

## Aturan eksekusi

- Feature/bug project dimulai dari issue plan di `docs/issues`; lihat `docs/rules/issue-workflow.md`.
- Gunakan `php artisan make:* --no-interaction` untuk file Laravel.
- Jangan tambah dependency atau base directory baru tanpa persetujuan.
- Setiap perubahan wajib memiliki/update test yang relevan.
- Setelah mengubah PHP, jalankan `vendor/bin/pint --dirty --format agent`.
- Jalankan test terfokus lebih dahulu, lalu suite yang relevan.
- Jangan menghapus test tanpa persetujuan.
- Pertahankan perubahan user yang tidak berkaitan.
