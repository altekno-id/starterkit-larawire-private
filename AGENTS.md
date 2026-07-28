# AI Entry Point

Dokumen ini adalah kontrak eksekusi untuk setiap model AI yang mengembangkan project turunan dari starterkit ini. Jangan membaca seluruh `docs/` pada setiap tugas; baca konteks inti dan rule yang relevan.

## Konteks wajib

- Project private/internal company, bukan SaaS.
- PHP, Laravel, Livewire, dan Pest mengikuti versi terbaru yang kompatibel serta sudah dikunci oleh dependency project. Template UI mengikuti aset/template aktif project; jangan mengasumsikan brand, versi, API, atau icon set tertentu tanpa memeriksa source aktual.
- Hak akses berbasis module: satu role dapat mengakses banyak module; route dan menu mengikuti module.
- Superuser adalah akun sistem tersembunyi bagi non-superuser dan tidak boleh diubah/dihapus.
- Metadata app, module, route, dan menu didefinisikan di source code lalu disinkronkan ke database.
- UI menggunakan Bahasa Indonesia, kecuali istilah familiar seperti username, password, email, role, dan module.
- Selama development jangan aktifkan config cache; gunakan config cache hanya pada deployment production.

Baca [project-context](docs/rules/project-context.md) sekali saat belum mengenal project atau ketika konteks percakapan hilang.

## Kontrak default feature

- User/developer cukup menjelaskan kebutuhan bisnis, flow, data, dan role yang relevan. Jangan meminta mereka mengulang standar teknis starterkit.
- Secara otomatis terapkan authorization, validation, proteksi injection/mass-assignment, server-side pagination untuk data yang dapat tumbuh, query efisien, audit log, transaksi, locale/format, pola Livewire/Alpine, UI state, migration production-safe, dan test sesuai rule pemilik.
- Seluruh feature untuk app/subdomain wajib mengikuti struktur `Apps/<Subdomain>` pada layer yang dipakai. Baca `docs/rules/architecture.md` sebelum membuat file feature app baru; jangan mencampurnya dengan folder Starter atau root project.
- Migration feature app wajib berada di `database/migrations/apps/<subdomain>/`, bukan root `database/migrations`; folder tersebut dimuat otomatis saat perintah Artisan migration berjalan.
- Asset CSS/JS custom halaman app wajib berada pada Blade asset yang berdekatan dengan view pemiliknya. Gunakan Alpine untuk UI kecil, Livewire hanya untuk kebutuhan server, dan `wire:model.defer` untuk form normal; baca `ui-ux.md` serta `performance.md` sebelum membuat interaksi atau memakai library baru.
- UI wajib berangkat dari contoh terdekat di `docs/template`. Gunakan `docs/template/template.md` sebagai atlas pencarian yang tidak mengikat: cari beberapa kandidat lintas konteks, buka satu sampai tiga sumber HTML, lalu pilih/komposisikan pola template UI aktif yang paling tepat. Jangan membuat desain/komponen hanya berdasarkan selera. Pilih komponen berdasarkan konteks, jenis, dan volume data agar halaman padat, informatif, responsif, serta profesional.
- Bila sebuah standar tidak relevan, lewati tanpa menambah code seremonial. Bila requirement meminta deviasi, jelaskan risiko dan minta keputusan eksplisit.

## Kontrak anti-asumsi

- Sebelum merencanakan atau mengubah code, telusuri flow existing dari route/menu → Livewire/controller → service → interface/repository → model/migration → test/config terkait.
- Nyatakan sesuatu sebagai kondisi existing hanya setelah dibuktikan dari code, schema, config, test, atau output command. Jangan mengarang route, tabel, kolom, role, status, config key, integration, atau business rule.
- Pisahkan requirement terkonfirmasi, temuan existing, proposal teknis, dan pertanyaan terbuka. Proposal tidak boleh ditulis seolah-olah keputusan user.
- Jika keputusan bisnis/authorization/data yang tidak dapat ditemukan akan mengubah hasil secara material, hentikan bagian yang bergantung padanya dan minta keputusan; jangan memilih diam-diam.
- Gunakan source of truth existing dan sibling terdekat. Jangan membuat layer, abstraction, helper, config, atau dependency baru bila pola project yang ada sudah menyelesaikan kebutuhan.
- Verifikasi versi framework/package dari `composer.lock`, `package-lock.json`, atau file konfigurasi aktual sebelum memakai API; jangan mengasumsikan dokumentasi versi terbaru cocok dengan dependency terpasang.
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
| Install/update clone starterkit atau extension project | `docs/installation-git-clone.md` |

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
2. Untuk implementasi, baca file flow existing dan rule yang relevan; jangan membuat artefak planning di repository kecuali diminta.
3. Untuk UI, cari `docs/template/template.md` dengan `rg` berdasarkan konteks dan komponen, lalu buka 1–3 HTML sumber yang paling relevan. Jangan membaca seluruh atlas atau `docs/template/tabler-components`.
4. Jangan menyalin ulang aturan umum ke dokumen feature.
5. Jangan membuat issue/archive atau dokumentasi planning di repository kecuali diminta eksplisit.

## Aturan eksekusi

- Pada project pemakai, folder clone `starterkit/` adalah core read-only untuk feature project. Perubahan di dalamnya hanya untuk improvement universal melalui branch/PR starterkit.
- Perubahan trivial seperti typo/dokumentasi murni dapat langsung dikerjakan bila tidak mengubah business flow, authorization, data, API, atau deployment.
- Jika user hanya meminta planning/review/diagnosis, jangan mengubah code atau state di luar artefak planning yang diminta.
- Gunakan `php artisan make:* --no-interaction` untuk file Laravel.
- Migration wajib aman untuk data production dan data existing; jangan mengandalkan database kosong.
- Setiap perubahan code/perilaku wajib memiliki atau memperbarui test yang relevan; perubahan dokumentasi murni wajib melewati pemeriksaan link dan konsistensi.
- Setelah mengubah PHP, jalankan `vendor/bin/pint --dirty --format agent`.
- Jalankan test terfokus lebih dahulu, lalu suite yang relevan.
- Jangan menghapus test tanpa persetujuan.

## Evolusi rules

- Sebelum mengeksekusi code, nilai apakah instruksi baru dari user/developer bersifat reusable dan cocok menjadi standar starterkit atau project turunannya.
- Jika cocok, minta konfirmasi singkat sebelum eksekusi. Setelah disetujui, perbarui rule pemilik yang paling relevan dalam perubahan yang sama tanpa menunggu permintaan lanjutan.
- Jangan menjadikan keputusan bisnis sekali pakai, detail satu feature, nilai rahasia, atau workaround sementara sebagai rule global.
- Tulis rule secara umum, ringkas, dapat dieksekusi, dan tidak menduplikasi rule lain. Jika bertentangan dengan rule existing, jelaskan konflik dan minta keputusan eksplisit sebelum menggantinya.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
