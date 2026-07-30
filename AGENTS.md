# AI Entry Point

Dokumen ini adalah kontrak eksekusi untuk setiap model AI yang mengembangkan project turunan dari starterkit ini. Jangan membaca seluruh `docs/` pada setiap tugas; baca konteks inti dan rule yang relevan.

Repository ini hanya berisi source core starterkit dan tidak dapat dijalankan sebagai aplikasi Laravel mandiri. Semua perintah Composer, Artisan, Pint, Pest, migration, dan setup dijalankan dari root Laravel host yang memiliki clone ini pada folder `starterkit/`.

Installer membuat connector terkelola pada `AGENTS.md` di root Laravel host.
Connector tersebut mengarahkan agent ke file canonical ini tanpa menyalin
seluruh rules, sehingga perubahan rules langsung terbaca setelah
`git pull` pada folder `starterkit/`. Bila bekerja dari root Laravel host,
referensi `docs/...` dalam file ini berarti `starterkit/docs/...`; path feature
seperti `app/`, `routes/apps/`, `resources/views/apps/`,
`database/migrations/apps/`, `tests/`, dan `issues/` tetap milik Laravel host.

## Konteks wajib

- Project private/internal company, bukan SaaS.
- PHP, Laravel, Livewire, dan Pest mengikuti versi terbaru yang kompatibel serta sudah dikunci oleh dependency project. Template UI mengikuti aset/template aktif project; jangan mengasumsikan brand, versi, API, atau icon set tertentu tanpa memeriksa source aktual.
- Hak akses berbasis module: satu role dapat mengakses banyak module; route dan menu mengikuti module.
- Superuser adalah akun sistem tersembunyi bagi non-superuser dan tidak boleh diubah/dihapus.
- Metadata app, module, route, dan menu didefinisikan di source code lalu disinkronkan ke database.
- UI menggunakan Bahasa Indonesia, kecuali istilah familiar seperti username, password, email, role, dan module.
- Selama development jangan aktifkan config cache; gunakan config cache hanya pada deployment production.
- Root clone starterkit hanya boleh memiliki folder bootstrap `installer/`,
  source core, migration inti, route inti, view inti, asset, dokumentasi, dan
  rules. Jangan menambahkan shell Laravel seperti `artisan`, `bootstrap/`,
  `app/`, `vendor/`, database development, landing project, atau app contoh.

Baca [project-context](docs/rules/project-context.md) sekali saat belum mengenal project atau ketika konteks percakapan hilang.

## Kontrak default feature

- User/developer cukup menjelaskan kebutuhan bisnis, flow, data, dan role yang relevan. Jangan meminta mereka mengulang standar teknis starterkit.
- Setiap permintaan membuat atau mengubah feature wajib melewati gerbang
  spesifikasi: buat `issues/<feature-slug>.md` pada Laravel host, berhenti sebelum
  mengubah code feature, lalu minta user membaca dan menyetujui detail teknis.
  Setelah file selesai, respons wajib menyebut bahwa eksekusi dapat dilanjutkan
  dengan model yang lebih hemat/rendah karena konteks teknis sudah dikunci.
- Permintaan module baru belum boleh masuk gerbang spesifikasi bila developer
  belum menyebut App pemilik, nama module, dan struktur menu (single atau parent
  beserta child). Respons pertama wajib menjelaskan informasi yang kurang dan
  memberi contoh prompt yang benar; detailnya mengikuti
  `docs/rules/feature-development.md`.
