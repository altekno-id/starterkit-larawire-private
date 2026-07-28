# App dan Subdomain Baru

## App pertama saat instalasi

- Installer interaktif menanyakan kode/subdomain dan nama App pertama setelah
  konfirmasi reset database.
- Input kode boleh dikosongkan. Instalasi tanpa App tetap valid; root landing,
  autentikasi, profil, pengaturan, user/role, dan log tetap dapat digunakan.
- Tanpa App, redirect setelah login jatuh ke halaman global pertama yang
  diizinkan dan landing onboarding menjadi petunjuk pembuatan App.
- Jangan membuat App dummy hanya agar instalasi berhasil. Buat App pertama saat
  boundary bisnis dan subdomainnya sudah diketahui.

## Generator resmi

```bash
php artisan starter:make-app <subdomain> \
  --name="Nama App" \
  --description="Deskripsi singkat" \
  --icon=apps
```

Subdomain hanya boleh berisi huruf kecil, angka, dan tanda hubung internal. Command membuat:

- `config/apps/<subdomain>.php`
- `routes/apps/<subdomain>.php`
- dashboard Livewire
- view dashboard
- feature test route
- metadata database melalui `starter:sync`

Gunakan `--no-sync` hanya jika file perlu dilengkapi sebelum metadata diterapkan.

## Setelah generate

1. Periksa namespace/class hasil generator.
2. Lengkapi module dan menu di config.
3. Lengkapi route bernama `<subdomain>.<module>.<action>`.
4. Jalankan `starter:sync <subdomain> --dry-run`, lalu `--force`.
5. Berikan module kepada role yang relevan.
6. Konfigurasikan DNS/vhost wildcard atau subdomain eksplisit menuju folder `public`.
7. Pastikan cookie/session domain mendukung root dan subdomain sesuai environment.
8. Jalankan test generator, route, authorization, dan browser.

## Migration app

- Saat app memerlukan tabel, letakkan migration di `database/migrations/apps/<subdomain>/`. Folder dibuat otomatis oleh `make:migration` bila belum ada dan akan dimuat otomatis oleh starter saat `php artisan migrate` dijalankan.
- Gunakan nama tabel berpola `{subdomain}_{module}_{entity}` dan buat model tanpa flag `-m`, lalu migration dengan path eksplisit. Lihat `code-style.md` untuk perintah dan aturan production-safe.
- Satu deployment tetap menjalankan semua migration pending dari seluruh app lewat `php artisan migrate`; pemisahan folder adalah ownership source code, bukan database atau proses deployment terpisah.

## Navigasi lintas subdomain

- URL root/auth/app dibentuk melalui named route dan `StarterNavigation`; jangan merangkai host atau URL login/logout secara manual di view.
- Perpindahan origin antara root/auth dan app subdomain wajib memakai full-page
  browser navigation. Jangan memakai `wire:navigate` pada link yang lintas
  subdomain atau yang respons akhirnya dapat diarahkan middleware ke subdomain
  lain; CORS bukan solusi untuk navigasi halaman terautentikasi.
- `APP_URL` harus menunjuk root domain yang sama dengan `APP_DOMAIN`; middleware trusted hosts otomatis mengizinkan root dan app subdomain tanpa daftar manual.
- Form logout selalu memakai method `POST`, CSRF, action route root yang valid, dan redirect tujuan yang lolos pemeriksaan safe redirect.
- Uji login, session, lock screen, dan logout dari root domain serta app subdomain pada environment lokal dan domain production.

## Jangan dilakukan

- Jangan menambah daftar app manual di provider: registry melakukan discovery.
- Jangan membuat app hanya dengan config atau hanya dengan route; keduanya wajib ada.
- Jangan memakai route name dengan prefix app/module yang berbeda.
- Jangan membuat route app global di `routes/starter/global.php` atau `routes/starter/web.php`.
