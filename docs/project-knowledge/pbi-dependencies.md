# PBI Dependencies — SymbioNusa

> Dokumen ini mengklasifikasikan dependensi antar-PBI menjadi hard dependency (teknis dan bisnis) dan soft dependency, serta mengevaluasi kemungkinan pengembangan paralel.

## 1. Hard Dependency (Ketergantungan Mutlak)
### A. Hard Dependency Teknis
- **FND-01, FND-02, FND-03 (Autentikasi & Manajemen Sesi):** Prasyarat teknis agar pengguna dapat masuk dan sesi dikenali oleh sistem dan Policy RBAC.
- **FND-04 (Main Dashboard Dasar):** Membutuhkan kerangka navigasi dasar, namun tidak menghalangi pembuatan service bisnis di balik layar.
- **PBI-08 (Profil & Verifikasi Perusahaan):** Prasyarat mutlak bagi entitas Seller dan Buyer untuk memiliki identitas dan izin terverifikasi sebelum berinteraksi dengan modul listing atau transaksi.

### B. Hard Dependency Bisnis (Alur Transaksi 7 Fase)
- **PBI-02 (Master Kategori Material) $\rightarrow$ PBI-09 (Listing Sisa Produksi) / PBI-14 (Demand Requests):** Kategori material dan validasi B3 harus ada sebelum listing atau permintaan dapat dibuat.
- **PBI-04 (RFQ & Negosiasi) $\rightarrow$ PBI-05 (Purchase Order):** Kontrak RFQ yang disepakati adalah sumber pembuatan PO.
- **PBI-05 (PO Dikonfirmasi) $\rightarrow$ PBI-18 (Penugasan Inspeksi) $\rightarrow$ PBI-17 (Verifikasi Dokumen Non-B3) $\rightarrow$ PBI-19 (Laporan Hasil Verifikasi Mutu):** Rangkaian pemeriksaan kepatuhan wajib mendahului penerbitan invoice dan pengiriman.
- **PBI-19 (Laporan Mutu Lolos) $\rightarrow$ PBI-06 (Penerbitan Invoice) $\rightarrow$ PBI-07 (Verifikasi Pembayaran) $\rightarrow$ PBI-11 (Manifes Angkut) $\rightarrow$ PBI-12 (Penerimaan Barang & ToleranceEvaluator):** Alur transaksional keuangan dan logistik berurutan mutlak sesuai proposal dan PRD.
- **PBI-12 (Di luar toleransi / Sengketa) $\rightarrow$ PBI-13 (Tiket Sengketa):** Sengketa otomatis terbuka apabila evaluasi penerimaan gagal.

## 2. Soft Dependency (Ketergantungan Lunak / Fungsional Tambahan)
- **PBI-10 (Jadwal Pasokan Berkala):** Bergantung pada keberadaan listing dasar (PBI-09), namun dapat dikerjakan secara independen setelah entitas listing stabil.
- **PBI-16 (Watchlist & Notifikasi):** Bergantung pada struktur kategori dan listing, namun berjalan sebagai fitur pemantauan asinkron.
- **PBI-20 (Pengawasan DLH) & PBI-03 (Parameter Dampak):** Tergantung pada akumulasi data transaksi, namun dapat diuji menggunakan seeder data dummy tanpa menunggu seluruh fitur sengketa selesai.

## 3. Evaluasi Pengembangan Paralel (CODEOWNERS)
Sesuai pembagian tugas CODEOWNERS (`AGENTS.md` Bagian 4), modul berikut dapat dikerjakan secara paralel karena batas ruang lingkupnya terisolasi:
- **Mayhesa:** Master Kategori, Faktor Dampak, Profil Inspector (PBI-01, PBI-02, PBI-03).
- **Dhafi:** RFQ, Purchase Order, Invoicing (PBI-04, PBI-05, PBI-06).
- **Haris:** Profil Perusahaan, Listing Sisa Produksi, Jadwal Pasokan (PBI-08, PBI-09, PBI-10).
- **Inaya:** Manifes Pengiriman, ToleranceEvaluator, Sengketa (PBI-11, PBI-12, PBI-13) + migrasi skema database.
- **Zacky:** Verifikasi Pembayaran, Demand Request, Ulasan, Watchlist (PBI-07, PBI-14, PBI-15, PBI-16).
- **Rosita:** Verifikasi Dokumen Non-B3, Penugasan Inspeksi, Laporan Mutu, Pengawasan DLH (PBI-17, PBI-18, PBI-19, PBI-20).
