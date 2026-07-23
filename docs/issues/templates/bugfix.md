# Bugfix: <Nama>

## Metadata

| Field | Nilai |
|---|---|
| Status | planned |
| Prioritas | <low/medium/high/critical> |
| App / Module | <app> / <module> |
| Planner | <nama/model> |
| Dibuat | YYYY-MM-DD |
| Diperbarui | YYYY-MM-DD |

## Gejala dan Dampak

- Gejala:
- User terdampak:
- Frekuensi:
- Expected:
- Actual:

## Bukti dan Reproduksi

1. <langkah reproduksi deterministik>

- Error/log/screenshot:
- `<path:line>` — <temuan code>.

## Root Cause

<Jelaskan sebab teknis yang terverifikasi, bukan hanya lokasi error.>

## Scope

### In

- <cakupan perbaikan>

### Out

- <bukan cakupan>

## Desain Perbaikan

- Perubahan:
- Mengapa memperbaiki root cause:
- Kompatibilitas data/flow existing:
- Authorization/security:
- Audit log:
- UI loading/modal/error state:

## File Plan

| File | Aksi | Tanggung jawab |
|---|---|---|
| `<path>` | create/modify | <detail> |

## Urutan Eksekusi

- [ ] 1. Buat regression test yang gagal pada kondisi lama.
- [ ] 2. Terapkan causal fix.
- [ ] 3. Uji semua entry point yang memakai flow bersama.
- [ ] 4. Jalankan test dan verifikasi UI.

## Regression dan Edge Cases

- <kasus> → <hasil>

## Test Cases

- [ ] Reproduksi lama sekarang lulus.
- [ ] Happy path existing tetap lulus.
- [ ] Authorization dan failure state relevan lulus.

## Acceptance Criteria

- [ ] <hasil terukur>

## Command Verifikasi

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact <test-path>
```

## Risiko dan Rollback

- Risiko:
- Mitigasi:
- Rollback:

## Hasil Aktual

Diisi executor saat selesai:

- Root cause final:
- Ringkasan fix:
- Test:
- Browser:
- Selesai pada:
