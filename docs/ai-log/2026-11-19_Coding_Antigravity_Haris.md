# Log AI: 2026-11-19 | Coding | Antigravity | Haris

- **PBI terkait:** PBI-09 (SYM-13) Listing Sisa Produksi
- **Tujuan sesi:** Implementasi end-to-end modul Listing Sisa Produksi untuk Seller dan Katalog Material untuk Buyer.
- **Konteks yang diberikan ke agent (berkas/halaman):** 
  - `PANDUAN_AGENT_ANGGOTA.md` (bagian 4, 4.8, 5)
  - `AGENTS.md` (bagian 5, 6, 8)
  - `docs/DDL ERD SymbioNusa.sql` (`listings`, `listing_attachments`, `categories`)
  - `docs/design/SCREENS.md` (Layar 20 & 21) dan `docs/design/mockups/`
  - `docs/SPRINT_1_BACKLOG.md`

## Prompt
```
Baca PANDUAN_AGENT_ANGGOTA.md (terutama bagian 4, 4.8, dan 5) dan AGENTS.md.
Nama saya <haris>. Saya mengerjakan <SYM-13> = <PBI-09: Listing Sisa Produksi>.
Isi tiket Jira kosong, susun acceptance criteria dari dokumen di repo.
...
```

## Ringkasan hasil
- Membuat Form Requests (`StoreListingRequest`, `UpdateListingRequest`) dengan validasi mutlak penolakan kategori B3 (`is_b3_prohibited = 1`) dan kewajiban berkas uji lab jika disyaratkan kategori.
- Membuat `ListingPolicy` dengan aturan RBAC ketat (hanya Seller terverifikasi dan pemilik perusahaan yang berhak mengelola listing).
- Mengimplementasikan `ListingService` dengan transaksi DB (`DB::transaction`) untuk penyimpanan data listing, upload lampiran dokumen/foto, pengarsipan, serta query katalog.
- Membuat `ListingController` (Seller) dan `CatalogController` (Katalog Material).
- Membangun antarmuka Blade menggunakan token desain `DESIGN.md` dan komponen `<x-ui.*>` untuk Layar 20 (*Listing Saya*), Layar 21 (*Form Buat & Ubah Listing*), Detail Listing, serta Halaman Katalog Material.
- Menyusun `ListingTest` (10 feature test) dengan grup `SYM-13` yang mencakup seluruh butir Acceptance Criteria.
- Seluruh 122 pengujian aplikasi lulus (100% hijau), Pint passed, Larastan passed (0 error).

## Penilaian
- Dipakai: Seluruh struktur Model, Policy, Request, Service, Controller, Blade Views, dan Feature Tests.
- Ditolak/diperbaiki dan alasannya:
  - Memperbaiki pengecekan status verifikasi perusahaan di `ListingPolicy` dari `$company->status` menjadi `$company->isVerified()` / `status_verifikasi`.
  - Menambahkan trait `AuthorizesRequests` pada `ListingController`.
  - Menyesuaikan generic types PHPDoc pagination pada `ListingService` menjadi `LengthAwarePaginator<int, Listing>` agar PHPStan bersih.
- Kesalahan AI yang ditemukan: Salah mengasumsikan kolom verifikasi perusahaan sebagai `status` alih-alih `status_verifikasi` sesuai DDL.

## Bukti
- Commit/PR: Menunggu konfirmasi dan persetujuan Haris sebelum commit dan push.
- Test: `php artisan test --group=SYM-13` (10 passed, 35 assertions), `php artisan test` (122 passed, 500 assertions).
