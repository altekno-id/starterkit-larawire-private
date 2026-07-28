# Performance dan Efisiensi Resource

Aturan ini berlaku otomatis untuk starterkit dan seluruh project turunannya. User/developer cukup menjelaskan kebutuhan bisnis feature; standar performa di bawah tidak perlu diulang dalam prompt.

## Baseline

- Target default adalah shared hosting satu instance dengan `file` cache/session dan queue `sync`.
- Solusi wajib tetap bekerja tanpa Redis, Octane, daemon, worker permanen, CDN, reverse proxy, atau tuning web server khusus.
- Optimalkan berdasarkan flow dan query nyata. Jangan menambah cache, index, package, atau abstraction hanya karena dugaan.
- Performa tidak boleh mengurangi validation, authorization, transaksi, audit log, keamanan session, atau integritas data.

## Query dan pagination

- Setiap daftar data bisnis yang dapat bertambah wajib melakukan search, filter, sort, aggregate, dan pagination di database.
- Dilarang memanggil `get()`/`all()` lalu memfilter, mengurutkan, atau membuat `LengthAwarePaginator` manual untuk daftar yang dapat bertambah.
- Batasi `perPage` pada pilihan yang wajar. Sort column, sort direction, filter enum, dan page size dari request/Livewire wajib memakai allowlist.
- Pilih hanya kolom dan relasi yang dipakai oleh response/view. Gunakan eager loading atau aggregate seperti `withCount()` untuk mencegah N+1 dan over-fetching.
- Jangan menjalankan query yang sama lebih dari sekali dalam satu render/request. Hitung sekali, simpan pada variable, atau gunakan request-scoped memoization.
- Jangan menjalankan query di dalam loop bila hasil dapat diambil secara batch.
- Pencegahan lazy loading dan atribut yang dibuang diam-diam wajib aktif pada local/testing agar N+1 dan mass-assignment yang salah menjadi kegagalan yang dapat diperbaiki sebelum production.
- Untuk filter tanggal pada tabel besar/indexed, gunakan rentang datetime setengah terbuka (`>= awal`, `< hari berikutnya`), bukan fungsi pada kolom seperti `whereDate()` yang dapat menghalangi index.
- Search bebas wajib memiliki batas panjang server-side dan debounce UI. Gunakan query binding Eloquent/query builder; jangan menggabungkan input ke raw SQL.
- Operasi banyak record memakai bulk query atau `chunkById()`/`lazyById()` sesuai kebutuhan. Jangan memuat seluruh tabel ke memory.

## Index dan migration

- Index mengikuti kombinasi `where`, `join`, dan `order by` yang benar-benar digunakan. Periksa query/`EXPLAIN` sebelum menambah index non-trivial.
- Hindari index duplikat dan index spekulatif yang hanya memperlambat write.
- Penambahan index wajib data-preserving, reversible, dan mengikuti aturan migration production-safe pada `code-style.md`.
- Perubahan skema besar tidak boleh mengasumsikan maintenance window, akses root server, atau database kosong.

## Livewire dan UI

- `render()` bersifat read-only, idempotent, dan hanya mengambil data yang dibutuhkan tampilan saat itu.
- Search mahal menggunakan debounce sekitar 300–500 ms. Input tetap dibatasi dan dinormalisasi di server.
- Interaksi presentasi kecil seperti toggle, show/hide, tab visual, dropdown, copy, dan preview lokal memakai Alpine/JavaScript, bukan request Livewire.
- Data referensi yang stabil tidak boleh di-query ulang pada setiap ketikan. Muat sekali, memoize per request, atau cache sebagai array dengan TTL/invalidation yang jelas.
- Public property Livewire dianggap input tidak tepercaya. Simpan state sekecil mungkin; jangan menyimpan collection/model besar, credential, atau payload sensitif lebih lama dari kebutuhan action.
- View composer/layout context tidak boleh membangun query berat untuk partial Livewire yang tidak menggunakan data tersebut.

## Cache

- Gunakan cache Laravel dengan store default project (`file`). Feature tidak boleh mewajibkan store khusus.
- Cache hanya data turunan yang aman; jangan cache keputusan authorization tanpa scope user/role dan invalidation yang dapat dibuktikan.
- Key harus memiliki namespace dan scope yang tepat. Tetapkan TTL atau invalidation pada setiap perubahan source data.
- Cache hit tidak boleh mengubah hasil authorization atau menyembunyikan perubahan data penting tanpa batas waktu.
- Config cache/`optimize` hanya untuk production; development mengikuti `deployment.md`.

## Audit dan bulk action

- Audit log tetap sinkron agar perubahan bisnis dapat dibuktikan pada shared hosting.
- Bulk update/delete yang sengaja melewati event model wajib menghasilkan satu audit summary manual berisi scope dan jumlah record; jangan membuat ribuan audit row identik tanpa kebutuhan bisnis.
- Jangan menonaktifkan audit, validation, atau transaksi hanya untuk mengejar query count.

## Asset

- Asset global starter yang benar-benar dipakai semua halaman—template UI aktif, Livewire/Alpine, theme script, dan `starter-runtime.js`—tetap dilayani lokal dari `public/assets` dan dimuat satu kali oleh layout. Jangan memindahkan asset inti ke CDN atau menjadikannya dependency feature.
- CSS/JS custom halaman app wajib berada dekat view pemilik: `resources/views/apps/<subdomain>/<module>/assets/<page>.css.blade.php` dan/atau `<page>.js.blade.php`. Blade utama hanya meng-include asset miliknya; jangan memasukkan asset halaman ke layout global atau halaman yang tidak memakainya.
- Pada Livewire, CSS/dependency dimuat melalui `@assets` dan JavaScript inisialisasi melalui `@script`, sehingga asset hanya dipasang sekali dan lifecycle-nya aman saat render ulang. Blade non-Livewire memakai stack `page-styles` dan `page-scripts` yang disediakan layout.
- Pecah asset Blade yang panjang menjadi partial di folder `assets/` yang sama. Jangan membuat file CSS/JS custom global terpisah hanya untuk kerapian. Asset vendor pihak ketiga tetap berupa file lokal/minified di `public/assets/apps/<subdomain>/vendor/` agar dapat di-cache browser; tag dan inisialisasinya tetap page-scoped.
- Jangan memuat bundle, font, CSS, JavaScript, package, atau pipeline build yang tidak dipakai template. Script non-kritis memakai `defer`; script theme yang mencegah flash warna boleh berjalan sebelum body.
- CDN dilarang untuk UI dasar atau library umum. Pengecualian hanya SDK pihak ketiga yang tidak dapat di-host sendiri, setelah alasan feature disetujui, versinya dikunci, dimuat `defer`, dan URL-nya tidak berasal dari input user.
- Asset atau script halaman tidak boleh memakai `data-navigate-once`; atribut itu hanya untuk runtime global singleton. Asset versioned yang perlu memaksa reload saat berubah memakai `data-navigate-track`.
- Production tidak membutuhkan Node/Vite server. Proses build hanya diperlukan bila source hasil build memang dipakai oleh view.

## Verifikasi

- Test daftar besar harus membuktikan pagination tetap benar dan query mengambil satu halaman, bukan seluruh collection.
- Tambahkan regression test dengan batas atas query untuk layout/daftar kritis bila perubahan berisiko mengulang query atau memunculkan N+1.
- Uji search, filter, sort, halaman terakhir, empty state, dan role dengan cakupan data berbeda.
- Untuk perubahan query besar, bandingkan query count dan bentuk SQL sebelum/sesudah; jangan memakai waktu lokal data kecil sebagai satu-satunya bukti.
