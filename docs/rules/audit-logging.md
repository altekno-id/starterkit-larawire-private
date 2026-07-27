# Audit Log Universal

## Tujuan

`starter_logs` menyimpan semua perubahan data oleh user untuk event create, update, dan delete. Read/list/show tidak dicatat. Satu aksi bisnis yang memengaruhi banyak tabel harus memiliki satu `action_id` dengan sequence berbeda.

## Otomatis untuk Eloquent

Listener global di `AppServiceProvider` mencatat event `created`, `updated`, dan `deleted` melalui `AuditLogService`.

- Gunakan Eloquent `save`, `create`, `update`, atau `delete` agar log otomatis terbentuk.
- Model log sendiri dikecualikan.
- Atribut sensitif seperti password, token, secret, dan credential disaring.
- Pastikan user terautentikasi saat aksi web dilakukan.

## Kelompokkan satu aksi bisnis

Gunakan `withinAction()` di service:

```php
return $auditLogs->withinAction(
    'pegawai.create',
    'Menambah pegawai '.$data['name'],
    function () use ($data): Pegawai {
        return DB::transaction(function () use ($data): Pegawai {
            $pegawai = Pegawai::query()->create($data);
            $pegawai->units()->sync($data['unit_ids']);

            return $pegawai;
        });
    },
);
```

Semua event Eloquent di callback memakai `action_id` yang sama dan `sequence` berurutan.

## Perubahan yang tidak memicu event model

Bulk query, pivot `sync`, raw SQL, dan operasi eksternal dapat membutuhkan `recordManual()`. Catat minimal:

- event: `created`, `updated`, atau `deleted`
- table name
- type/id/label objek
- old/new values yang memang berubah
- metadata yang membantu audit

Lihat pemakaian nyata pada `UserManagementRoleService`.

Untuk bulk action, utamakan satu query/chunk yang aman dan satu audit summary dengan jumlah serta scope record. Jangan memaksa event model per-row bila menghasilkan ribuan query/log identik dan tidak menambah nilai audit.

## Event keamanan

- Gunakan `AuditLogService::recordSecurityEvent()` untuk aktivitas autentikasi/session, bukan menulis row log langsung.
- Gunakan `action_key` stabil berawalan `auth.`, event `security`, actor bila sudah terautentikasi, dan target akun bila dapat dikenali dengan aman.
- Catat login berhasil/gagal/dibatasi/dikunci, konfirmasi password berhasil/gagal/dibatasi, lock/unlock, perubahan/reset password, logout, dan penghentian session.
- Metadata hanya memuat alasan aman, counter, atau konteks teknis minimum; credential dan nilai password lama/baru tidak boleh dicatat.
- Tambahkan test untuk success dan failure state penting, termasuk actor/target serta ketiadaan data sensitif.

## Aturan implementasi

- Business service menentukan `action_key` stabil berbentuk `<domain>.<aksi>`.
- Label harus dapat dipahami user Indonesia.
- Bungkus perubahan atomik dengan `DB::transaction()`.
- Jangan memasukkan password, token, isi file, atau payload sensitif ke old/new/metadata.
- Jangan menulis langsung ke `starter_logs` dari feature; gunakan service.
- Tambahkan test yang membuktikan action, actor, table, event, dan grouping bila multi-tabel.