- Secara otomatis terapkan authorization, validation, proteksi injection/mass-assignment, server-side pagination untuk data yang dapat tumbuh, query efisien, audit log, transaksi, locale/format, pola Livewire/Alpine, UI state, migration production-safe, dan test sesuai rule pemilik.
- Seluruh feature untuk app/subdomain wajib mengikuti struktur `Apps/<Subdomain>` pada layer yang dipakai. Baca `docs/rules/architecture.md` sebelum membuat file feature app baru; jangan mencampurnya dengan folder Starter atau root project.
- Migration feature app wajib berada di `database/migrations/apps/<subdomain>/`, bukan root `database/migrations`; folder tersebut dimuat otomatis saat perintah Artisan migration berjalan.
- Endpoint API App wajib berada di `routes/apps/<subdomain>.api.php`. API memakai
  gateway `api.<APP_DOMAIN>/<subdomain>`, tidak memakai prefix `/api`, tidak
  menjadi metadata menu/module web, dan hanya didaftarkan ketika
  `STARTER_API_ENABLED=true`.
- Asset CSS/JS custom halaman app wajib berada pada Blade asset yang berdekatan dengan view pemiliknya. Gunakan Alpine untuk UI kecil, Livewire hanya untuk kebutuhan server, dan `wire:model.defer` untuk form normal; baca `ui-ux.md` serta `performance.md` sebelum membuat interaksi atau memakai library baru.
- UI wajib berangkat dari contoh terdekat di `docs/template`. Gunakan `docs/template/template.md` sebagai atlas pencarian yang tidak mengikat: cari beberapa kandidat lintas konteks, buka satu sampai tiga sumber HTML, lalu pilih/komposisikan pola template UI aktif yang paling tepat. Jangan membuat desain/komponen hanya berdasarkan selera. Pilih komponen berdasarkan konteks, jenis, dan volume data agar halaman padat, informatif, responsif, serta profesional.
- Bila sebuah standar tidak relevan, lewati tanpa menambah code seremonial. Bila requirement meminta deviasi, jelaskan risiko dan minta keputusan eksplisit.

## Kontrak anti-asumsi

- Sebelum merencanakan atau mengubah code, telusuri flow existing dari route/menu → Livewire/controller → service → interface/repository → model/migration → test/config terkait.
- Nyatakan sesuatu sebagai kondisi existing hanya setelah dibuktikan dari code, schema, config, test, atau output command. Jangan mengarang route, tabel, kolom, role, status, config key, integration, atau business rule.
- Pisahkan requirement terkonfirmasi, temuan existing, proposal teknis, dan pertanyaan terbuka. Proposal tidak boleh ditulis seolah-olah keputusan user.
- Jika keputusan bisnis/authorization/data yang tidak dapat ditemukan akan mengubah hasil secara material, hentikan bagian yang bergantung padanya dan minta keputusan; jangan memilih diam-diam.
- Gunakan source of truth existing dan sibling terdekat. Jangan membuat layer, abstraction, helper, config, atau dependency baru bila pola project yang ada sudah menyelesaikan kebutuhan.
- Verifikasi versi framework/package dari `composer.lock`, lockfile frontend, atau file konfigurasi aktual milik Laravel host sebelum memakai API; jangan mengasumsikan dokumentasi versi terbaru cocok dengan dependency terpasang.
- Jika requirement project bertentangan dengan rule inti, jelaskan rule yang terdampak, alasan, risiko, dan perubahan arsitekturnya. Deviasi hanya dilakukan setelah keputusan eksplisit dan rule/context terkait diperbarui pada perubahan yang sama.
- Terapkan perubahan terkecil yang menyelesaikan root cause. Pertahankan perubahan user dan bagian worktree lain yang tidak terkait.
- Jangan menambah package, base directory, service production, daemon, atau konfigurasi web server khusus tanpa kebutuhan terverifikasi dan persetujuan eksplisit.

## Router dokumen

