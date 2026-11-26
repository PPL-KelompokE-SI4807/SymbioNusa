# Log AI: 2026-11-18 | Implementasi (FND-03, FND-04) | Claude Code (aplikasi Claude desktop) | Mayhesa

- **PBI terkait:** FND-03 / SYM-3 (Reset Password, diambil alih PM dari Zacky pada 18 Nov 2026), FND-04 / SYM-28 (Main Dashboard)
- **Tujuan sesi:** menuntaskan semua item fondasi: tabel framework yang hilang, reset kata sandi, dan isi dasbor per peran sesuai `docs/design/`.
- **Konteks yang diberikan ke agent (berkas/halaman):** `AGENTS.md`, `docs/DDL ERD SymbioNusa.sql`, proposal (`docs/design/Proposal_SymbioNusa_lengkap.md`), `docs/design/DESIGN.md`, `docs/design/prompts/03_STATUS_MAP.md`, `docs/design/prototype/dashboard-admin-v2.html`, kode auth dan dasbor yang ada, `.env.example`, `phpunit.xml`, `.github/workflows/ci.yml`.

## Prompt
```
- "yang FND emang udah semua? soalnya itu dasarnya kan?"
- "kita ambil alih semua deh FND nya, tolong plan dan kerjakan, saya beri anda kebebasan"
```

## Ringkasan hasil
- Temuan: `.env.example` memakai driver `database` untuk sesi, cache, dan antrean, tetapi tabel `sessions`, `cache`, `jobs`, dan `password_reset_tokens` tidak pernah dimigrasikan (migrasi bawaan hanya berupa `.bak`). Test tidak menangkapnya karena `phpunit.xml` memakai driver `array`.
- Tiga migrasi baru untuk tabel framework (AGENTS.md §5.6), idempoten dengan `Schema::hasTable`.
- FND-03: lupa kata sandi dan reset lewat broker bawaan Laravel; pesan sama untuk email tidak terdaftar atau akun nonaktif (mencegah penebakan email); email berbahasa Indonesia; throttle 6/menit; layout tamu bersama untuk login, lupa, dan reset kata sandi; 9 test.
- FND-04: `DashboardService` (semua angka dari data), `App\Support\Format` dan `App\Support\StatusMap` (salinan `03_STATUS_MAP.md`), dasbor Admin mengikuti prototipe v2 (strip KPI, Alur Order dengan tahap paling tertahan, antrean tindakan dengan tab Mendesak, kartu ringkasan modul), dasbor Seller, Buyer, dan Inspector dengan KPI dan tabel; pesan galat tidak lagi menampilkan isi exception.

## Penilaian
- Dipakai: semua perubahan pada commit ini.
- Ditolak/diperbaiki dan alasannya: elemen prototipe v2 yang datanya tidak ada di DDL (target DLH, SLA, delta bulanan, grafik tonase kumulatif) sengaja tidak dibuat daripada memakai angka karangan. Anotasi `@property` ditambahkan ke model Company, Dispute, PurchaseOrder hanya untuk Larastan (tanpa perubahan perilaku).
- Kesalahan AI yang ditemukan: tipe kolom DECIMAL awalnya dianotasi `float` padahal di-cast `decimal:2` (string), diperbaiki; impor ganda di `DashboardTest` diperbaiki; tabel dasbor di 375 px menyembunyikan kolom status di luar layar, diperbaiki dengan badge di kolom pertama.

## Bukti
- Commit/PR: `feat(SYM-3)` dan `feat(SYM-28)` di branch `mayhesa`.
- Test: `php artisan test` 51/51 lulus (9 baru untuk SYM-3, 2 baru untuk SYM-28); Pint dan Larastan bersih; `npm run build` berhasil.
- Visual QA (skill visual-qa): 0 temuan pemblokir pada dasbor 4 peran dan halaman login, lupa, serta reset kata sandi di 320–1440 px.
