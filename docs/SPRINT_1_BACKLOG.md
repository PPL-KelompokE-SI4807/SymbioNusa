# Sprint 1 Product Backlog — SymbioNusa

> Rincian 20 Product Backlog Item (PBI) fungsional bisnis beserta 4 Fondasi Kolektif (FND) untuk proyek SymbioNusa.
> Setiap anggota tim bertanggung jawab secara *end-to-end* (Migration, Model, Request, Policy, Livewire/Controller, View, Pest Test) atas PBI masing-masing.

---

## 1. Fondasi Kolektif (Dikerjakan Bersama / Sebelum PBI Utama)

| ID | Nama Fondasi | Deskripsi & Cakupan |
|---|---|---|
| **FND-01** | Login | Autentikasi email & kata sandi (Laravel Breeze), manajemen sesi, pengalihan dashboard berdasarkan role. |
| **FND-02** | Logout | Mengakhiri sesi aktif dan meregenerasi token CSRF. |
| **FND-03** | Reset Password | Permintaan tautan reset via email dengan token berbatas waktu dan form ubah password baru. |
| **FND-04** | Main Dashboard Dasar | Kerangka halaman utama per peran (navigasi, layout, kartu ringkasan). Setiap modul menyumbang satu kartu ringkasan. |

---

## 2. Daftar PBI Sprint 1 (Berdasarkan Penanggung Jawab)

### A. Mayhesa Rachmadiharja (Project Manager & Master Data / Impact Module)
*Total Beban: 13 SP (+ ImpactCalculator engine)*

- **PBI-01: Registri Inspector & Sertifikasi Kompetensi** (3 SP | Update-dominant)
  - Admin mendaftarkan Inspector beserta bidang keahlian (kategori material), wilayah tugas, nomor & berkas sertifikasi, serta masa berlaku.
  - Status Inspector otomatis nonaktif jika sertifikasi kedaluwarsa. Hanya Inspector aktif yang bisa ditugaskan.
- **PBI-02: Master Kategori Material, Templat Spesifikasi & Toleransi** (5 SP | Create-dominant)
  - Admin mengelola kategori material hierarkis (induk & subkategori), mendefinisikan templat spesifikasi parameter (kadar air, ukuran, kebersihan), parameter wajib, dan penanda B3 (`is_b3_prohibited`).
  - Menetapkan toleransi tonase (%) dan batas kadar air maksimum per kategori.
- **PBI-03: Parameter Dampak & Faktor Emisi** (5 SP | Read-dominant)
  - Admin mengelola faktor emisi (pembuangan awal, bahan primer, pemrosesan, angkutan), substitution rate, dan harga referensi.
  - Setiap perubahan membuat **versi baru** dengan tanggal berlaku (versi historis tetap terjaga untuk order lama). Dilengkapi simulasi pratinjau dampak.

### B. Achmad Dhafi Fadilah (Transaction Module)
*Total Beban: 18 SP*

- **PBI-04: RFQ & Negosiasi Penawaran** (8 SP | Create-dominant)
  - Buyer mengajukan RFQ atas listing aktif (volume, harga, tenggat).
  - Seller merespons: terima, tolak, atau **penawaran balik (counter-offer)** dalam utas negosiasi bertahap (maks 5 putaran). Kontrak yang disepakati mengunci harga dan volume.
- **PBI-05: Purchase Order (PO)** (5 SP | Update-dominant)
  - Buyer membuat PO dari kontrak RFQ yang disepakati; menyalin snapshot toleransi tonase dan kadar air dari kategori.
  - Seller konfirmasi/tolak PO. Saat dikonfirmasi, sistem mereservasi stok listing menggunakan **`lockForUpdate()`** dalam transaksi DB.
- **PBI-06: Penerbitan Invoice** (5 SP | Create-dominant)
  - Seller menerbitkan invoice resmi atas PO yang berstatus *Lolos Inspeksi*.
  - Nomor invoice dibuat otomatis, rekap umur tagihan, unduh PDF (`barryvdh/laravel-dompdf`), dan fitur *void* (pembatalan dengan jejak audit).

### C. Haris Permana (Seller Catalog Module)
*Total Beban: 18 SP*

- **PBI-08: Profil & Verifikasi Legalitas Perusahaan** (5 SP | Update-dominant)
  - Perusahaan mengelola profil (NIB, alamat, koordinat, kontak, tipe mitra: Seller/Buyer/Keduanya) dan mengunggah dokumen legalitas & lingkungan.
  - Admin meninjau dan menyetujui/menolak. Hanya perusahaan terverifikasi yang boleh bertransaksi.
