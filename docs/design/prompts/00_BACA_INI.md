# Paket Prompt SymbioNusa — BACA INI DULU

Versi SymbioNusa dari bundel **"4 Prompt Updated Oktober"** (bundel prompt komunitas; tidak disimpan di repo). Disusun 17 Nov 2026. Disimpan di repo pada `docs/design/prompts/`; berkas desain yang dirujuk ada satu tingkat di atasnya (`docs/design/`).

## Kenapa perlu versi khusus

Bundel asli berasumsi dua hal yang tidak berlaku di SymbioNusa:

1. **Belum ada desain**, jadi AI membuat `DESIGN.md` dari gambar referensi. SymbioNusa sudah punya `DESIGN.md`, `STITCH_BRIEF.md`, `SCREENS.md` (37 layar), dan mockup yang diturunkan dari proposal.
2. **Backend Supabase atau Google Sheets.** SymbioNusa memakai Laravel 13 + Livewire 4 + Tailwind 4 + MySQL 8, dengan aturan tim di `AGENTS.md`.

Paket ini menyimpan mekanisme yang bagus dari bundel asli (DESIGN.md sebagai kontrak, audit 10 aturan, gerbang rencana, larangan data tiruan, verifikasi sebelum lapor) dan mengganti sisanya.

## Isi paket

| File | Untuk apa | Dipakai di | Pengganti dari bundel asli |
|---|---|---|---|
| `01_STITCH_AUDITOR.md` | Tiga mode: **AUDIT** (screenshot vs DESIGN.md), **INSPIRASI** (ambil pola dari gambar luar tanpa mengganti token), **LAYAR BARU** (prompt Stitch format `SCREENS.md`) | Chat AI yang bisa membaca gambar | `STITCH_PROMPT_GENERATOR.md`, skill Gemini |
| `02_ATURAN_UI_SYMBIONUSA.md` | 10 aturan kamus yang sudah diputuskan terhadap DESIGN.md, urutan prioritas sumber, aturan untuk celah (focus, disabled, ikon) | Ditempel bersama 01 atau 04 | Bagian "RULES" di `UIUX_TECHNICAL_DICTIONARY.md` |
| `03_STATUS_MAP.md` | ENUM DDL → label UI → varian status (dirender sebagai teks berwarna + glyph), plus daftar selisih dengan paket Stitch | Ditempel bersama 01 dan 04; dipakai di Enum PHP | (baru) |
| `04_AGENT_LARAVEL_LIVEWIRE_BUILDER.md` | Membangun layar jadi Blade + Livewire per PBI, dengan blok token `@theme` siap pakai | Coding agent di repo (Claude Code, Antigravity, Codex, OpenCode) | `AGENT_FULLSTACK_BUILDER_SUPABASE_v2.md`, `SUPABASE_BUILDER.md` |
| `90_TEMUAN_DAN_USULAN.md` | Hasil audit paket Stitch, keputusan atas selisih DDL, usulan yang tersisa | Dibaca PM dan tim | (baru) |
| `91_USULAN_PERUBAHAN_SKEMA.md` | Dua migrasi yang diajukan (draf kebutuhan; rating aspek + waktu ulasan) dan keputusan yang sengaja tanpa perubahan skema | Inaya, Zacky, PM | (baru) |

## Urutan prioritas sumber

1. DDL (`docs/DDL ERD SymbioNusa.sql`) dan aturan bisnis `AGENTS.md` §5–6
2. `DESIGN.md` (FND v12 "Kaca"), dengan `PANDUAN_KACA.md` sebagai resep praktisnya (kalau keduanya berbeda, DESIGN.md menang dan selisihnya dilaporkan)
3. `03_STATUS_MAP.md`
4. `STITCH_BRIEF.md`, `SCREENS.md`, prototipe `docs/design/prototype/`
5. `02_ATURAN_UI_SYMBIONUSA.md`
6. `UIUX_TECHNICAL_DICTIONARY.md` asli (kosakata saja)

## Alur kerja

