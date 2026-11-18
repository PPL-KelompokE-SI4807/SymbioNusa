# Log AI: 2026-11-19 | Implementasi (PBI-01) | Claude Code (aplikasi Claude desktop) | Mayhesa

- **PBI terkait:** PBI-01 / SYM-5 (Registri Inspector & Sertifikasi Kompetensi, 3 SP, Sprint 1)
- **Tujuan sesi:** sinkron dengan `staging`, lalu mengerjakan PBI-01 end-to-end (model, Form Request, Policy, service, controller, view, test) sesuai proposal, DDL, dan `docs/design/`.
- **Konteks yang diberikan ke agent (berkas/halaman):** `AGENTS.md`, playbook `.claude/skills/symbionusa-playbook`, `docs/DDL ERD SymbioNusa.sql` (`inspector_profiles`, `audit_logs`, `categories`, `users`), `docs/design/Proposal_SymbioNusa_lengkap.md` (PBI-01, UC-05, UC-33, TEK-07), `docs/design/SCREENS.md` layar 02 dan 03 beserta mockup PNG, `docs/design/STITCH_BRIEF.md`, `docs/design/prompts/00_BACA_INI.md`, `03_STATUS_MAP.md`, `04_AGENT_LARAVEL_LIVEWIRE_BUILDER.md`, `91_USULAN_PERUBAHAN_SKEMA.md`, serta kode SYM-6 (Kategori) sebagai pola.

## Prompt
```
- "saya ingin lanjut, cek apakah branch local saya udh terbaru dari staging"
- "ya pakai branch mayhesa aja, ambil staging terbaru"
- "saya yang belum apa ya?"
- "SYM-5 dulu"
- Jawaban atas 4 pertanyaan keputusan dari agent (lihat Penilaian).
- "Review dulu" → "commit" → "push aja"
```

## Ringkasan hasil
- Sinkronisasi: branch `mayhesa` di-fast-forward ke `origin/staging`; perubahan lama dari branch snapshot disimpan di stash, tidak di-merge.
- `InspectorProfile` (tabel DDL apa adanya, tanpa migrasi baru) dan `InspectorStatus` (status turunan Aktif / Segera Berakhir ≤ 30 hari / Nonaktif, label lewat `StatusMap`).
- `InspectorService`: daftarkan, ubah kompetensi, perpanjang (sekaligus aktif kembali), keluarkan (`status_aktif = 0` + alasan), unggah berkas oleh Inspector, nonaktif otomatis; semua dalam `DB::transaction()` dan tercatat di `audit_logs`. Berkas di disk privat, dihapus bila transaksi gagal.
- `InspectorProfilePolicy` (Admin mengelola; Inspector hanya profil sendiri), 5 Form Request dengan pesan Bahasa Indonesia, controller tipis.
- Perintah `inspector:nonaktifkan-kedaluwarsa` dijadwalkan harian 00:05 WIB (TEK-07).
- Scope `assignable()` dan `withExpertise()` untuk dipakai PBI-18 (penugasan inspeksi).
- Halaman: daftar dengan KPI dan saringan (SCREENS 02), form daftarkan dan ubah (SCREENS 03), detail dengan riwayat dan form Keluarkan, Perpanjang, serta Profil Kompetensi untuk Inspector (UC-33). Menu sidebar "Registri Inspector" dan "Profil Kompetensi" diaktifkan.
- Seeder demo: tiga profil Inspector (aktif, segera berakhir, kedaluwarsa).

## Penilaian
- Dipakai: semua perubahan pada commit SYM-5.
- Keputusan yang ditanyakan agent dan dijawab PM (tidak ditebak):
  - "Keluarkan dari registri" = `status_aktif = 0`, bukan hapus baris (DDL tidak punya `deleted_at`).
  - Field "Tanggal terbit" di mockup dihilangkan karena tidak ada di DDL (tanpa migrasi baru).
  - Wilayah tugas = satu dari 38 provinsi (`App\Support\Wilayah`), bukan teks bebas.
  - Inspector hanya melihat profil dan mengunggah berkas sertifikat; tanggal berlaku dan status tetap diubah Admin.
- Ditolak/diperbaiki dan alasannya:
  - Tabel daftar 7 kolom terpotong (kolom Aksi tak terlihat) di 1280–1440 px; diganti tampilan kartu di bawah 1536 px. Berbeda dari mockup di rentang itu, tetapi tidak ada aksi yang tersembunyi.
  - Bidang keahlian dibatasi ke kategori aktif non-B3 (material B3 tidak diperdagangkan, jadi tidak diinspeksi).
  - `config/app.php` (timezone UTC) tidak diubah karena hot spot tim; SYM-5 memakai `InspectorProfile::today()` (tanggal WIB). Perlu keputusan tim untuk modul lain.
- Kesalahan AI yang ditemukan:
  - Perhitungan "hari ini" awalnya memakai `Carbon::today()` (UTC), sehingga antara 00:00–07:00 WIB status sertifikat telat sehari; ketahuan dari riwayat yang menampilkan tanggal kemarin dan dari test yang gagal setelah perbaikan sebagian. Diperbaiki di model, service, request, factory, seeder, dan test; waktu riwayat ditampilkan dalam WIB.
  - Penggantian massal dengan `sed` merusak namespace di satu berkas Blade (`AppModelsInspectorProfile`); diperbaiki dengan `@use`.
  - 4 temuan Larastan (tipe kembalian, nullsafe yang tidak perlu, PHPDoc JSON) dan format Pint diperbaiki.
  - Visual QA pertama menemukan halaman tanpa CSS karena `public/build` masih hasil 3 Nov; aset dibangun ulang (`npm run build`). Juga diperbaiki: tautan yang hanya dibedakan warna, landmark `aside` ganda, nomor sertifikat terpotong di 375 px.

## Bukti
- Commit/PR: `c1965b3 feat(SYM-5): registri Inspector dan sertifikasi kompetensi (PBI-01)` di branch `mayhesa` (sudah di-push; PR ke `staging` belum dibuka).
- Test: `php artisan test` 83/83 lulus (16 baru di `InspectorRegistryTest`, grup `SYM-5`); Pint dan Larastan bersih.
- Visual QA (skill visual-qa, database preview terpisah): 0 temuan pemblokir di 320–1600 px pada daftar, daftarkan, detail, ubah, perpanjang, dan Profil Kompetensi; alur Keluarkan, Perpanjang, dan galat validasi dicoba di browser. Skor subjektif profil enterprise **belum lulus** (rata-rata 3,0 dari 3,5; aksesibilitas butuh uji screen reader yang belum dilakukan).
- Belum diverifikasi: screen reader, perangkat nyata, unggah berkas lewat browser (hanya lewat feature test).
