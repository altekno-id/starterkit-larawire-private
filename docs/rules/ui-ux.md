# UI/UX Livewire dan Template UI

## Source UI

Gunakan template UI aktif yang sudah tersedia. `docs/template/<theme>/template.md` adalah atlas pencarian lengkap dan tidak mengikat untuk theme aktif; pakai atlas untuk menemukan kandidat lintas konteks, bukan sebagai daftar keputusan desain yang kaku.

Prosedur wajib dan hemat token:

1. Tentukan tujuan halaman, jenis/volume data, status, aksi utama user, serta interaksi yang diperlukan.
2. Cari atlas dengan kombinasi konteks dan komponen menggunakan `rg`; jangan hanya mencari nama komponen yang sudah diasumsikan.
3. Pilih tiga sampai lima kandidat, termasuk alternatif lintas konteks bila relevan; lalu buka hanya satu sampai tiga HTML sumber terdekat.
4. Cari markup/kelas spesifik pada sumber terpilih, bandingkan hierarki informasi dan kepadatan, lalu komposisikan pola template UI yang tepat.
5. Jika kandidat atlas belum cukup, cari `preview/pages`, `shared/includes`, atau dokumentasi template secara terarah.
6. Jangan membaca seluruh atlas atau seluruh source template untuk satu kebutuhan halaman.

Contoh pencarian:

```bash
rg -l "modal|modal-dialog" docs/template/tabler | head
rg -l "table-responsive|pagination" docs/template/tabler | head
rg -l "form-selectgroup|form-check" docs/template/tabler | head
rg -n "invoice|table|status" docs/template/tabler/template.md
```

## Prinsip

- Layout shell, dropdown akun, auth, lock screen, dan error page dimiliki starterkit. Project tidak boleh mengubah atau meng-copy view core untuk menambahkan feature.
- Extension global project hanya melalui path tetap: `extensions/starter/header-actions/index.blade.php`, `extensions/starter/profile-menu/index.blade.php`, `extensions/starter/layout/head.blade.php`, dan `extensions/starter/layout/body-end.blade.php`. File tersebut hanya ada di repository project bila diperlukan.
- `header-actions` untuk aksi global top bar dan wajib mendukung variable `$compact`; `profile-menu` untuk aksi global terkait user/project sebelum Logout. Navigasi module tetap melalui registry menu/sidebar, bukan raw Blade extension.
- Extension wajib mengotorisasi visibility dan action sendiri, memakai named route, serta mengikuti komponen/markup template UI aktif. Jangan memakai extension untuk memodifikasi state, urutan, atau security control milik starterkit.
- Setiap halaman wajib memakai contoh/markup template UI aktif terdekat dari `docs/template/<theme>`; gunakan atlas untuk membandingkan kandidat dan jangan membuat desain atau komponen baru hanya berdasarkan selera.
- Gunakan komponen/markup template UI existing sebelum membuat komponen custom. Bila padanan persis tidak tersedia, komposisikan card, list group, badge, table, dropdown, modal, alert, empty state, dan utility template terdekat—jangan membangun design system baru.
- Layout content fluid sampai Full HD dan dibatasi secara wajar untuk layar sangat besar.
- Halaman harus padat, informatif, mudah dipindai, dan profesional tanpa terasa sesak. Hindari card besar berisi sedikit data, whitespace berlebihan, tombol tersebar tanpa hierarki, serta detail panjang yang tidak mendukung keputusan user.
- Page header menjelaskan konteks; satu aksi utama paling menonjol di header atau area konteks, sedangkan aksi sekunder dikelompokkan secara jelas atau ditempatkan pada dropdown.
- Form kompleks desktop memakai pembagian kolom agar user tidak bolak-balik scroll; mobile harus kembali satu kolom.
- Add dan edit memakai halaman sendiri jika form cukup kompleks.
- Destructive action memakai confirm modal, bukan browser alert.
- Detail sekunder memakai modal ringkas dan proporsional.
- Tabel dipakai untuk banyak data sejenis yang perlu dipindai atau dibandingkan; gunakan pagination database, filter, status badge, aksi per baris, dan kolom yang benar-benar membantu keputusan. Jangan mengubah tabel besar menjadi kumpulan card hanya demi mobile.
- Setiap tabel pada project Livewire wajib diimplementasikan dengan Livewire
  PowerGrid. Gunakan Builder sebagai datasource agar pencarian, filter, sorting,
  dan pagination tetap server-side. Collection hanya boleh untuk dataset kecil,
  statis, dan terverifikasi tidak akan tumbuh serta bukan tabel manajemen.
- Setiap kolom data wajib memiliki filter yang sesuai tipe dan domainnya.
  Checkbox, aksi, nomor urut presentasi, nilai composite yang tidak dapat
  difilter dengan benar, serta kolom yang tidak membantu keputusan boleh tanpa
  filter; alasan pengecualian wajib dicatat pada code/test feature.
