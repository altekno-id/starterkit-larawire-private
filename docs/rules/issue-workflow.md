# Workflow Planning dan Issue

Workflow ini berlaku saat starterkit dipakai untuk membangun project client.

## Prinsip

Feature, bugfix, refactor, atau pekerjaan teknis non-trivial dimulai dengan planning oleh model AI berkemampuan tinggi. Hasil planning adalah kontrak kerja yang cukup detail untuk dieksekusi programmer junior atau model AI murah tanpa menebak keputusan penting.

Jika user hanya memberi prompt seperti “Tambahkan fitur A dengan flow berikut…”, planner harus:

1. memeriksa code dan data flow existing;
2. membuat file issue;
3. menjelaskan keputusan, risiko, dan acceptance criteria;
4. menunggu persetujuan sebelum implementasi, kecuali user secara eksplisit meminta langsung eksekusi.

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

Tidak boleh ada instruksi “buat seperti biasa”, “sesuaikan”, atau “dll.” pada bagian eksekusi.

## Eksekusi dan monitoring

- Executor mengubah `status` dari `planned` → `in_progress`.
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

Template berada di `docs/issues/templates/`.
