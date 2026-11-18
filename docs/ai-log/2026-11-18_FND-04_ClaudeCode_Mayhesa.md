# Log AI: 2026-11-18 | Implementasi (fondasi UI) | Claude Code (aplikasi Claude desktop) | Mayhesa

- **PBI terkait:** FND-04 / SYM-28 (app shell), dipakai seluruh PBI
- **Tujuan sesi:** memasang token `docs/design/DESIGN.md` di Tailwind 4, membuat komponen Blade dasar, dan mengganti app shell ke sidebar kiri hijau tua sesuai DESIGN.md dan STITCH_BRIEF.md.
- **Konteks yang diberikan ke agent (berkas/halaman):** `docs/design/` (DESIGN.md, STITCH_BRIEF.md, prompts/04 builder), `AGENTS.md`, `resources/css/app.css`, `resources/views/**`, `vite.config.js`, test di `tests/Feature`.

## Prompt
```
- "mulai tugas fondasi UI itu apa maksudnya?" -> penjelasan
- "butuh saya generate di stitch dulu ga?" -> tidak perlu; sidebar hijau tua untuk semua peran
- "ya kerjakan"
```

## Ringkasan hasil
- `app.css`: token DESIGN.md di `@theme` (31 warna, radius, 10 gaya huruf); sintaks `@tailwind` v3 dihapus.
- `vite.config.js`: font Inter lewat helper `bunny()` bawaan laravel-vite-plugin (diunduh saat build, tanpa paket baru); dimuat dengan `@fonts`.
- `resources/views/components/ui/`: button, badge, card, page-header, kpi, alert, icon (path digambar sendiri), field, input, select, textarea.
- `resources/views/components/app/sidebar.blade.php` + `layouts/app.blade.php`: sidebar 240 px hijau tua dengan menu persis STITCH_BRIEF per peran (menu tanpa halaman ditampilkan non-aktif), topbar 64 px dengan breadcrumb, drawer di bawah 1024 px, pesan flash memakai `x-ui.alert`.
- Login memakai token dan komponen; halaman kategori dan dashboard: padding dobel dihapus, breadcrumb pindah ke topbar, teks hijau kontras rendah diganti token, tabel yang bisa digeser bisa dijangkau keyboard, select tanpa label diberi label.

## Penilaian
- Dipakai: semua perubahan pada commit ini.
- Ditolak/diperbaiki dan alasannya: konfigurasi preview pertama memakai `bash` yang ternyata mengarah ke WSL, diganti skrip PowerShell; isi dashboard (panel gradien) sengaja tidak dirombak karena di luar cakupan fondasi.
- Kesalahan AI yang ditemukan: kelas ukuran huruf ganda pada item sidebar aktif (diperbaiki sebelum commit).

## Bukti
- Commit/PR: commit `feat(SYM-28)` di branch `mayhesa`.
- Test: `php artisan test` 40/40 lulus; Pint dan Larastan bersih; `npm run build` berhasil.
- Visual QA (skill visual-qa): 0 temuan pemblokir di /login, /dashboard (Admin, Seller), /admin/kategori, /admin/kategori/create pada 320–1440 px; skor rubrik belum lolos untuk konsistensi (isi halaman lama belum memakai komponen) dan aksesibilitas (screen reader belum diuji).
