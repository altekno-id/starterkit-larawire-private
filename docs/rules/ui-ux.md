# UI/UX Livewire dan Template UI

## Source UI

Gunakan template UI aktif yang sudah tersedia. `docs/template/template.md` adalah atlas pencarian lengkap dan tidak mengikat untuk seluruh HTML di `docs/template`; pakai atlas untuk menemukan kandidat lintas konteks, bukan sebagai daftar keputusan desain yang kaku.

Prosedur wajib dan hemat token:

1. Tentukan tujuan halaman, jenis/volume data, status, aksi utama user, serta interaksi yang diperlukan.
2. Cari atlas dengan kombinasi konteks dan komponen menggunakan `rg`; jangan hanya mencari nama komponen yang sudah diasumsikan.
3. Pilih tiga sampai lima kandidat, termasuk alternatif lintas konteks bila relevan; lalu buka hanya satu sampai tiga HTML sumber terdekat.
4. Cari markup/kelas spesifik pada sumber terpilih, bandingkan hierarki informasi dan kepadatan, lalu komposisikan pola template UI yang tepat.
5. Jika kandidat atlas belum cukup, cari `preview/pages`, `shared/includes`, atau dokumentasi template secara terarah.
6. Jangan membaca seluruh `template.md`, `docs/template/tabler-components`, atau membuat katalog tambahan untuk satu kebutuhan halaman.

Contoh pencarian:

```bash
rg -l "modal|modal-dialog" docs/template | head
rg -l "table-responsive|pagination" docs/template | head
rg -l "form-selectgroup|form-check" docs/template | head
rg -n "invoice|table|status" docs/template/template.md
```

## Prinsip

- Setiap halaman wajib memakai contoh/markup template UI aktif terdekat dari `docs/template`; gunakan atlas untuk membandingkan kandidat dan jangan membuat desain atau komponen baru hanya berdasarkan selera.
- Gunakan komponen/markup template UI existing sebelum membuat komponen custom. Bila padanan persis tidak tersedia, komposisikan card, list group, badge, table, dropdown, modal, alert, empty state, dan utility template terdekat—jangan membangun design system baru.
- Layout content fluid sampai Full HD dan dibatasi secara wajar untuk layar sangat besar.
- Halaman harus padat, informatif, mudah dipindai, dan profesional tanpa terasa sesak. Hindari card besar berisi sedikit data, whitespace berlebihan, tombol tersebar tanpa hierarki, serta detail panjang yang tidak mendukung keputusan user.
- Page header menjelaskan konteks; satu aksi utama paling menonjol di header atau area konteks, sedangkan aksi sekunder dikelompokkan secara jelas atau ditempatkan pada dropdown.
- Form kompleks desktop memakai pembagian kolom agar user tidak bolak-balik scroll; mobile harus kembali satu kolom.
- Add dan edit memakai halaman sendiri jika form cukup kompleks.
- Destructive action memakai confirm modal, bukan browser alert.
- Detail sekunder memakai modal ringkas dan proporsional.
- Tabel dipakai untuk banyak data sejenis yang perlu dipindai atau dibandingkan; gunakan pagination database, filter, status badge, aksi per baris, dan kolom yang benar-benar membantu keputusan. Jangan mengubah tabel besar menjadi kumpulan card hanya demi mobile.
- Card ringkas, statistik, list group, atau key-value dipakai untuk ringkasan, sedikit data, dan detail satu entitas. Riwayat/perubahan memakai timeline, tabel log, atau list kronologis sesuai contoh template UI aktif.
- Pilihan sedikit memakai radio, checkbox, select group, atau segmented control; pilihan besar memakai pencarian/autocomplete server-side atau tabel selector, bukan dropdown panjang.
- Prioritaskan identitas, status, waktu penting, angka ringkas, dan aksi pada tampilan awal; detail sekunder dipindahkan ke halaman detail atau modal ringkas.
- Empty state harus memberi penjelasan kondisi dan next action. Status proses wajib memiliki teks/badge selain warna.
- Label UI Bahasa Indonesia; pertahankan istilah familiar: username, password, email, role, module.
- Pesan validasi wajib memakai Bahasa Indonesia dan nama field yang terlihat oleh user; jangan tampilkan nama property internal, nested key, atau label field berbahasa Inggris.
- Sebelum membuat toast, alert, modal konfirmasi/hapus/password, loader, icon, atau pola feedback baru, cari dan gunakan komponen yang sudah tersedia di `resources/views/starter/templates/` bila sesuai kebutuhan.
- Validasi pada `input-group` harus mewarnai border input dan seluruh addon/button sebagai satu control utuh; ikon validasi background pada input di dalam group disembunyikan agar tidak bertabrakan dengan trailing control.
- `input-group` invalid yang sedang fokus wajib mempertahankan border dan focus ring merah; state `focus-within` bawaan template tidak boleh mengembalikannya menjadi biru.
- Pada varian `input-group-flat`, letakkan `invalid-feedback` sebagai sibling setelah penutup `input-group` dengan `d-block`, bukan sebagai child group. Hubungkan input dan pesan memakai `aria-invalid` serta `aria-describedby` agar focus ring tidak membungkus pesan dan rounded corner addon tetap benar.