| Jika tugas menyangkut | Baca hanya |
|---|---|
| Menambah/mengubah feature dalam app | `docs/rules/feature-development.md` |
| App atau subdomain baru | `docs/rules/app-subdomain.md` |
| Route, menu, module, atau role | `docs/rules/access-control.md` |
| Model, create/update/delete, transaksi | `docs/rules/audit-logging.md` |
| Livewire, form, tabel, modal, loader | `docs/rules/ui-ux.md` |
| Konfigurasi, upload, login, lock screen | `docs/rules/security-and-config.md` |
| PHP/Laravel conventions | `docs/rules/code-style.md` |
| Query, pagination, cache, bulk action, atau asset | `docs/rules/performance.md` |
| Locale, angka, tanggal, atau currency | `docs/rules/localization-and-formatting.md` |
| Pengujian dan definition of done | `docs/rules/testing.md` |
| Shared hosting/deployment | `docs/rules/deployment.md` |
| Hubungan layer dan source of truth | `docs/rules/architecture.md` |
| Install/update clone starterkit atau extension project | `README.md` |

## Profil baca minimum

Profil ini menentukan titik mulai pembacaan, bukan izin untuk mengabaikan rule lain yang tersentuh oleh requirement. Tambahkan rule pemilik setiap kali perubahan menyentuh topiknya.

| Jenis tugas | Baca minimum | Tambahkan bila tersentuh |
|---|---|---|
| Bug/refactor kecil pada area yang sudah dikenal | `AGENTS.md`, sibling code/test, dan rule pemilik area | `testing.md` untuk perubahan perilaku; rule lain sesuai dampak |
| Feature CRUD pada app existing | `feature-development.md`, `architecture.md`, `audit-logging.md`, `testing.md` | `code-style.md` bila model/migration; `performance.md` bila daftar/query; `ui-ux.md` bila UI/interaksi |
| Halaman atau interaksi UI | `ui-ux.md`, `testing.md`, dan pencarian atlas template | `performance.md` untuk daftar/asset/library; `security-and-config.md` untuk upload, sesi, atau kredensial |
| Schema, migration, atau perubahan data | `feature-development.md`, `code-style.md`, `testing.md` | `audit-logging.md` untuk mutation bisnis; `performance.md` untuk query/index |
| App/subdomain baru | `app-subdomain.md`, `architecture.md`, `access-control.md`, `testing.md` | `deployment.md` dan `security-and-config.md` bila domain/session/config berubah |
| Konfigurasi, autentikasi, security, atau deployment | `security-and-config.md`, `deployment.md`, `testing.md` | `access-control.md`, `audit-logging.md`, atau `app-subdomain.md` sesuai dampak |

## Prioritas rule

Jika terjadi benturan atau ketidakjelasan, gunakan urutan berikut di dalam repository:

1. Instruksi platform/developer dan keputusan eksplisit user untuk tugas saat ini.
2. Requirement bisnis, authorization, keamanan, dan integritas data yang telah terkonfirmasi.
3. Konteks arsitektur project serta rule pemilik yang paling spesifik untuk area tersebut.
4. Source of truth existing—schema, config, route, test, dan implementasi sibling yang telah dibuktikan.
5. Konvensi umum, contoh template UI, dan preferensi implementasi.

Rule pada tingkat lebih rendah tidak boleh dipakai untuk membatalkan tingkat lebih tinggi. Jika dua aturan setingkat benar-benar bertentangan, hentikan bagian yang terdampak, jelaskan konflik, dan minta keputusan; jangan memilih diam-diam.

## Cara kerja hemat token

1. Cari file terkait dengan `rg`; baca sibling terdekat sebelum mengubah code.
2. Untuk permintaan feature, lakukan discovery minimum lalu tulis satu spesifikasi
   teknis di `issues/<feature-slug>.md`; jangan menulis ulang rule umum ke dalamnya.
3. Untuk UI, cari `docs/template/template.md` dengan `rg` berdasarkan konteks dan komponen, lalu buka 1–3 HTML sumber yang paling relevan. Jangan membaca seluruh atlas atau `docs/template/tabler-components`.
4. Jangan menyalin ulang aturan umum ke dokumen feature.
5. Jangan membuat folder template issue atau archive. File `issues/*.md` hanya
   dibuat sebagai gerbang permintaan feature; bugfix, diagnosis, maintenance,
   dan dokumentasi tidak membuat issue otomatis.

