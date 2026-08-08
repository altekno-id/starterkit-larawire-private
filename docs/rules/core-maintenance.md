# Canonical Starter Core Maintenance

Rule ini berlaku ketika repository yang sedang dikerjakan adalah repository
canonical `starterkit-larawire-private`, termasuk ketika repository tersebut
dibuka langsung atau melalui Git submodule pada Laravel host.

## Eksekusi langsung

- Perubahan core starterkit yang diminta langsung oleh developer dieksekusi
  tanpa prosedur konfirmasi pemahaman, file planning, file `issues/*.md`,
  persetujuan spesifikasi, atau persetujuan eksekusi tambahan.
- Lakukan discovery source yang relevan, implementasi perubahan, verifikasi
  secara proporsional, lalu laporkan hasilnya.
- Jangan membuat atau mengarsipkan issue feature/bug milik Laravel host untuk
  maintenance repository canonical starterkit.
- Permintaan developer yang secara eksplisit mengubah rule starterkit sudah
  menjadi konfirmasi perubahan rule; jangan meminta konfirmasi duplikat.
- Tetap minta keputusan hanya jika terdapat pilihan bisnis, authorization,
  data, atau scope material yang benar-benar belum ditentukan dan tidak dapat
  dibuktikan dari source.

## Batas penerapan

- Pengecualian ini hanya untuk perubahan core universal di repository
  canonical starterkit.
- Ketika starterkit dipakai untuk mengembangkan feature atau memperbaiki bug
  aplikasi Laravel turunannya, prosedur confirmation → `issues/*.md` → approval
  → implementation pada `feature-development.md` tetap wajib.
- Feature project tidak boleh disamarkan sebagai core improvement untuk
  melewati prosedur aplikasi turunan.

## Git dan verifikasi

- Jika dikerjakan melalui submodule, pastikan perubahan core berada pada branch
  `master`, bukan detached HEAD, sebelum commit dan push.
- Verifikasi core melalui Laravel host bila perubahan menyentuh integrasi
  framework, installer, Artisan, migration, route, Livewire, theme, atau asset.
- Jangan menyinkronkan commit starterkit ke Laravel host mana pun, termasuk
  `dosen/dosen-bo` dan `starterkit-test`, kecuali developer memerintahkannya
  secara eksplisit pada task yang sedang berjalan. Permintaan perubahan atau
  push repository canonical tidak otomatis mengizinkan sinkronisasi host.
- Jika sinkronisasi host diperintahkan, ikuti mekanisme integrasi aktual host,
  perbarui gitlink submodule, lalu jalankan dependency, `starter:sync`, dan
  verifikasi relevan sebelum commit atau push host sesuai instruksi developer.