- PowerGrid wajib memakai custom theme adapter milik template aktif. Class,
  markup, asset, filter, pagination, checkbox, empty/loading state, action, dan
  modal harus konsisten dengan template aktif. Jangan mengubah view vendor atau
  memakai theme PowerGrid yang tidak sesuai hanya agar tabel cepat tampil.
- Card ringkas, statistik, list group, atau key-value dipakai untuk ringkasan, sedikit data, dan detail satu entitas. Riwayat/perubahan memakai timeline, tabel log, atau list kronologis sesuai contoh template UI aktif.
- Pemilihan tabel atau card mengikuti pekerjaan user, bukan diterapkan seragam. Tabel manajemen yang datanya dapat bertambah wajib menyediakan pencarian, pagination database, jumlah hasil, reset filter, filter domain yang relevan, serta sorting hanya pada kolom yang membantu keputusan. Card listing yang dapat bertambah tetap memakai pencarian, filter/status, sorting penting, pagination, dan empty state.
- Setiap halaman manajemen entitas bisnis yang dapat dimutasi wajib menyediakan lifecycle lengkap secara default: tambah, lihat/edit, update, arsip (soft delete), pulihkan dari arsip, dan hapus permanen. Pengecualian hanya untuk data turunan, append-only, audit/compliance, atau data sistem yang memang tidak boleh dibuat/diubah manual; alasan pengecualian wajib dibuktikan dari flow dan dicatat pada implementasi/test.
- Tabel manajemen wajib memiliki checkbox per baris, pilih semua pada halaman aktif, indikator jumlah terpilih, serta aksi massal Arsipkan, Pulihkan, dan Hapus Permanen sesuai state data. Aksi massal tidak boleh menghilangkan pencarian, filter, sorting, pagination, atau aksi individual.
- Setiap tabel manajemen wajib menyediakan pencarian lintas kolom identitas utama dan filter per kolom/domain yang memang membantu keputusan. Filter aktif harus dapat dipakai sebagai scope aksi massal tanpa mengharuskan user mencentang setiap halaman. Tombol aksi by-filter wajib menyebutkan bahwa seluruh data yang cocok filter akan diproses, bukan hanya halaman yang terlihat.
- Aksi arsip, pulihkan, hapus permanen, massal, dan by-filter wajib memakai dialog konfirmasi. Dialog menyebut jenis aksi, scope (record terpilih atau filter aktif), jumlah record bila dapat dihitung, dampak terhadap relasi, apakah dapat dipulihkan, dan kalimat tegas bahwa hard delete tidak dapat dibatalkan. Jangan memakai browser alert.
- Arsip hanya mengubah state soft delete dan tidak menghapus row database. Daftar menyediakan filter status `Aktif`, `Diarsipkan`, dan `Semua`; data arsip menampilkan aksi Pulihkan dan Hapus Permanen, sedangkan data aktif menampilkan Edit dan Arsipkan.
- Selection harus memakai identifier stabil, dibersihkan setelah aksi berhasil atau ketika scope filter berubah material, tidak memilih row di luar hasil yang dimaksud, dan tetap memberikan empty/error/loading feedback yang jelas. Checkbox header merepresentasikan halaman aktif; aksi by-filter adalah kontrol terpisah agar scope tidak ambigu.
- Filter, sorting, serta kolom tidak boleh ditambahkan hanya agar tampak lengkap. Pertahankan state filter, sorting, dan halaman selama interaksi yang tidak mengubah konteks; reset halaman hanya ketika kriteria query berubah.
- Pilihan sedikit memakai radio, checkbox, select group, atau segmented control; pilihan besar memakai pencarian/autocomplete server-side atau tabel selector, bukan dropdown panjang.
- Select yang sumber datanya banyak atau dapat terus bertambah wajib dapat dicari tanpa memuat seluruh pilihan ke browser. Gunakan autocomplete server-side atau tabel selector dengan debounce, batas hasil, dan allowlist filter/sort.
- Prioritaskan identitas, status, waktu penting, angka ringkas, dan aksi pada tampilan awal; detail sekunder dipindahkan ke halaman detail atau modal ringkas.
- Empty state harus memberi penjelasan kondisi dan next action. Status proses wajib memiliki teks/badge selain warna.
- Label UI Bahasa Indonesia; pertahankan istilah familiar: username, password, email, role, module.
- Pesan validasi wajib memakai Bahasa Indonesia dan nama field yang terlihat oleh user; jangan tampilkan nama property internal, nested key, atau label field berbahasa Inggris.
- Sebelum membuat toast, alert, modal konfirmasi/hapus/password, loader, icon, atau pola feedback baru, cari dan gunakan komponen yang sudah tersedia di `resources/themes/<theme>/views/starter/templates/` bila sesuai kebutuhan.
- Validasi pada `input-group` harus mewarnai border input dan seluruh addon/button sebagai satu control utuh; ikon validasi background pada input di dalam group disembunyikan agar tidak bertabrakan dengan trailing control.
- `input-group` invalid yang sedang fokus wajib mempertahankan border dan focus ring merah; state `focus-within` bawaan template tidak boleh mengembalikannya menjadi biru.
- Pada varian `input-group-flat`, letakkan `invalid-feedback` sebagai sibling setelah penutup `input-group` dengan `d-block`, bukan sebagai child group. Hubungkan input dan pesan memakai `aria-invalid` serta `aria-describedby` agar focus ring tidak membungkus pesan dan rounded corner addon tetap benar.

