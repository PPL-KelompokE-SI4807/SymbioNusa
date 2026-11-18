# Log AI: 2026-11-17 | Desain & dokumentasi (lintas PBI) | Claude Code (aplikasi Claude desktop) | Mayhesa

- **PBI terkait:** lintas PBI (fondasi UI, PBI-01, 08, 09, 14, 15 untuk keputusan status)
- **Tujuan sesi:** menyelaraskan bundel prompt "4 Prompt Updated Oktober" dengan SymbioNusa; mengaudit paket desain Stitch; menyusun peta status dari DDL; memindahkan paket desain ke repo dan memperbarui AGENTS.md.
- **Konteks yang diberikan ke agent (berkas/halaman):** bundel prompt komunitas (di luar repo), paket desain Stitch milik PM (DESIGN.md, STITCH_BRIEF.md, SCREENS.md, 37 mockup), prototipe Dashboard Admin, `AGENTS.md`, `docs/DDL ERD SymbioNusa.sql`, proposal (versi Markdown), `composer.lock`, `package.json`, kode di `resources/` dan `app/Livewire/`.

## Prompt
```
- "baca [folder bundel prompt] pahami semuanya, jelaskan"
- "bagaimana folder ini bisa align sesuai dengan proyek symbio nusa"
- "apakah yang di Stitch-SymbioNusa sudah baik? ... kalau sudah yakin buatkan paket prompt versi symbionusa"
- "Ya, terapkan" (tambahan token DESIGN.md)
- "tolong anda putuskan sendiri yang baiknya gimana, saya beri permission" (selisih status vs DDL)
- "pindahin aja" / "push ke branch mayhesa yang sudah ada"
```

## Ringkasan hasil
- Audit paket Stitch: analisis visual mockup 01 dan 12, skrip kontras WCAG (30 pasangan) dan grid 4 px, perbandingan status dengan ENUM DDL (29 tabel). Hasil di `docs/design/prompts/90_TEMUAN_DAN_USULAN.md`.
- `DESIGN.md` ditambah token `warning`, `focus-ring`, `secondary-hover`, `on-secondary-muted`; `label-caps` 11 → 12 px; aturan States, Icons, baris tabel minimal 52 px.
- Paket `docs/design/prompts/`: 01 auditor (pengganti Stitch generator), 02 aturan UI, 03 peta status (DISETUJUI atas delegasi PM), 04 builder Laravel/Livewire dengan blok `@theme`, 90 temuan, 91 usulan skema.
- Keputusan selisih DDL (prinsip: alur dari proposal, nama/nilai dari DDL); `STITCH_BRIEF.md` dan `SCREENS.md` disesuaikan; dua migrasi diajukan di `91` (belum dikerjakan).
- `AGENTS.md`: stack disamakan dengan lock file (Laravel 13, Livewire 4, Tailwind 4, PHPUnit 12), aturan UI merujuk `docs/design/`, FigCraft jadi opsional, nama berkas proposal/DDL dibetulkan.

## Penilaian
- Dipakai: seluruh isi `docs/design/` dan perubahan `AGENTS.md` pada commit ini.
- Ditolak/diperbaiki dan alasannya: permintaan menyamarkan kata "Stitch" ditolak AI karena bertentangan dengan AGENTS.md §10 dan §12; diganti dengan catatan asal-usul jujur di `docs/design/README.md`. Perubahan lokal lama (routes/auth.php, view login, migrasi sessions/cache dari 3 Nov) tidak ikut di-push karena lebih tua dari login SYM-1 yang sudah di `staging`.
- Kesalahan AI yang ditemukan: hitungan awal di file 90 salah (28 warna/19 komponen; seharusnya 27/16) dan langsung diperbaiki; skrip cek kelas Tailwind sempat salah melaporkan kelas varian "missing", dicek ulang langsung di CSS hasil kompilasi dan ternyata ada.

## Bukti
- Commit/PR: commit `docs(design)` di branch `mayhesa` (lihat riwayat git).
- Test: blok `@theme` dikompilasi dengan Tailwind 4.3.3 milik repo (semua kelas token terbentuk); kecocokan 31 warna, ukuran huruf, dan radius antara `@theme` dan `DESIGN.md` dicek otomatis. Belum ada test aplikasi karena tidak ada perubahan kode.
- Catatan: sesi Google Stitch (pembuatan layar dari `SCREENS.md`) perlu log tersendiri yang diisi PM.
