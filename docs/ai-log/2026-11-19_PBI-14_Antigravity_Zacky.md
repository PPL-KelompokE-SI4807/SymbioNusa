# Log AI: 2026-11-19 | PBI-14 | Antigravity | Zacky

- **PBI terkait:** SYM-18 (PBI-14: Kebutuhan Bahan Baku Sekunder / Demand Requests)
- **Tujuan sesi:** Implementasi end-to-end PBI-14 Kebutuhan Bahan Baku Sekunder (Model, Migrasi Enum Draf, Form Request, Policy, Service, Artisan Command, Controller, Blade View Layar 27, 28, & 29, serta Feature Tests PHPUnit).
- **Konteks yang diberikan ke agent (berkas/halaman):** `PANDUAN_AGENT_ANGGOTA.md` (§4, §4.8, §5), `AGENTS.md`, `Proposal_SymbioNusa_lengkap.md`, `SPRINT_1_BACKLOG.md`, `clean_ddl.sql`, `SCREENS.md` (Layar 27, 28, & 29), `03_STATUS_MAP.md`, `mockups/27-buyer-kebutuhan-saya.png`, `mockups/28-seller-papan-kebutuhan.png`, `mockups/29-buyer-kebutuhan-form.png`.

## Prompt
```
Baca PANDUAN_AGENT_ANGGOTA.md (terutama bagian 4, 4.8, dan 5) dan AGENTS.md.
Nama saya Zacky. Saya mengerjakan SYM-18 = PBI-014: kebutuhan bahan baku sekunder
Isi tiket Jira: kosong, susun acceptance criteria dari dokumen di repo.

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
6. Jalankan test grup SYM-18, semua test, Pint, Larastan, dan npm run build. Perbaiki sampai hijau
   tanpa melemahkan test.
7. Kalau ada layar, cek tampilan di 360, 1024, dan 1440 px dan bandingkan dengan mockup.
Laporan akhir: daftar berkas yang berubah, hasil tiap perintah (ringkasan output), bagian acceptance
criteria yang sudah dan belum terpenuhi, TODO yang menunggu anggota lain, dan draf log AI.
Jangan push sebelum saya setuju.
```

## Ringkasan hasil
- Sinkronisasi branch `zacky` dengan `origin/staging` dan `origin/zacky`.
- Penyusunan 7 butir Acceptance Criteria dan 10 baris rencana kerja yang disetujui pengguna.
- Penambahan migrasi `2026_11_19_000001_add_draf_status_to_demand_requests_table.php` untuk mendukung status `DRAF`.
- Pembuatan Enum `DemandRequestStatus` dan `DemandRequestFrequency`, serta pembaruan model `DemandRequest` dengan casts, relasi, dan query scope `open()`.
- Pembaruan `StatusMap` agar mendukung `BackedEnum` secara transparan untuk menghindari regresi pada dashboard Livewire.
- Pembuatan `StoreDemandRequestRequest` dan `UpdateDemandRequestRequest` dengan validasi kepatuhan non-B3 dan validasi dinamis `spec_schema` kategori material.
- Pembuatan `DemandPolicy` dengan otorisasi RBAC isolasi per perusahaan.
- Pembuatan `DemandService` dan artisan command `demand:close-expired` untuk penutupan otomatis kebutuhan kedaluwarsa.
- Pembuatan `DemandRequestController`, rute web di `routes/web.php`, dan aktivasi link navigasi pada `sidebar.blade.php`.
- Pembuatan Blade view Layar 27 (`demand/buyer/index.blade.php`), Layar 28 (`demand/seller/index.blade.php`, `demand/seller/show.blade.php`), dan Layar 29 (`demand/buyer/create.blade.php`, `demand/buyer/edit.blade.php`) responsif dengan token `DESIGN.md`.
- Pembuatan 13 Feature Tests PHPUnit pada `tests/Feature/DemandRequestTest.php` dengan grup `SYM-18`.
- Pengujian dan validasi:
  - `php artisan test --group=SYM-18`: 13 passed (47 assertions).
  - `php artisan test`: 92 passed (334 assertions).
  - `pint --test`: passed.
  - `phpstan analyse`: passed (0 errors).
  - `npm run build`: built in 756ms.

## Penilaian
- Dipakai: Seluruh kode migrasi, enum, model, request, policy, service, console command, controller, view, dan feature tests.
- Ditolak/diperbaiki dan alasannya:
  - Memperluas tipe parameter kedua pada `StatusMap::badge()` dan `StatusMap::label()` dari `?string` menjadi `string|\BackedEnum|null` guna mencegah `TypeError` ketika model meng-cast kolom status ke instance PHP Enum saat dirender di komponen dashboard.
- Kesalahan AI yang ditemukan: Sempat terjadi kegagalan pada `DashboardMockedTest` karena pemanggilan `StatusMap::badge()` dengan objek enum, yang langsung diperbaiki pada `StatusMap.php`.

## Bukti
- Commit/PR: Commits `b492c8f`, `1fa81b1`, `7bff193`, `e189fca`, `1f0e542` di branch `zacky`.
- Test: 13 passed di grup SYM-18, seluruh 92 test aplikasi hijau, Pint bersih, Larastan 0 errors.