- **PBI-09: Listing Sisa Produksi** (8 SP | Create-dominant)
  - Seller membuat listing (kategori, spesifikasi templat, volume, harga, lokasi, metode buang sebelumnya, biaya buang) dan melampirkan bukti Non-B3 (surat pernyataan, foto sampel, uji lab).
  - Sistem otomatis menolak jika kategori B3. Katalog menyediakan pencarian dan penyaringan.
- **PBI-10: Jadwal Pasokan Berkala** (5 SP | Read-dominant)
  - Seller menetapkan jadwal pasokan berulang (mingguan/bulanan) per listing dengan volume per periode beserta tampilan kalender dan proyeksi volume.

### D. Inaya Keisha Sukmawardy (Logistics & Fulfillment Module)
*Total Beban: 18 SP*

- **PBI-11: Pengiriman & Manifes Angkut** (5 SP | Create-dominant)
  - Seller mencatat logistik pengiriman (armada, plat, sopir, rute, berat dikirim).
  - Syarat: sudah dibayar & verifikasi dokumen Non-B3 disetujui. Sistem otomatis menerbitkan **Manifes Angkut PDF**.
- **PBI-12: Penerimaan Barang & Realisasi Tonase** (8 SP | Update-dominant)
  - Buyer mencatat berat diterima, berat ditolak, kadar air, dan kondisi material.
  - **ToleranceEvaluator** mengevaluasi terhadap toleransi PO: dalam toleransi → order *Selesai* & cetak adjustment note; di luar toleransi → buka tiket *Sengketa* otomatis.
- **PBI-13: Sengketa & Klaim Ketidaksesuaian** (5 SP | Delete-dominant)
  - Pengelolaan tiket sengketa oleh pihak yang dirugikan atau sistem (selisih tonase/kadar air). Admin memberikan putusan penyelesaian (penyesuaian harga, penggantian, atau pembatalan).

### E. M Zacky Aghaatsa (Buyer Demand, Payment & Engagement Module)
*Total Beban: 16 SP*

- **PBI-07: Verifikasi Pembayaran** (3 SP | Update-dominant)
  - Buyer mengunggah bukti transfer pembayaran. Seller memeriksa dan memverifikasi atau menolak dengan alasan. Verifikasi valid mengubah status order menjadi *Dibayar*.
- **PBI-14: Kebutuhan Bahan Baku Sekunder** (5 SP | Create-dominant)
  - Buyer memublikasikan kebutuhan bahan baku (kategori, spesifikasi, volume, frekuensi, harga maksimal). Seller melihat melalui papan kebutuhan.
- **PBI-15: Ulasan & Rating Mitra** (3 SP | Read-dominant)
  - Setelah order *Selesai*, kedua pihak memberi ulasan (mutu, ketepatan, komunikasi) dan menyuntingnya dalam 7 hari. Sistem menghitung skor rata-rata. Admin dapat memoderasi ulasan.
- **PBI-16: Daftar Pantau & Peringatan Material** (5 SP | Delete-dominant)
  - Pengguna membuat kriteria pantauan (kategori, wilayah, rentang volume/harga) dan menerima notifikasi in-app otomatis saat listing/kebutuhan yang cocok dipublikasikan.

### F. Rosita Jian Syahiirah (Inspection & Compliance Module)
*Total Beban: 18 SP*

- **PBI-17: Verifikasi Dokumen Legalitas Non-B3** (5 SP | Update-dominant)
  - Inspector memeriksa dokumen per order menggunakan checklist (pernyataan Non-B3, legalitas, izin lingkungan, bukti listing) dan menyetujui atau menolak.
- **PBI-18: Penugasan Inspeksi** (5 SP | Create-dominant)
  - Admin menugaskan Inspector untuk order yang terkonfirmasi berdasarkan keahlian. Sistem merekomendasikan mode **Desk** (bukti digital) atau **Lapangan** (pemeriksaan fisik) berdasarkan tingkat risiko.
- **PBI-19: Laporan Hasil Verifikasi Mutu** (5 SP | Create-dominant)
  - Inspector mengisi laporan verifikasi mutu berdasarkan parameter templat kategori (nilai parameter, sumber bukti, lampiran). Status lulus/gagal mengubah status order menjadi *Lolos* atau *Gagal Inspeksi*.
- **PBI-20: Pengawasan & Tindak Lanjut DLH** (3 SP | Read-dominant)
  - DLH mencatat temuan atau peringatan terhadap perusahaan (jenis, deskripsi, tenggat), memperbarui status tindak lanjut (*Temuan, Peringatan, Ditindaklanjuti, Selesai*), dan mengekspor laporan.

---

## 3. Total Beban dan Bobot Sprint 1
- **Total PBI:** 20 PBI fungsional + 4 Fondasi
- **Total Story Points:** 101 SP (Rata-rata ~17 SP per anggota)
- **Target Integrasi:** Semua branch anggota di-rebase, diuji (`php artisan test`), dan di-merge secara berkala ke branch `staging`.