## Livewire

- Form normal memakai `wire:submit` dan `wire:model.defer`. Validasi, authorization, query, audit, dan persistence berjalan saat submit; jangan memakai `wire:model.live`, autosave, atau validasi server pada setiap input secara default.
- Form create/edit sederhana atau menengah boleh memakai modal. Modal wajib membedakan mode tambah/edit, mereset state serta error saat dibuka, mengarahkan fokus awal secara wajar, dan menampilkan feedback submit tanpa menumpuk modal dengan loader. Form kompleks tetap memakai halaman sendiri kecuali requirement feature secara eksplisit menetapkan modal dan isinya dapat dibagi menjadi section yang tetap proporsional.
- Editable table hanya dipakai untuk perubahan berulang pada field homogen seperti urutan, durasi, atau bobot. Perubahan dilakukan secara lokal saat mengetik, baris/sel yang berubah harus terlihat, validasi muncul pada sel terkait, dan persistence memakai aksi simpan per baris atau simpan semua—bukan request/autosave pada setiap ketikan. Authorization, transaksi, audit, allowlist kolom, serta batas jumlah baris tetap wajib.
- Live request form hanya boleh digunakan untuk kebutuhan yang tidak dapat ditunda—cek unik yang perlu diketahui sebelum submit, dropdown bergantung server, autocomplete, perhitungan harga/stok otoritatif, atau upload/preview file. Request tersebut wajib memiliki alasan UX, scope field sempit, batas input server-side, dan validasi penuh tetap diulang saat submit.
- Search/filter live memakai debounce sekitar 300–500 ms bila menjalankan query dan tetap dibatasi server-side.
- Action yang mengirim request menampilkan loader global dan blur area melalui `starter-runtime.js`.
- Polling, refresh otomatis monitoring, dan sinkronisasi pasif berjalan tanpa loader global, tanpa mengosongkan konten, serta tanpa mereset filter, pagination, fokus input, atau posisi scroll. Gunakan indikator kecil seperti waktu pembaruan terakhir bila feedback diperlukan; loader global tetap dipakai untuk aksi eksplisit user.
- Request perubahan input biasa tidak boleh memunculkan loader action.
- Modal harus ditutup/diselesaikan sebelum loader mengambil fokus agar tidak bertumpuk.
- Feedback sukses/error memakai event toast project, bukan alert.
- Navigasi SPA Livewire hanya dipakai bila URL akhir dijamin tetap pada origin
  yang sama. Link lintas root/auth/app subdomain, serta link seperti Login yang
  dapat diarahkan middleware ke subdomain App, wajib memakai navigasi browser
  penuh tanpa `wire:navigate`; jangan membuka CORS untuk mengakali navigasi UI.

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

- Error `400`, `401`, `403`, `404`, `405`, `408`, `419`, `422`, `429`, `500`, `503`, serta fallback `4xx`/`5xx` memakai layout lokal `resources/themes/<theme>/views/starter/errors/layout.blade.php` dan desain template UI aktif yang konsisten.
- Error page memakai asset lokal, `noindex`, Bahasa Indonesia, kode status, dan aksi kembali yang aman; jangan tampilkan stack trace, exception message internal, path server, query, atau credential.
- Uji view spesifik/fallback dan minimal satu response exception nyata dengan `APP_DEBUG=false`.

## Verifikasi

Uji dengan browser:

- jalur masuk dari menu, redirect, dan tombol;
- loading, modal, toast, validasi, empty state, status, dan aksi utama;
- data kosong, sedikit, dan banyak untuk membuktikan pemilihan komponen serta kepadatan informasi tepat;
- desktop Full HD, ukuran laptop, dan mobile yang relevan.
- kesesuaian struktur/markup dengan contoh `docs/template/<theme>` yang dipakai sebagai referensi.
- Untuk perubahan CSS komponen, verifikasi state normal, focus, invalid, serta invalid+focus; periksa struktur DOM/computed style bila screenshot saja belum membuktikan penyebab.