```
Proposal + mockup
  ▼  (sudah selesai)
DESIGN.md + STITCH_BRIEF.md + SCREENS.md
  ▼  satu layar per prompt
Stitch (impor DESIGN.md, tempel brief, lalu prompt layar) — atau FigCraft (skill figcraft-fast)
  ▼
01 mode AUDIT pada hasil Stitch → perbaiki prompt bila menyimpang
  ▼
04 builder di repo, per PBI (SYM-x) → Blade + Livewire + data MySQL
  ▼
01 mode AUDIT pada screenshot halaman jadi (360 / 1024 / 1440 px) → perbaiki
  ▼
test, Pint, Larastan → PR ke staging
```

Sebelum layar pertama dibangun, ada satu tugas fondasi: token DESIGN.md di `app.css`, komponen `components/ui/*`, dan app shell (lihat Phase 1 di `04`). Tugas ini menyentuh berkas bersama, jadi butuh persetujuan PM.

## Cara pakai cepat

- **Audit hasil Stitch atau halaman jadi:** buka chat baru → tempel `01`, `02`, `03`, dan isi `DESIGN.md` → kirim screenshot + sebut nomor layar `SCREENS.md` dan sumbernya.
- **Prompt layar baru** (mis. Login, Katalog Material, Tugas Inspeksi): tempel `01`, `03`, `DESIGN.md`, `STITCH_BRIEF.md` → tulis "Mode LAYAR BARU: [nama layar], peran [..], PBI [..]".
- **Ambil pola dari inspirasi:** tempel `01`, `02`, `DESIGN.md` → kirim gambar → "Mode INSPIRASI".
- **Membangun di repo:** buka agent di root repo SymbioNusa → tempel `04` → "Kerjakan SYM-x, layar SCREENS NN". Agent membaca sendiri berkas lain lewat path yang disebut di `04`, termasuk `docs/design/PANDUAN_KACA.md` (cuplikan Kaca siap tiru, token, aturan gerak dan kontras, daftar periksa sebelum PR).

## Yang sengaja tidak dibawa dari bundel asli

| Berkas asli | Alasan |
|---|---|
| `SUPABASE_BUILDER.md`, `AGENT_FULLSTACK_BUILDER(SUPABASE).md`, `AGENT_FULLSTACK_BUILDER_SUPABASE_v2.md` | Backend berbeda. Mekanisme bagusnya pindah ke `04`. |
| `AGENT_APPSCRIPT_SHEET_BUILDER.md` | Tidak relevan. |
| Skill Gemini | Tidak bertanya, menebak konteks, memakai Tailwind CDN, dan format DESIGN.md-nya berbeda, sehingga bisa menimpa kontrak desain. |
| Aturan Tailwind CDN v3/v4 | SymbioNusa memakai Tailwind 4 lewat Vite (`@tailwindcss/vite`), bukan CDN. |
| "Kirim kode FULL setiap revisi" | Hanya relevan untuk chat; agent mengedit berkas langsung. |

## Status

- **25 Nov 2026: Kaca (FND v12) menjadi standar seluruh aplikasi** (keputusan PM). `DESIGN.md`, `02` dan `04` sudah diselaraskan; resep praktisnya di `docs/design/PANDUAN_KACA.md`. Yang sudah dibangun dengan Kaca baru dasbor Admin; layar lain dibangun ulang mengikuti panduan itu.
- `03_STATUS_MAP.md` **DISETUJUI** (17 Nov 2026, diputuskan atas delegasi PM). Baris bertanda ⏳ menunggu migrasi di `91_USULAN_PERUBAHAN_SKEMA.md`.
- `STITCH_BRIEF.md` (stepper 10 langkah, kosakata status) dan `SCREENS.md` (layar 03, 07, 18, 20, 24, 26, 27, 30, 31) sudah disamakan dengan peta status.
- Usulan bagian B di `90_TEMUAN_DAN_USULAN.md` (token `warning`, `focus-ring`, `secondary-hover`, `on-secondary-muted`, `label-caps` 12 px, aturan States/Icons/tabel) **sudah diterapkan** ke `DESIGN.md` pada 17 Nov 2026. Impor ulang DESIGN.md di Stitch.
- Bagian C (layar yang belum punya prompt) dan E (catatan kecil) di `90` belum dikerjakan.
- Prompt di paket ini belum diuji di Stitch maupun di agent; laporkan bagian yang tidak jalan supaya bisa diperbaiki.
