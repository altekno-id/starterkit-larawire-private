# Arsitektur dan Source of Truth

## Lapisan

1. **Registry code-first**
   - `config/apps/<app-key>.php`: nama app, module, struktur menu, icon, dan landing.
   - `routes/apps/<app-key>.php`: route app pada subdomain.
   - `routes/apps/<app-key>.api.php`: endpoint API App pada gateway API bersama;
     file ini tidak menjadi metadata registry web.
   - `StarterAppRegistry`: menemukan app valid dari pasangan kedua file tersebut.
2. **Metadata database**
   - `starter_apps`, `starter_app_mods`, `starter_app_routes`, `starter_app_menus`.
   - Dihasilkan dan dirapikan oleh `php artisan starter:sync`.
3. **Authorization**
   - role ↔ module: `pivot_client_roles_app_mods`.
   - landing per app: `pivot_client_roles_app_landings`.
   - route app diperiksa `starter.authorize`.
4. **Presentation**
   - Full-page Livewire component di `app/Livewire/Apps/<App>/<Module>/`.
   - Blade di `resources/views/apps/<app-key>/<module>/`.
   - Layout utama `resources/views/starter/templates/layouts/app.blade.php`.
5. **Domain/data**
   - Model untuk persistence.
   - Service untuk business flow, transaksi, dan aksi lintas model.
   - Interface/contract mendefinisikan kebutuhan persistence/query yang stabil.
   - Repository mengimplementasikan contract dan menjadi pemilik query/persistence reusable per domain.
6. **Cross-cutting**
   - `AuditLogService`: log create/update/delete.
   - `StarterConfigService`: konfigurasi dinamis.
   - `StarterContextService`: app aktif, sidebar, branding, dan data layout.

## Pola default module bisnis

Alur dependency default untuk feature bisnis:

```text
Livewire/Controller → Service → Repository Interface → Repository → Model/Database
```

Tanggung jawab setiap layer:

- **Livewire/Controller** menangani input transport, validasi bentuk request, authorization awal, pemanggilan use case, dan response/redirect. Jangan menaruh query atau business flow di view/component.
- **Service** menangani use case dan business rule, authorization defensif yang bergantung pada data, transaksi, koordinasi beberapa repository, audit grouping, serta side effect. Service tidak boleh berisi detail SQL, pagination manual, atau markup/UI.
- **Repository interface** adalah kontrak minimal yang benar-benar dibutuhkan consumer. Signature wajib typed dan memakai nama bisnis; jangan menyalin seluruh API Eloquent ke interface.
- **Repository** memiliki query, filter, sort, aggregate, pagination database, eager loading, locking, dan bulk persistence yang reusable untuk satu domain. Repository tidak boleh membaca `request()`, `auth()`, session, atau menampilkan response/UI.
- **Model** memiliki mapping tabel, cast, relation, accessor/mutator, dan invariant model yang kecil. Model bukan tempat orchestration use case.

Ketentuan konsistensi dan performa:

- Module bisnis baru yang memiliki persistence memakai interface dan repository domain sebagai boundary default.
- Tambahkan service bila terdapat mutation, business rule, transaksi, audit grouping, authorization berbasis data, side effect, atau koordinasi lebih dari satu operasi. Jangan membuat service yang hanya meneruskan satu pemanggilan repository tanpa logic.
- Read-only sederhana boleh memanggil repository interface langsung dari Livewire/controller bila tidak memiliki business logic. Begitu flow berkembang, pindahkan orchestration ke service tanpa memindahkan query keluar repository.
- Dilarang membuat `BaseRepository`/`GenericRepository` dengan CRUD universal, magic filter, atau method `all()` yang menjadi jalan pintas memuat data tanpa batas.
- Method repository harus intention-revealing seperti `paginateForViewer()`, `findActiveByUsername()`, atau `countByStatus()`, bukan wrapper generik `query()`/`execute()`.
- Jangan mengembalikan `Builder` dari contract kecuali composition tersebut memang menjadi kontrak domain yang teruji. Utamakan model, collection terbatas, paginator, scalar aggregate, atau DTO/read model yang typed.
- Perubahan schema/query diserap di repository selama kontrak use case tidak berubah. Interface berubah hanya ketika kebutuhan consumer berubah, bukan karena detail implementasi database berubah.
- Gunakan constructor injection dan daftarkan seluruh binding interface → repository secara terpusat di service provider. Hindari `app()` service locator pada code domain baru bila dependency dapat diinjeksi.
- Terapkan seluruh batas pagination, select minimal, eager loading, query budget, cache, dan bulk operation dari `performance.md` di dalam repository.
- Test repository membuktikan bentuk query, scope data, pagination, dan batas query; test service membuktikan business rule, transaksi, authorization, audit, dan rollback.

