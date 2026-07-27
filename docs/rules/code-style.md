# Code Style

## PHP dan Laravel

- Ikuti sibling terdekat dan struktur existing.
- Gunakan strict, descriptive naming; hindari singkatan tidak jelas.
- Semua parameter dan return type harus eksplisit.
- Gunakan constructor property promotion.
- Selalu gunakan curly braces.
- Gunakan PHPDoc untuk array shape/generic yang membantu static understanding; hindari komentar yang hanya mengulang code.
- Gunakan named route dan `route()`, bukan URL hard-coded.
- Gunakan Form Request untuk HTTP controller kompleks; validasi Livewire berada dekat action atau object form.
- Gunakan Eloquent/factory pada test.
- Module bisnis mengikuti dependency dan pembagian tanggung jawab `architecture.md`: query/persistence berada di repository, kontraknya di interface, dan business flow berada di service bila terdapat logic nyata.
- Jangan membuat service kosong, repository CRUD generik, atau interface yang hanya menyalin seluruh method Eloquent.
- Jangan menggunakan `env()` di luar file config.
- Jangan menambah package bila solusi Laravel/existing project mencukupi.

## Livewire

- Full-page component memakai `#[Layout('layouts::app')]`.
- Method action memakai nama bisnis, bukan nama teknis generik.
- Authorize dan validate pada setiap action yang mengubah state.
- Render/query mengikuti `performance.md`: daftar yang dapat bertambah wajib query dan pagination database, bukan pagination Collection.
- Jangan menyimpan object besar atau data sensitif sebagai public property.

## Database

- Tabel domain/bisnis baru wajib memakai pola `{subdomain}_{module}_{entity}` dalam `snake_case` lowercase. Gunakan app key/subdomain dan module key dari registry sebagai source of truth, lalu normalisasi tanda hubung menjadi underscore.
- Nama `entity` merepresentasikan koleksi/plural sesuai bahasa domain; jangan memaksakan akhiran `s` bahasa Inggris pada istilah Indonesia. Contoh: `spmb_pendaftaran_calon_mahasiswa` dan `spmb_pendaftaran_dokumen_persyaratan`.
- Model untuk tabel berpola tersebut wajib mendeklarasikan `$table` secara eksplisit; jangan mengandalkan tebakan pluralisasi Eloquent.
- Pivot memakai pola `{subdomain}_{module}_{entity_a}_{entity_b}` dengan urutan entity yang konsisten/alfabetis. Foreign key tetap memakai `{entity_singular}_id` yang jelas.
- Seluruh nama tabel, foreign key, unique constraint, dan index wajib berada dalam batas identifier database—maksimal 64 karakter untuk MySQL. Bila nama berisiko panjang, pendekkan key app/module/entity secara jelas dan beri nama index eksplisit; jangan memakai singkatan ambigu.
- Prefix `starter_` tetap khusus infrastruktur starterkit. Rule prefix domain hanya berlaku untuk tabel feature/project baru; tabel existing tidak boleh di-rename sekadar menyeragamkan nama tanpa scope eksplisit dan migration production-safe.
- Migration infrastruktur starter hanya berada di `database/migrations/starter`. Migration tabel feature wajib berada di `database/migrations/apps/<subdomain>/`; jangan membuat migration feature baru di root `database/migrations` atau folder module. Loader starter menemukan folder subdomain valid secara otomatis saat Artisan migration berjalan.
- Sampai generator khusus tersedia, buat model dan migration app sebagai dua perintah terpisah agar path migration tidak salah:

  ```bash
  php artisan make:model Apps/Spmb/Mahasiswa --no-interaction
  php artisan make:migration create_spmb_pendaftaran_mahasiswa_table \
    --create=spmb_pendaftaran_mahasiswa \
    --path=database/migrations/apps/spmb \
    --no-interaction
  ```

- Jangan memakai `php artisan make:model NamaModel -m` untuk tabel app karena Laravel akan menaruh migration-nya di root. Nama file migration harus unik secara global dan menjelaskan tabel yang diubah, walaupun foldernya sudah dipisahkan per app.
- Migration bersifat reversibel dan aman terhadap data existing.
- Baseline migration starter boleh dikonsolidasikan hanya sebelum starter digunakan pada production, atau saat reset database telah disetujui eksplisit. Setelah ada instalasi/data production, jangan mengubah atau menghapus migration baseline yang sudah dirilis; tambahkan migration baru yang production-safe.
- Anggap setiap migration akan berjalan pada tabel production yang sudah besar dan berisi data.
- Penambahan kolom wajib memilih nullable/default yang kompatibel, lalu backfill bertahap sebelum constraint diperketat.
- Rename/drop/type change wajib memiliki analisis kompatibilitas, backup/rollback, dan tidak boleh menghapus data existing secara diam-diam.
- Hindari operasi table rewrite atau backfill besar dalam satu transaksi bila berisiko timeout pada shared hosting.
- Migration tidak boleh bergantung pada model yang dapat berubah; gunakan query builder/schema dengan nilai eksplisit.
- Foreign key/index disesuaikan pola query.
- Input untuk sort/filter/raw expression wajib memakai allowlist dan parameter binding; jangan interpolasi input ke SQL.
- Jangan menambah `client_id`; satu instalasi hanya satu perusahaan.
- Gunakan transaksi untuk aksi yang harus atomik.

## Format dan generator

```bash
php artisan make:<type> ... --no-interaction
vendor/bin/pint --dirty --format agent
```

Jangan membuat script verifikasi sementara jika Pest dapat membuktikan perilaku yang sama.

Selama development jangan menjalankan `config:cache` atau `optimize`. Setelah mengubah `.env`/config lokal gunakan `php artisan optimize:clear` bila state pernah tercache.