## Livewire

- Form normal memakai `wire:submit` dan `wire:model.defer`. Validasi, authorization, query, audit, dan persistence berjalan saat submit; jangan memakai `wire:model.live`, autosave, atau validasi server pada setiap input secara default.
- Live request form hanya boleh digunakan untuk kebutuhan yang tidak dapat ditunda—cek unik yang perlu diketahui sebelum submit, dropdown bergantung server, autocomplete, perhitungan harga/stok otoritatif, atau upload/preview file. Request tersebut wajib memiliki alasan UX, scope field sempit, batas input server-side, dan validasi penuh tetap diulang saat submit.
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
- Hindari script tersebar di markup halaman. Simpan CSS/JS custom pada Blade asset yang berdekatan dengan halaman pemilik; gunakan `@assets` untuk dependency dan `@script` untuk inisialisasi Livewire.
- Pilih komponen template UI/Alpine lebih dahulu, lalu library vanilla lokal. Jangan menambah jQuery kecuali library yang diperlukan memang tidak memiliki alternatif layak dan kompatibilitasnya telah dibuktikan.
- Library jQuery harus lokal dan page-scoped: urutan `jQuery → library → inisialisasi`, memakai `defer` tanpa `async`, tidak boleh menjadi asset global starter, dan DOM yang dimanipulasinya harus diisolasi dengan `wire:ignore` bila dikelola Livewire.
- Library tidak boleh mengambil alih router, memanipulasi DOM global, atau memasang listener/observer/timer berulang. Inisialisasi harus idempotent dan membersihkan instance atau listener ketika halaman ditinggalkan bila diperlukan.

## Asset Template

- Asset inti template UI/runtime/Livewire dilayani dari file lokal di `public`; jangan menambah CDN atau dependency network untuk UI dasar.
- CSS utama dimuat di `<head>`, script theme yang mencegah flash warna dijalankan sebelum body dirender, dan JavaScript interaktif dimuat sekali di akhir body dengan `defer` bila memungkinkan. Layout menyediakan stack `page-styles` dan `page-scripts` untuk Blade non-Livewire.
- Gunakan `asset()` dengan cache-busting versi file untuk asset statik. Jangan memuat bundle yang sama lebih dari sekali dalam satu layout.
- Asset custom Livewire mengikuti konvensi `assets/<page>.css.blade.php` dan `assets/<page>.js.blade.php` di samping view pemilik. Jangan menaruhnya di layout atau folder asset global; library vendor tetap lokal pada folder `public/assets/apps/<subdomain>/vendor/`.
- Asset Livewire dipublish ke `public/vendor/livewire` agar production/shared hosting tidak bergantung pada route dinamis asset; publish ulang setelah versi Livewire berubah.
- Production tidak membutuhkan Vite dev server atau proses Node untuk menjalankan template yang sudah dibuild.

## Error Page

- Error `400`, `401`, `403`, `404`, `405`, `408`, `419`, `422`, `429`, `500`, `503`, serta fallback `4xx`/`5xx` memakai layout lokal `resources/views/starter/errors/layout.blade.php` dan desain template UI aktif yang konsisten.
- Error page memakai asset lokal, `noindex`, Bahasa Indonesia, kode status, dan aksi kembali yang aman; jangan tampilkan stack trace, exception message internal, path server, query, atau credential.
- Uji view spesifik/fallback dan minimal satu response exception nyata dengan `APP_DEBUG=false`.

## Verifikasi

Uji dengan browser:

- jalur masuk dari menu, redirect, dan tombol;
- loading, modal, toast, validasi, empty state, status, dan aksi utama;
- data kosong, sedikit, dan banyak untuk membuktikan pemilihan komponen serta kepadatan informasi tepat;
- desktop Full HD, ukuran laptop, dan mobile yang relevan.
- kesesuaian struktur/markup dengan contoh `docs/template` yang dipakai sebagai referensi.
- Untuk perubahan CSS komponen, verifikasi state normal, focus, invalid, serta invalid+focus; periksa struktur DOM/computed style bila screenshot saja belum membuktikan penyebab.
