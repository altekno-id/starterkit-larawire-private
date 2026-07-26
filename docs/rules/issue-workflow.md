# Workflow Planning dan Issue

Workflow ini berlaku untuk feature, bugfix, refactor, dan pekerjaan teknis non-trivial pada project yang dibangun dari starterkit.

## Prinsip

Issue adalah kontrak kerja berbasis bukti. Isinya harus cukup lengkap agar model AI atau developer lain dapat mengeksekusi tanpa mengarang keputusan bisnis, struktur data, authorization, atau flow existing.

Sebelum membuat issue, planner wajib:

1. menelusuri flow existing dan file terkait;
2. memeriksa route/config/schema/test yang menjadi source of truth;
3. memisahkan requirement terkonfirmasi, temuan existing, proposal, dan pertanyaan terbuka;
4. menentukan scope, risiko, acceptance criteria, dan command verifikasi.

Jika user hanya meminta planning, berhenti setelah issue siap direview. Jika user meminta langsung eksekusi, lanjutkan implementasi setelah issue dibuat selama tidak ada keputusan material yang belum jelas.

## Lokasi dan nama

Issue aktif:

```text
docs/issues/feature_<slug>.md
docs/issues/bugfix_<slug>.md
docs/issues/refactor_<slug>.md
docs/issues/chore_<slug>.md
```

Gunakan lowercase snake_case. Contoh:

```text
docs/issues/feature_format_tanggal_seperti_migration.md
docs/issues/bugfix_format_tanggal_seperti_migration.md
```

Selesai:

```text
docs/issues/archives/_done_feature_format_tanggal_seperti_migration.md
```

## Isi wajib planning

- Metadata: status, jenis, prioritas, app/module, pembuat, tanggal.
- Ringkasan masalah dan outcome bisnis.
- Scope in dan out.
- Kondisi existing berbasis file/code yang sudah diperiksa.
- User flow dan business rules bernomor.
- Authorization dan role/capability.
- Dampak data: tabel, kolom, index, migration/backfill.
- Kontrak route, Livewire, service, event, dan UI.
- Integrasi menu/config + perintah sync.
- Integrasi audit log, termasuk grouping multi-table.
- File yang dibuat/diubah beserta tanggung jawabnya.
- Urutan implementasi atomik.
- Edge cases dan failure states.
- Test cases konkret.
- Acceptance criteria yang dapat diverifikasi.
- Command verifikasi.
- Risiko, rollback, dan pertanyaan yang benar-benar belum diputuskan.

Setiap klaim kondisi existing harus menunjuk file/code/schema/test atau bukti command. Keputusan yang belum dikonfirmasi harus berlabel proposal atau pertanyaan terbuka. Tidak boleh ada instruksi “buat seperti biasa”, “sesuaikan”, “ikuti kebutuhan”, atau “dll.” pada bagian eksekusi.

## Eksekusi dan monitoring

- `planned`: kontrak kerja sudah lengkap tetapi implementasi belum dimulai.
- `in_progress`: implementasi sedang berjalan; gunakan langsung setelah planning bila user meminta eksekusi.
- `blocked`: ada keputusan material atau dependency eksternal yang benar-benar menghentikan pekerjaan, disertai bukti dan kebutuhan unblock.
- `done`: seluruh acceptance criteria, implementasi, dan verifikasi selesai.
- Checklist dicentang hanya setelah code dan test terkait selesai.
- Jika blocked, tulis penyebab dan bukti; jangan menebak requirement.
- Perubahan scope harus dicatat pada issue.
- Satu issue idealnya menghasilkan satu unit review/commit yang fokus.

## Menutup issue

1. Semua acceptance criteria dan checklist selesai.
2. Isi bagian hasil aktual dan bukti test/browser.
3. Set `status: done` dan tanggal selesai.
4. Pindahkan file ke `docs/issues/archives/`.
5. Tambahkan prefix `_done_`, jangan menghapus riwayat planning.
