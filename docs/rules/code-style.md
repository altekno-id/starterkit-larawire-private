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
- Gunakan service untuk business flow dan transaksi lintas model.
- Jangan menggunakan `env()` di luar file config.
- Jangan menambah package bila solusi Laravel/existing project mencukupi.

## Livewire

- Full-page component memakai `#[Layout('layouts::app')]`.
- Method action memakai nama bisnis, bukan nama teknis generik.
- Authorize dan validate pada setiap action yang mengubah state.
- Render/query harus menghindari N+1 dan pagination untuk data besar.
- Jangan menyimpan object besar atau data sensitif sebagai public property.

## Database

- Migration bersifat reversibel dan aman terhadap data existing.
- Foreign key/index disesuaikan pola query.
- Jangan menambah `client_id`; satu instalasi hanya satu perusahaan.
- Prefix `starter_` dikhususkan untuk infrastruktur starterkit.
- Gunakan transaksi untuk aksi yang harus atomik.

## Format dan generator

```bash
php artisan make:<type> ... --no-interaction
vendor/bin/pint --dirty --format agent
```

Jangan membuat script verifikasi sementara jika Pest dapat membuktikan perilaku yang sama.
