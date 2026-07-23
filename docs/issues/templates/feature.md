# Feature: <Nama>

## Metadata

| Field | Nilai |
|---|---|
| Status | planned |
| Prioritas | <low/medium/high/critical> |
| App / Module | <app> / <module> |
| Planner | <nama/model> |
| Dibuat | YYYY-MM-DD |
| Diperbarui | YYYY-MM-DD |

## Outcome

<Hasil bisnis yang harus dirasakan user.>

## Scope

### In

- <cakupan>

### Out

- <bukan cakupan>

## Kondisi Existing

- `<path:line>` — <temuan faktual>.

## User Flow

1. <langkah>

## Business Rules

1. <aturan eksplisit>

## Authorization

- Role/module/capability:
- Perilaku 403/hidden:

## Desain Data

### Tabel/migration

- <tabel, kolom, tipe, nullable/default, index/FK>

### Backfill/kompatibilitas

- <strategi>

## Kontrak Implementasi

### Route dan menu

- Route name/path/middleware:
- Config app/module/menu:
- Landing:

### Backend/Livewire

- Class/method/state/validation:
- Service dan transaction boundary:

### UI/UX

- Layout, component Tabler, loading, modal, empty/error state:
- Responsive behavior:

### Audit Log

- `action_key` / label:
- Model event otomatis:
- Manual log/grouping:
- Data sensitif yang dilarang:

## File Plan

| File | Aksi | Tanggung jawab |
|---|---|---|
| `<path>` | create/modify | <detail> |

## Urutan Eksekusi

- [ ] 1. <langkah atomik>
- [ ] 2. <langkah atomik>
- [ ] 3. Jalankan sync metadata bila route/config berubah.
- [ ] 4. Jalankan test dan verifikasi UI.

## Edge Cases dan Failure States

- <kasus> → <perilaku>

## Test Cases

- [ ] <given/when/then>

## Acceptance Criteria

- [ ] <hasil yang dapat diverifikasi>

## Command Verifikasi

```bash
php artisan starter:sync <app> --dry-run
vendor/bin/pint --dirty --format agent
php artisan test --compact <test-path>
```

## Risiko dan Rollback

- Risiko:
- Mitigasi:
- Rollback:

## Pertanyaan Terbuka

- <kosongkan bila tidak ada; pertanyaan blocking harus diputuskan sebelum eksekusi>

## Hasil Aktual

Diisi executor saat selesai:

- Ringkasan:
- Perubahan scope:
- Test:
- Browser:
- Selesai pada:
