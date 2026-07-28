# Localization dan Format Nilai

## Locale

- Project memakai `laravel-lang/common`; jangan membuat ulang terjemahan framework yang sudah tersedia.
- Bahasa aplikasi mengikuti `APP_LOCALE`, fallback mengikuti `APP_FALLBACK_LOCALE`, dan Faker mengikuti `APP_FAKER_LOCALE`.
- Default project adalah `id`, `id`, dan `id_ID`.
- Setelah update dependency bahasa, jalankan `php artisan lang:update --no-interaction` dan review perubahan sebelum commit.
- Label bisnis tetap ditulis jelas dalam Bahasa Indonesia; istilah familiar seperti username, password, email, role, dan module boleh dipertahankan.

## Angka dan Currency

- Gunakan `Altekno\StarterKit\Support\Starter\StarterNumber`, bukan `number_format()` tersebar di view.
- Format angka Indonesia memakai titik untuk ribuan dan koma untuk pecahan: `1.234` dan `1.234,5`.
- Currency default adalah IDR dan harus menampilkan simbol/kode sesuai locale; pecahan currency ditampilkan dua digit hanya bila nilai memang memiliki pecahan.
- Nilai database, input API, kalkulasi, dan validation tetap menggunakan tipe numerik mentah; format locale hanya pada presentation boundary.
- Parsing input berformat locale harus dilakukan eksplisit dan diuji; jangan cast string `1.234,5` langsung ke float.

## Tanggal dan Waktu

- Simpan timestamp database dalam format native dan tampilkan sesuai timezone `APP_TIMEZONE`.
- Gunakan translator/formatter locale pada UI; jangan menyimpan nama hari/bulan hasil format ke database.

## Test Minimum

- Locale mengikuti env/config aplikasi.
- Angka bulat, angka pecahan, nilai negatif, dan currency diuji.
- Formatter tidak mengubah nilai persistence atau business calculation.
