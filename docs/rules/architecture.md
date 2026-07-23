# Arsitektur dan Source of Truth

## Lapisan

1. **Registry code-first**
   - `config/apps/<app-key>.php`: nama app, module, struktur menu, icon, dan landing.
   - `routes/apps/<app-key>.php`: route app pada subdomain.
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
   - Layout utama `resources/views/templates/layouts/app.blade.php`.
5. **Domain/data**
   - Model untuk persistence.
   - Service untuk business flow, transaksi, dan aksi lintas model.
   - Contract/repository dipakai bila memang ada boundary/query abstraction; ikuti sibling terdekat.
6. **Cross-cutting**
   - `AuditLogService`: log create/update/delete.
   - `StarterConfigService`: konfigurasi dinamis.
   - `StarterContextService`: app aktif, sidebar, branding, dan data layout.

## Registrasi route

- `routes/starter.php` didaftarkan tanpa domain dan tersedia pada semua subdomain untuk Profile, Pengaturan, Log Aktivitas, lock screen, dan endpoint session.
- `routes/web.php` hanya didaftarkan pada root `APP_DOMAIN`.
- `StarterRouteRegistrar` memasang `routes/apps/<app-key>.php` pada `<app-key>.<APP_DOMAIN>`.
- File app harus memakai nama route `<app-key>.<module-code>.<action>`.
- Route `<app-key>.anchor` adalah pengecualian untuk redirect awal app.

## Aturan perubahan

- Jangan edit metadata app/module/menu langsung di database.
- Ubah config dan route di source code, validasi, lalu sync.
- Database adalah runtime projection untuk authorization dan navigasi.
- Route yang tidak memiliki module valid akan ditolak oleh `starter:sync`.
