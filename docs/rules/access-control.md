# Module, Route, Menu, dan Role

## Model akses

- Satu role dapat memiliki banyak module.
- Module memberikan akses ke seluruh route yang nama route-nya berada pada module tersebut.
- Menu mengikuti module yang dimiliki role dan hanya menjadi navigasi.
- Landing menentukan halaman awal role pada setiap app.
- Superuser bypass akses module.

Contoh:

```text
role: operator
  -> app1.pegawai
     -> app1.pegawai.index
     -> app1.pegawai.show
  -> app2.dashboard
```

Jika Operator membutuhkan page read-only yang berbeda dari Administrator, buat module seperti `pegawai_view` dengan route/view sendiri. Jangan menumpuk conditional role pada page CRUD penuh.

## Capability global

Route statik tidak dimiliki module app:

- `can_manage_settings`: akses Pengaturan, Roles, Users, Profil Perusahaan, dan Keamanan.
- `can_view_logs`: akses Log Aktivitas.

Middleware:

- `starter.admin` memeriksa `canManageSettings()`.
- `starter.logs` memeriksa `canViewLogs()`.
- Superuser selalu memiliki keduanya.

Capability global ditentukan pada form role dan disimpan di `starter_client_roles`.

## Perlindungan wajib

- Seluruh route app: `auth:web`, `starter.active`, `starter.password-change`, `starter.lock`.
- Seluruh route page module: `starter.authorize`.
- Action Livewire tetap harus memvalidasi dan mengotorisasi data yang dimanipulasi.
- Jangan mengandalkan menu tersembunyi sebagai security.
- Role/user Superuser tidak boleh terlihat, diedit, dipindahkan, atau dihapus oleh non-superuser.
- Password akun Superuser tidak boleh di-reset dari User Management, termasuk ketika actor adalah Superuser itu sendiri. Superuser hanya mengubah password miliknya melalui Edit Profil Saya.
- Larangan terhadap akun sistem harus ditegakkan pada UI dan service/action server; menyembunyikan tombol saja tidak cukup.

## Sinkronisasi

`starter:sync` memetakan module dari segmen kedua nama route. Karena itu:

- benar: `app1.pegawai.index`
- salah: `app1.data-pegawai.index` jika code config adalah `pegawai`

Selalu dry run sebelum sync yang menulis database.
