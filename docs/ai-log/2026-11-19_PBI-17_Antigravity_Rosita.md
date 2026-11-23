# Log AI: 2026-11-19 | PBI-17 | Antigravity | Rosita

- **PBI terkait:** SYM-21 (PBI-17: Verifikasi Dokumen Legalitas Non-B3)
- **Tujuan sesi:** Implementasi end-to-end PBI-17 Verifikasi Dokumen Legalitas Non-B3 (Model `DocumentVerification`, Factory, Policy otorisasi Inspector, Form Request `VerifyDocumentRequest`, Service `DocumentVerificationService`, Controller `DocumentVerificationController`, Blade View Layar 34 & Index, Navigasi Sidebar Inspector, dan Feature Tests PHPUnit).
- **Konteks yang diberikan ke agent (berkas/halaman):** `PANDUAN_AGENT_ANGGOTA.md` (§4, §4.8, §5), `AGENTS.md`, `Proposal_SymbioNusa_lengkap.md`, `clean_ddl.sql`, `SCREENS.md` (Layar 34), `03_STATUS_MAP.md`, dan mockups (`mockups/34-inspector-verifikasi-dokumen.png`).

## Prompt
```
Baca PANDUAN_AGENT_ANGGOTA.md (terutama bagian 4, 4.8, dan 5) dan AGENTS.md.
Nama saya Rosita. Saya mengerjakan SYM-21 = PBI 17: Verifikasi Dokumen.
Isi tiket Jira (opsional; kosongkan kalau tiketnya tidak berisi deskripsi atau acceptance criteria):

Langkah:
1. Pastikan branch saya sudah sinkron dengan staging (bagian 3).
2. Pelajari: baris PBI ini di tabel PBI docs/design/Proposal_SymbioNusa_lengkap.md, PBI ini di
   docs/SPRINT_1_BACKLOG.md (bila sprint 1), kolom "Acceptance Criteria Utama" di
   docs/project-knowledge/pbi-traceability.md, tabel terkait di DDL (catat kolom NOT NULL, ENUM, UNIQUE),
   layar terkait di docs/design/SCREENS.md dan mockup, docs/project-knowledge/pbi-dependencies.md,
   serta model dan kode yang sudah ada.
3. Susun daftar acceptance criteria bernomor (bagian 4.1) dari isi tiket Jira di atas dan dokumen tadi.
   Tandai sumber tiap butir. Kalau sumber saling bertentangan, tulis sebagai pertanyaan.
   Tunjukkan daftarnya ke saya dan BERHENTI sampai saya menyetujuinya.
4. Tulis rencana maksimal 10 baris: berkas yang dibuat/diubah, tabel yang dipakai, aturan bisnis yang
   harus dijaga, test yang akan ditulis, dan ketergantungan ke modul anggota lain.
   Tulis hal yang ambigu sebagai pertanyaan. BERHENTI dan tunggu persetujuan saya.
5. Setelah saya setuju, kerjakan berurutan: Model → Form Request + Policy → Service → Controller/Livewire
   → View (ikuti bagian 5) → Test (ikuti 4.4). Setiap butir acceptance criteria punya minimal satu test.
   Commit kecil per tonggak dengan format 4.6.
6. Jalankan test grup SYM-21, semua test, Pint, Larastan, dan npm run build. Perbaiki sampai hijau
   tanpa melemahkan test.
7. Kalau ada layar, cek tampilan di 360, 1024, dan 1440 px dan bandingkan dengan mockup.
Laporan akhir: daftar berkas yang berubah, hasil tiap perintah (ringkasan output), bagian acceptance
criteria yang sudah dan belum terpenuhi, TODO yang menunggu anggota lain, dan draf log AI.
Jangan push sebelum saya setuju.
```

## Ringkasan hasil
- Sinkronisasi branch `rosita` dengan `origin/staging`.
- Penyusunan 7 butir Acceptance Criteria bernomor dan 5 poin rencana kerja (<= 10 baris) yang telah disetujui pengguna.
- Implementasi Model `DocumentVerification`, Factory, penambahan relasi di `PurchaseOrder` dan `User`, serta pemetaan status di `StatusMap`.
- Implementasi `DocumentVerificationPolicy` (memastikan hanya Inspector yang ditugaskan yang dapat memutuskan verifikasi).
- Implementasi Form Request `VerifyDocumentRequest` dengan validasi checklist dan catatan wajib jika ditolak.
- Implementasi `DocumentVerificationService` untuk query daftar, pengumpulan dokumen legalitas/izin/listing, dan transaksi verifikasi.
- Implementasi `DocumentVerificationController` dan perutean `inspector.verifications.*` di `routes/web.php`.
- Menghubungkan menu "Verifikasi Dokumen" di sidebar Inspector (`resources/views/components/app/sidebar.blade.php`).
- Pembuatan tampilan Blade:
  - `resources/views/inspector/verifications/index.blade.php` (daftar verifikasi per PO dengan filter status)
  - `resources/views/inspector/verifications/show.blade.php` (layar 34: konteks bar, tab dokumen legalitas/izin/listing, viewer pratinjau dokumen, checklist 5 butir, catatan, dan tombol Setujui/Tolak).
- Pembuatan Feature Test `tests/Feature/DocumentVerificationTest.php` dengan grup `SYM-21` (9 test, 41 assertions).
- Pembersihan format kode menggunakan Laravel Pint (`pint --test` passed).
- Analisis statis Larastan / PHPStan (`0 errors`).
- Pengujian penuh seluruh test suite (`121 passed, 506 assertions`).
- Kompilasi aset Vite (`npm run build` sukses dalam 4.11 detik).

## Penilaian
- Dipakai:
  - Seluruh struktur Model, Policy, Service, Form Request, Controller, dan Views.
  - Komponen antarmuka `<x-ui.*>` dan token desain SymbioNusa.
  - Alur otorisasi ketat berbasis peran Inspector dan kepemilikan penugasan.
- Ditolak/diperbaiki dan alasannya:
  - Memperbaiki `CompanyDocumentFactory` yang belum ada di test dengan menggunakan `CompanyDocument::create()` langsung agar tidak memerlukan migrasi/factory baru yang bukan milik PBI ini.
  - Memperbaiki nullsafe property access pada `DocumentVerificationService` agar sesuai strict type phpstan level 5.
- Kesalahan AI yang ditemukan:
  - Awalnya mengasumsikan relasi `rfqContract` otomatis terdefinisi di docblock `PurchaseOrder`, yang memicu error phpstan properti `listings_id_listings`. Diperbaiki dengan melengkapi return type annotation di `PurchaseOrder.php`.

## Bukti
- Commit:
  - `9528b36`: `feat(SYM-21): model, policy, dan service verifikasi dokumen non-B3`
  - `c9fd70b`: `feat(SYM-21): controller dan tampilan verifikasi dokumen dengan checklist`
  - `156b84c`: `test(SYM-21): test fitur verifikasi dokumen dan otorisasi inspector`
- Test:
  - `php artisan test --group=SYM-21`: 9 passed, 41 assertions.
  - `php artisan test`: 121 passed, 506 assertions.
  - `php vendor/bin/pint --test`: passed.
  - `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed (0 errors).
  - `npm run build`: built in 4.11s.
