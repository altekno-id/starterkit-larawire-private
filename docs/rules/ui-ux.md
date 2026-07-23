# UI/UX Livewire dan Tabler

## Source UI

Gunakan Tabler yang sudah tersedia. Urutan referensi:

1. Contoh ringkas project: `docs/template/*.html`.
2. Preview lengkap: `docs/template/tabler-components/preview/pages/`.
3. Potongan komponen: `docs/template/tabler-components/shared/includes/`.
4. Dokumentasi komponen: `docs/template/tabler-components/docs/ui/`.

Jangan membuka seluruh folder. Contoh pencarian:

```bash
rg -l "modal|modal-dialog" docs/template | head
rg -l "table-responsive|pagination" docs/template | head
rg -l "form-selectgroup|form-check" docs/template | head
```

## Prinsip

- Gunakan komponen/markup Tabler existing sebelum membuat komponen custom.
- Layout content fluid sampai Full HD dan dibatasi secara wajar untuk layar sangat besar.
- Page header menjelaskan konteks dan aksi utama berada di area konten yang relevan.
- Form kompleks desktop memakai pembagian kolom agar user tidak bolak-balik scroll; mobile harus kembali satu kolom.
- Add dan edit memakai halaman sendiri jika form cukup kompleks.
- Destructive action memakai confirm modal, bukan browser alert.
- Detail sekunder memakai modal ringkas dan proporsional.
- Tabel harus padat tetapi mudah dipindai; detail teknis dipindahkan ke modal.
- Empty state harus memberi penjelasan dan next action.
- Label UI Bahasa Indonesia; pertahankan istilah familiar: username, password, email, role, module.

## Livewire

- Gunakan `wire:model.blur` atau `wire:model.defer` untuk input biasa; jangan request pada setiap ketikan tanpa kebutuhan.
- Search/filter live dapat memakai debounce yang masuk akal.
- Action yang mengirim request menampilkan loader global dan blur area melalui `starter-runtime.js`.
- Request perubahan input biasa tidak boleh memunculkan loader action.
- Modal harus ditutup/diselesaikan sebelum loader mengambil fokus agar tidak bertumpuk.
- Feedback sukses/error memakai event toast project, bukan alert.
- Link internal memakai pola navigasi Livewire yang sudah ada.

## JavaScript

Gunakan JavaScript/Alpine hanya untuk interaksi client seperti password visibility, timer auto-lock, atau UI state ringan. Business state, validation, dan authorization tetap di Livewire/server.

## Verifikasi

Uji dengan browser:

- jalur masuk dari menu, redirect, dan tombol;
- loading, modal, toast, validasi, empty state;
- data sedikit dan data banyak;
- desktop Full HD, ukuran laptop, dan mobile yang relevan.
