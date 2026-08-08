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
- Setelah commit starterkit dipush, sinkronkan perubahan ke seluruh Laravel host
  terkait yang diketahui. Saat ini host terkait adalah `dosen/dosen-bo` dan
  `starterkit-test`.
- Ikuti mekanisme integrasi aktual setiap host: perbarui dan rekam gitlink pada
  host yang memakai Git submodule; sinkronkan seluruh source tanpa metadata
  `.git` pada host yang masih memakai snapshot terlacak.
- Jalankan `composer install`, `php artisan starter:sync`, dan verifikasi relevan
  dari setiap host sebelum commit dan push perubahan host yang sudah terlacak.
