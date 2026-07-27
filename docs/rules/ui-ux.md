# UI/UX Livewire dan Tabler

## Source UI

Gunakan Tabler yang sudah tersedia. Urutan referensi:

1. Contoh ringkas project: `docs/template/*.html`.
2. Preview lengkap: `docs/template/tabler-components/preview/pages/`.
3. Potongan komponen: `docs/template/tabler-components/shared/includes/`.
4. Dokumentasi komponen: `docs/template/tabler-components/docs/ui/`.

Jangan membuka seluruh folder. Contoh pencarian:

```bash
rg -l "modal|modal-dialog" docs/template | head
rg -l "table-responsive|pagination" docs/template | head
rg -l "form-selectgroup|form-check" docs/template | head
```

## Prinsip

- Gunakan komponen/markup Tabler existing sebelum membuat komponen custom.
- Layout content fluid sampai Full HD dan dibatasi secara wajar untuk layar sangat besar.
- Page header menjelaskan konteks dan aksi utama berada di area konten yang relevan.
- Form kompleks desktop memakai pembagian kolom agar user tidak bolak-balik scroll; mobile harus kembali satu kolom.
- Add dan edit memakai halaman sendiri jika form cukup kompleks.
- Destructive action memakai confirm modal, bukan browser alert.
- Detail sekunder memakai modal ringkas dan proporsional.
- Tabel harus padat tetapi mudah dipindai; detail teknis dipindahkan ke modal.
- Empty state harus memberi penjelasan dan next action.
- Label UI Bahasa Indonesia; pertahankan istilah familiar: username, password, email, role, module.
- Pesan validasi wajib memakai Bahasa Indonesia dan nama field yang terlihat oleh user; jangan tampilkan nama property internal, nested key, atau label field berbahasa Inggris.
- Validasi pada `input-group` harus mewarnai border input dan seluruh addon/button sebagai satu control utuh; ikon validasi background pada input di dalam group disembunyikan agar tidak bertabrakan dengan trailing control.
- `input-group` invalid yang sedang fokus wajib mempertahankan border dan focus ring merah; state `focus-within` bawaan Tabler tidak boleh mengembalikannya menjadi biru.
- Pada varian `input-group-flat`, letakkan `invalid-feedback` sebagai sibling setelah penutup `input-group` dengan `d-block`, bukan sebagai child group. Hubungkan input dan pesan memakai `aria-invalid` serta `aria-describedby` agar focus ring tidak membungkus pesan dan rounded corner addon tetap benar.

## Livewire

- Gunakan `wire:model.blur` atau `wire:model.defer` untuk input biasa; jangan request pada setiap ketikan tanpa kebutuhan.
- Search/filter live memakai debounce sekitar 300–500 ms bila menjalankan query dan tetap dibatasi server-side.
- Action yang mengirim request menampilkan loader global dan blur area melalui `starter-runtime.js`.
- Request perubahan input biasa tidak boleh memunculkan loader action.
- Modal harus ditutup/diselesaikan sebelum loader mengambil fokus agar tidak bertumpuk.
- Feedback sukses/error memakai event toast project, bukan alert.
- Link internal memakai pola navigasi Livewire yang sudah ada.

## JavaScript

Gunakan JavaScript/Alpine hanya untuk interaksi client seperti password visibility, timer auto-lock, atau UI state ringan. Business state, validation, dan authorization tetap di Livewire/server.

- Gunakan Alpine untuk toggle, show/hide, tab visual, dropdown, copy-to-clipboard, preview lokal, dan state presentasi kecil.
- Jangan memakai `wire:click`/public property untuk interaksi yang tidak membutuhkan server.
- Gunakan Livewire hanya ketika perlu membaca/menulis data, validasi server, authorization, transaksi, atau audit log.
- Daftar Livewire yang dapat bertambah mengikuti server-side query/pagination dan batas resource pada `performance.md`.
- Hindari inline script manual yang tersebar; gunakan Alpine atau file JavaScript lokal agar interaksi tetap ringan dan mudah dirawat.

## Asset Template

- Asset inti Tabler/runtime/Livewire dilayani dari file lokal di `public`; jangan menambah CDN atau dependency network untuk UI dasar.
- CSS utama dimuat di `<head>`, script theme yang mencegah flash warna dijalankan sebelum body dirender, dan JavaScript interaktif dimuat sekali di akhir body dengan `defer` bila memungkinkan.
- Gunakan `asset()` dengan cache-busting versi file untuk asset statik. Jangan memuat bundle yang sama lebih dari sekali dalam satu layout.
- Asset Livewire dipublish ke `public/vendor/livewire` agar production/shared hosting tidak bergantung pada route dinamis asset; publish ulang setelah versi Livewire berubah.
- Production tidak membutuhkan Vite dev server atau proses Node untuk menjalankan template yang sudah dibuild.

## Error Page

- Error `400`, `401`, `403`, `404`, `405`, `408`, `419`, `422`, `429`, `500`, `503`, serta fallback `4xx`/`5xx` memakai layout lokal `resources/views/starter/errors/layout.blade.php` dan desain Tabler yang konsisten.
- Error page memakai asset lokal, `noindex`, Bahasa Indonesia, kode status, dan aksi kembali yang aman; jangan tampilkan stack trace, exception message internal, path server, query, atau credential.
- Uji view spesifik/fallback dan minimal satu response exception nyata dengan `APP_DEBUG=false`.

## Verifikasi

Uji dengan browser:

- jalur masuk dari menu, redirect, dan tombol;
- loading, modal, toast, validasi, empty state;
- data sedikit dan data banyak;
- desktop Full HD, ukuran laptop, dan mobile yang relevan.
- Untuk perubahan CSS komponen, verifikasi state normal, focus, invalid, serta invalid+focus; periksa struktur DOM/computed style bila screenshot saja belum membuktikan penyebab.
