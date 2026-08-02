# Instruksi AI Project

Project Laravel ini menggunakan `{{STARTERKIT_DIRECTORY}}/` sebagai fondasi dan kontrak
agentic coding.

Sebelum merencanakan atau mengubah code:

1. Baca `{{STARTERKIT_DIRECTORY}}/AGENTS.md` secara lengkap sebagai kontrak utama.
2. Baca hanya rule dalam `{{STARTERKIT_DIRECTORY}}/docs/rules/` yang dirujuk oleh router tugas.
3. Perlakukan path `docs/...` yang disebut oleh kontrak starterkit sebagai
   `{{STARTERKIT_DIRECTORY}}/docs/...`; path feature seperti `app/`, `routes/apps/`,
   `resources/views/apps/`, `database/migrations/apps/`, `tests/`, dan
   `issues/` berada pada root Laravel ini.
4. Jangan mengubah source di `{{STARTERKIT_DIRECTORY}}/` untuk kebutuhan feature project.
   Perubahan di sana hanya untuk improvement universal starterkit.

Developer cukup memberikan konteks bisnis. Agent wajib menerapkan workflow,
keamanan, performa, validasi, audit, pagination, UI, testing, dan struktur file
sesuai rules tanpa meminta developer mengulang standar teknis tersebut.
