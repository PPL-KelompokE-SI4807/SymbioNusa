# Development Roadmap — SymbioNusa

> Dokumen ini menyusun tahapan roadmap pengembangan dari fondasi hingga seluruh PBI fungsional Sprint 1 berdasarkan urutan dependensi teknis dan bisnis.

## Milestone 0: Fondasi Sistem & Autentikasi
- **Cakupan:** FND-01 (Login), FND-02 (Logout), FND-03 (Reset Password), FND-04 (Main Dashboard Dasar).
- **Dependensi:** Tidak ada.
- **Prioritas:** Tertinggi (Prasyarat seluruh pengujian dan interaksi).

## Milestone 1: Master Data & Onboarding Perusahaan
- **Cakupan:** 
  - PBI-08: Profil & Verifikasi Legalitas Perusahaan (Haris)
  - PBI-01: Registri Inspector & Sertifikasi Kompetensi (Mayhesa)
  - PBI-02: Master Kategori Material, Templat Spesifikasi & Toleransi (Mayhesa)
  - PBI-03: Parameter Dampak & Faktor Emisi Berversi (Mayhesa)
- **Dependensi:** Milestone 0.
- **Prioritas:** Tinggi (Prasyarat mutlak bagi aktor untuk bertransaksi dan membuat listing).

## Milestone 2: Katalog Sisa Produksi & Demand Request
- **Cakupan:**
  - PBI-09: Listing Sisa Produksi dengan validasi B3 (`is_b3_prohibited`) (Haris)
  - PBI-10: Jadwal Pasokan Berkala (Haris)
  - PBI-14: Kebutuhan Bahan Baku Sekunder / Demand Requests (Zacky)
  - PBI-16: Daftar Pantau & Peringatan Material / Watchlists (Zacky)
- **Dependensi:** Milestone 1 (Kategori & Profil Perusahaan terverifikasi).
- **Prioritas:** Tinggi.

## Milestone 3: Transaksional Inti (RFQ & Purchase Order)
- **Cakupan:**
  - PBI-04: RFQ & Negosiasi Penawaran / Counter-Offer (Dhafi)
  - PBI-05: Purchase Order dengan snapshot toleransi & `lockForUpdate()` stok (Dhafi)
- **Dependensi:** Milestone 2 (Listing aktif & perusahaan terverifikasi).
- **Prioritas:** Sangat Tinggi (Inti platform B2B).

## Milestone 4: Inspeksi & Verifikasi Kepatuhan Dokumen
- **Cakupan:**
  - PBI-18: Penugasan Inspeksi Mode Desk / Lapangan (Rosita)
  - PBI-17: Verifikasi Dokumen Legalitas Non-B3 (Rosita)
  - PBI-19: Laporan Hasil Verifikasi Mutu & Pembaruan Status Order (Rosita)
- **Dependensi:** Milestone 3 (PO terkonfirmasi).
- **Prioritas:** Tinggi.

## Milestone 5: Keuangan, Logistik & Penerimaan Barang
- **Cakupan:**
  - PBI-06: Penerbitan Invoice Resmi & PDF dompdf (Dhafi)
  - PBI-07: Verifikasi Pembayaran & Bukti Transfer (Zacky)
  - PBI-11: Pengiriman & Penerbitan Manifes Angkut PDF (Inaya)
  - PBI-12: Penerimaan Barang & `ToleranceEvaluator` (Inaya)
  - PBI-13: Sengketa & Tiket Otomatis (Inaya)
- **Dependensi:** Milestone 4 (Lolos Inspeksi / Dikonfirmasi).
- **Prioritas:** Tinggi.

## Milestone 6: Engagement, Dampak & Pengawasan DLH
- **Cakupan:**
  - PBI-15: Ulasan & Rating Mitra pasca order selesai (Zacky)
  - PBI-20: Pengawasan & Tindak Lanjut DLH serta Ekspor Excel (Rosita)
  - Eksekusi `ImpactCalculator` untuk rekam jejak `impact_logs`.
- **Dependensi:** Milestone 5 (Order Selesai).
- **Prioritas:** Menengah.
