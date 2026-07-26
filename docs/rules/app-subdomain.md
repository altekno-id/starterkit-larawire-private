# App dan Subdomain Baru

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

## Navigasi lintas subdomain

- URL root/auth/app dibentuk melalui named route dan `StarterNavigation`; jangan merangkai host atau URL login/logout secara manual di view.
- Form logout selalu memakai method `POST`, CSRF, action route root yang valid, dan redirect tujuan yang lolos pemeriksaan safe redirect.
- Uji login, session, lock screen, dan logout dari root domain serta app subdomain pada environment lokal dan domain production.

## Jangan dilakukan

- Jangan menambah daftar app manual di provider: registry melakukan discovery.
- Jangan membuat app hanya dengan config atau hanya dengan route; keduanya wajib ada.
- Jangan memakai route name dengan prefix app/module yang berbeda.
- Jangan membuat route app global di `routes/starter.php`.
