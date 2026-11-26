# Log AI: 2026-11-22 | PBI-18 | Antigravity | Rosita

- **PBI terkait:** SYM-22 (PBI 18: Penugasan Inspeksi)
- **Tujuan sesi:** Implementasi end-to-end PBI-18 Penugasan Inspeksi (Model `Inspection`, Factory, Policy `InspectionPolicy`, Form Request `AssignInspectionRequest`, Service `InspectionModeAdvisor`, `InspectorMatchingService`, `InspectionAssignmentService`, Controller `InspectionAssignmentController`, Blade Views Layar 35 & Index/Detail Admin + Task List Inspector, Navigasi Sidebar Admin & Inspector, dan Feature Tests PHPUnit).
- **Konteks yang diberikan ke agent (berkas/halaman):** `PANDUAN_AGENT_ANGGOTA.md` (§4, §4.8, §5), `AGENTS.md`, `Proposal_SymbioNusa_lengkap.md`, `SPRINT_1_BACKLOG.md`, `clean_ddl.sql`, `SCREENS.md` (Layar 35), `03_STATUS_MAP.md`, `pbi-traceability.md`, `pbi-dependencies.md`, dan mockups (`mockups/35-admin-penugasan-inspeksi.png`).

## Prompt
```
Baca PANDUAN_AGENT_ANGGOTA.md (terutama bagian 4, 4.8, dan 5) dan AGENTS.md.
Nama saya Rosita. Saya mengerjakan SYM-22 = PBI 18: Penugasan Inspeksi.
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
6. Jalankan test grup SYM-22, semua test, Pint, Larastan, dan npm run build. Perbaiki sampai hijau
   tanpa melemahkan test.
7. Kalau ada layar, cek tampilan di 360, 1024, dan 1440 px dan bandingkan dengan mockup.
Laporan akhir: daftar berkas yang berubah, hasil tiap perintah (ringkasan output), bagian acceptance
criteria yang sudah dan belum terpenuhi, TODO yang menunggu anggota lain, dan draf log AI.
Jangan push sebelum saya setuju.
```

## Ringkasan hasil
- Sinkronisasi branch `rosita` dengan `origin/staging` (resolusi konflik di `PurchaseOrder.php` dan `sidebar.blade.php` dengan mempertahankan model Dhafi dan modul Rosita).
- Penyusunan 8 butir Acceptance Criteria bernomor dan 5 poin rencana kerja (<= 10 baris) yang telah disetujui pengguna.
- Implementasi Model `Inspection`, Factory `InspectionFactory` dengan berbagai state, penambahan relasi di `PurchaseOrder` dan `User`.
- Pembuatan layanan cerdas pendukung:
  - `InspectionModeAdvisor`: merekomendasikan mode `LAPANGAN` jika kategori risiko tinggi (`TINGGI`), kategori wajib sertifikat lab (`wajib_sertifikat_lab`), atau volume transaksi > 10 ton; mode `DESK` untuk kasus berisiko rendah lainnya.
  - `InspectorMatchingService`: filter inspector aktif dan tersertifikasi berlaku, perhitungan skor kecocokan (keahlian kategori 50 poin, wilayah tugas 30 poin, beban kerja 20 poin), dan pengecekan bentrok jadwal pada tanggal yang sama.
  - `InspectionAssignmentService`: orkestrasi transaksi DB, integrasi transisi status PO ke `INSPEKSI` melalui `OrderStateMachine`, auto-create penugasan `DocumentVerification` untuk transisi mulus ke PBI-17, serta dukungan pembatalan/penugasan ulang.
- Implementasi `InspectionPolicy` dan Form Request `AssignInspectionRequest`.
- Implementasi Controller `Admin\InspectionAssignmentController` dan penambahan rute `admin.inspections.*` serta `inspector.tasks.index`.
- Pembuatan antarmuka Blade:
  - `resources/views/admin/inspections/index.blade.php`: antrean PO menunggu penugasan dan log penugasan platform.
  - `resources/views/admin/inspections/create.blade.php`: Layar 35 / Mockup 35 (ringkasan PO, rekomendasi mode pintar, form override mode, daftar ranking inspector dengan skor match, pemilih jadwal & status ketersediaan).
  - `resources/views/admin/inspections/show.blade.php`: rincian inspeksi dan form pembatalan penugasan.
  - `resources/views/inspector/tasks/index.blade.php`: antarmuka inspector untuk memantau tugas inspeksi yang diberikan kepadanya.
- Pembuatan Feature Test `tests/Feature/InspectionAssignmentTest.php` dengan grup `SYM-22` (10 test methods, 36 assertions).
- Pembersihan format kode menggunakan Laravel Pint (`pint --test` passed).
- Analisis statis Larastan / PHPStan (`0 errors`).
- Pengujian penuh seluruh test suite (`144 passed, 609 assertions`).

## Penilaian
- Dipakai:
  - Seluruh struktur Model, Policy, Service, Form Request, Controller, dan Views.
  - State machine transisi PO dari `OrderStateMachine` hasil sinkronisasi staging.
  - Integrasi dua arah antara `Inspection` (PBI-18) dan `DocumentVerification` (PBI-17).
  - Token desain SymbioNusa dan komponen Blade `<x-ui.*>`.
- Ditolak/diperbaiki dan alasannya:
  - Memperbaiki nama kolom `wajib_uji_lab` menjadi `wajib_sertifikat_lab` sesuai DDL riil pada tabel `categories`.
  - Mengimpor `App\Models\InspectorProfile` pada `InspectorMatchingService` dan mendokumentasikan properti dinamis di docblock `User.php` untuk mematuhi strict type level Larastan.
- Kesalahan AI yang ditemukan:
  - Awalnya mengasumsikan nama kolom DDL kategori adalah `wajib_uji_lab`, padahal nama kolom di DDL tabel `categories` adalah `wajib_sertifikat_lab`. Dideteksi saat pengujian test suite dan langsung diperbaiki.

## Bukti
- Commit:
  - `6d29161`: `feat(SYM-22): implementasi model, service, dan policy penugasan inspeksi`
  - `c68a919`: `feat(SYM-22): controller, routes, dan views penugasan inspeksi`
  - `fa9a191`: `test(SYM-22): test fitur penugasan inspeksi dan mode advisor`
- Test:
  - `php artisan test --group=SYM-22`: 10 passed, 36 assertions.
  - `php artisan test`: 144 passed, 609 assertions (100% green).
  - `php vendor/bin/pint --test`: passed.
  - `php vendor/bin/phpstan analyse --memory-limit=1G --no-progress`: passed (0 errors).