## Isolasi starterkit dan project turunan

- Pada mode Git clone, source core berada di `<laravel>/starterkit` sebagai repository mandiri dan diabaikan oleh Git project host. Feature project dilarang mengubah file clone; improvement universal wajib melalui branch dan PR repository starterkit.
- Connector host dibatasi pada autoload namespace `Altekno\StarterKit\` dari
  `starterkit/src`, registrasi `StarterServiceProvider`, pemanggilan
  `StarterBootstrap`, environment, dan asset publish. Pada Laravel host standar
  seluruh connector dipasang oleh `php starterkit/installer/install.php`.
  Instalasi awal hanya menerima Laravel fresh dan database baru karena memakai
  `migrate:fresh`; project existing harus memakai alur update. Pengecualian
  hanya reinstall destruktif yang diminta eksplisit pada environment
  local/development melalui `--reset`. Detail instalasi ada pada `README.md`
  root starterkit.
- Seluruh PHP milik starterkit wajib berada pada subfolder/namespace `Starter` di layer masing-masing: Commands, Contracts, Controllers, Middleware, Livewire, Models, Repositories, Rules, Services, dan Support.
- Binding, alias Livewire, listener, migration loader, view path, dan persistent middleware starter dimiliki `src/Providers/Starter/StarterServiceProvider.php`. `AppServiceProvider` host tetap bersih untuk binding project turunan.
- Seluruh migration core berada di `starterkit/database/migrations/starter`. Migration feature app milik host berada di `database/migrations/apps/<subdomain>` dan seluruh folder subdomain valid dimuat otomatis saat perintah Artisan migration berjalan. Tidak ada konfigurasi environment atau registrasi manual per app.
- Seluruh Blade internal starter berada di `resources/views/starter`, termasuk `errors`, `templates`, auth, profile, settings, log, dan user management.
- Landing adalah area kustom project host dan wajib berada di
  `app/Livewire/Landing` serta `resources/views/landing`, bukan di folder clone
  starterkit. Installer membuat landing minimum hanya bila project belum
  memiliki root landing. Ketika instalasi tidak membuat App pertama, landing
  minimum berfungsi sebagai onboarding yang menjelaskan hierarki App → Module →
  Menu → Submenu, generator, lokasi source, dan alur sync. Setelah dibuat,
  ownership landing sepenuhnya milik project.
- Route internal starter berada di `starterkit/routes/starter`; `routes/web.php` milik host memuat landing dan route project root-domain.
- Test internal starter berada di `tests/Feature/Starter`; test feature project berada di `tests/Feature/Apps/<App>` atau folder domain project.
- Asset internal starter berada di `public/assets/starter`; jangan menaruh asset starter baru di folder project yang generik.
- File standar framework seperti `config/auth.php`, `config/session.php`, dan `config/livewire.php` tetap pada lokasi Laravel karena dibaca langsung oleh framework. Isolasi dilakukan pada ownership dan referensinya, bukan dengan memindahkan file standar secara paksa.

## Struktur feature app/subdomain

- Setiap feature bisnis milik app/subdomain wajib berada di area `Apps/<Subdomain>` pada layer yang dipakai; jangan membuat feature app di folder `Starter`, root layer, atau app/subdomain lain.
- Gunakan `<Subdomain>` dalam PascalCase untuk namespace/path PHP, misalnya `Sales`; gunakan `<subdomain>` lowercase untuk route, config, view, translation, dan asset, misalnya `sales`.
- Buat hanya folder yang benar-benar dibutuhkan feature. Jangan membuat seluruh tree kosong sebagai formalitas.

```text
app/Livewire/Apps/<Subdomain>/<Module>/
app/Http/Controllers/Apps/<Subdomain>/<Module>/
app/Services/Apps/<Subdomain>/<Module>/
app/Contracts/Apps/<Subdomain>/<Module>/
app/Repositories/Apps/<Subdomain>/<Module>/
app/Models/Apps/<Subdomain>/
app/Rules/Apps/<Subdomain>/<Module>/
app/Support/Apps/<Subdomain>/