## Aturan eksekusi

- Pada project pemakai, folder clone `starterkit/` adalah core read-only untuk feature project. Perubahan di dalamnya hanya untuk improvement universal melalui branch/PR starterkit.
- Folder `starterkit/installer/` hanya dimiliki bootstrap instalasi. Setelah
  setup berhasil, developer dan AI feature wajib mengabaikannya; baca atau ubah
  folder tersebut hanya saat tugas memang menyangkut installer.
- Instalasi standar hanya boleh dilakukan pada Laravel fresh dengan database
  khusus yang baru melalui `php starterkit/installer/install.php`. Installer
  wajib memeriksa source target, menjelaskan bahwa `migrate:fresh` menghapus
  seluruh tabel/data, dan berhenti sebelum mutation kecuali developer
  mengonfirmasi dengan `y`. Installer wajib menanyakan kode/subdomain dan nama
  App pertama serta menerima input kosong untuk instalasi tanpa App. Tanpa App,
  landing onboarding wajib menjelaskan App, module, menu, submenu, generator,
  struktur source, dan alur sync. Jangan memakai `starterkit:install` untuk
  update, deploy rutin, project berjalan, atau database existing.
- Reinstall destruktif hanya melalui
  `php starterkit/installer/install.php --reset`, hanya pada
  `APP_ENV=local|development`, dan wajib melewati konfirmasi `y` lalu `RESET`.
  Mode ini menghapus seluruh data serta source App/project yang dimiliki
  kontrak starterkit; production wajib ditolak sebelum mutation.
- Jangan meminta developer menyalin source core atau mengedit connector satu per
  satu pada instalasi Laravel fresh yang masih memakai struktur standar.
- Installer wajib membuat atau memperbarui block connector terkelola pada
  `AGENTS.md` root Laravel host tanpa menimpa instruksi project di luar block
  tersebut. File canonical `starterkit/AGENTS.md` tetap menjadi source of truth
  agar update rules tidak memerlukan copy manual.
- Setelah membuat `issues/<feature-slug>.md`, jangan langsung mengeksekusi code.
  Beri tahu user bahwa detail teknis siap diperiksa dan tunggu persetujuan
  eksplisit untuk melanjutkan implementasi.
- Perubahan trivial seperti typo/dokumentasi murni dapat langsung dikerjakan bila tidak mengubah business flow, authorization, data, API, atau deployment.
- Jika user hanya meminta planning/review/diagnosis, jangan mengubah code atau state di luar artefak planning yang diminta.
- Jalankan `php artisan make:* --no-interaction` dari root Laravel host untuk file project.
- Migration wajib aman untuk data production dan data existing; jangan mengandalkan database kosong.
- Setiap perubahan code/perilaku wajib memiliki atau memperbarui test yang relevan; perubahan dokumentasi murni wajib melewati pemeriksaan link dan konsistensi.
- Setelah mengubah PHP core, jalankan `vendor/bin/pint --dirty --format agent` dari Laravel host.
- Jalankan test integrasi terfokus dari Laravel host lebih dahulu, lalu suite yang relevan.
- Jangan menghapus test tanpa persetujuan.

## Evolusi rules

- Sebelum mengeksekusi code, nilai apakah instruksi baru dari user/developer bersifat reusable dan cocok menjadi standar starterkit atau project turunannya.
- Jika cocok, minta konfirmasi singkat sebelum eksekusi. Setelah disetujui, perbarui rule pemilik yang paling relevan dalam perubahan yang sama tanpa menunggu permintaan lanjutan.
- Jangan menjadikan keputusan bisnis sekali pakai, detail satu feature, nilai rahasia, atau workaround sementara sebagai rule global.
- Tulis rule secara umum, ringkas, dapat dieksekusi, dan tidak menduplikasi rule lain. Jika bertentangan dengan rule existing, jelaskan konflik dan minta keputusan eksplisit sebelum menggantinya.