resources/views/apps/<subdomain>/<module>/
resources/views/apps/<subdomain>/<module>/assets/
public/assets/apps/<subdomain>/vendor/
lang/id/apps/<subdomain>/
tests/Feature/Apps/<Subdomain>/
database/migrations/apps/<subdomain>/
```

- Layer khusus hanya dibuat bila dipakai: `app/Http/Middleware/Apps/<Subdomain>/`, `app/Policies/Apps/<Subdomain>/`, `app/Jobs/Apps/<Subdomain>/`, `app/Events/Apps/<Subdomain>/`, `app/Listeners/Apps/<Subdomain>/`, `app/Notifications/Apps/<Subdomain>/`, dan `app/Console/Commands/Apps/<Subdomain>/`.
- `config/apps/<subdomain>.php`, `routes/apps/<subdomain>.php`, dan
  `routes/apps/<subdomain>.api.php` sengaja tetap berupa file langsung karena
  dipakai registrar starter. Isi dan class yang dirujuknya tetap mengikuti area
  `Apps/<Subdomain>`.
- CSS/JS custom halaman berada di `resources/views/apps/<subdomain>/<module>/assets/<page>.css.blade.php` dan/atau `<page>.js.blade.php`, lalu hanya di-include oleh Blade pemiliknya. File vendor pihak ketiga yang tidak dapat ditulis sebagai Blade tetap lokal di `public/assets/apps/<subdomain>/vendor/`; tag pemuatan dan inisialisasinya tetap berada pada asset Blade halaman.
- Landing root bukan feature app; tetap di `app/Livewire/Landing` dan `resources/views/landing`.
- Migration app tidak diletakkan di root `database/migrations` atau di dalam folder module. Satu folder `<subdomain>` menampung seluruh riwayat migration app tersebut agar Laravel tetap dapat memuatnya otomatis dan ownership schema mudah ditelusuri.
- Extension UI project hanya boleh berada pada kontrak `resources/views/extensions/starter/` yang didokumentasikan. Extension bukan override: project dilarang menyalin atau mengganti view/layout core.
- `AGENTS.md` pada root Laravel host adalah connector project yang dipasang
  installer dan wajib disimpan dalam repository project. Rules canonical tetap
  berada di `starterkit/AGENTS.md` serta `starterkit/docs/rules/`; connector
  hanya mengarahkan agent agar rules terbaru langsung berlaku setelah clone
  starterkit diperbarui.
- Installer hanya boleh mengelola block connector starterkit di dalam
  `AGENTS.md`; instruksi project di luar marker block wajib dipertahankan.

## Registrasi route

- `routes/starter/global.php` didaftarkan tanpa domain dan tersedia pada semua subdomain untuk Profile, Pengaturan, Log Aktivitas, lock screen, dan endpoint session.
- `routes/starter/web.php` hanya didaftarkan pada root `APP_DOMAIN` untuk autentikasi starter.
- `routes/web.php` hanya memuat landing dan route project root-domain.
- `StarterRouteRegistrar` memasang `routes/apps/<app-key>.php` pada `<app-key>.<APP_DOMAIN>`.
- Ketika `STARTER_API_ENABLED=true`, registrar memasang
  `routes/apps/<app-key>.api.php` pada
  `api.<APP_DOMAIN>/<app-key>` dengan namespace route `api.<app-key>.*`.
- Domain `api.<APP_DOMAIN>` adalah gateway infrastruktur dan bukan App baru.
  Scramble memakai root domain tersebut sebagai UI dokumentasi dan
  `/openapi.json` sebagai spesifikasi.
- File app harus memakai nama route `<app-key>.<module-code>.<action>`.
- Route `<app-key>.anchor` adalah pengecualian untuk redirect awal app.

## Aturan perubahan

- Buktikan ownership dan source of truth feature existing sebelum menentukan file yang diubah.
- Jangan edit metadata app/module/menu langsung di database.
- Ubah config dan route di source code, validasi, lalu sync.
- Database adalah runtime projection untuk authorization dan navigasi.
- Route yang tidak memiliki module valid akan ditolak oleh `starter:sync`.
- Jangan menduplikasi source of truth ke config, database, dan public state sekaligus. Pilih owner sesuai lapisan di atas dan turunkan projection/runtime state dari owner tersebut.
- Jangan membuat helper/event/abstraction tambahan hanya berdasarkan preferensi. Interface dan repository mengikuti pola default domain di atas; layer lain tetap harus memiliki tanggung jawab nyata dan mengikuti sibling existing.
