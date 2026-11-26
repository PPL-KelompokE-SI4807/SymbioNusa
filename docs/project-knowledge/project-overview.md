# Project Overview — SymbioNusa

> Dokumen ini merangkum konsep bisnis, tujuan, masalah, stakeholder, aktor, dan value proposition platform SymbioNusa berdasarkan dokumen proposal akademik, PRD, dan pedoman kerja.

## 1. Konsep Bisnis
SymbioNusa adalah platform web B2B (Business-to-Business) ekonomi sirkular untuk pengelolaan sisa produksi industri non-B3 di Indonesia. Platform ini mempertemukan penghasil sisa produksi industri (**Seller**) dengan pengolah atau pembeli bahan baku sekunder (**Buyer**), yang diverifikasi oleh verifikator independen/internal (**Inspector**) dan diawasi oleh instansi pengawas lingkungan (**Admin / DLH**).

- **Sumber Referensi:** `docs/PRD.md` (Bagian 1), `docs/Proposal PPL_Kelompok SI-48-07 B-A.docx`.
- **Sifat Proyek:** *Pure software* tanpa integrasi IoT atau e-wallet. Segala transaksi keuangan dilakukan melalui invoice dan transfer bank manual yang diverifikasi lewat unggahan bukti transfer.

## 2. Masalah yang Diselesaikan
- Pengelolaan dan pembuangan sisa produksi industri non-B3 sering kali tidak terdokumentasi dengan baik dan memakan biaya tinggi.
- Kurangnya transparansi dan akses pasar bagi industri daur ulang untuk mendapatkan pasokan bahan baku sekunder yang terverifikasi.
- Keterbatasan instansi pengawas (DLH) dalam memantau pergerakan sisa produksi industri secara digital dan terintegrasi.

## 3. Tujuan (Objectives)
- Menyediakan platform marketplace B2B dan manajemen transaksi sisa produksi non-B3 yang transparan dan akuntabel.
- Memastikan kepatuhan terhadap regulasi lingkungan (UU No. 32 Tahun 2009 & PP No. 22 Tahun 2021).
- Menyediakan perhitungan otomatis dampak lingkungan (penghematan tonase, penghematan biaya, dan pengurangan emisi $\text{CO}_2$ ekuivalen) guna mendukung SDG 12.
- Menyediakan audit trail dan pelaporan bagi Dinas Lingkungan Hidup (DLH).

## 4. Stakeholder & Empat Peran Pengguna
| Peran | Tanggung Jawab Utama | Sumber Referensi |
|---|---|---|
| **Admin / DLH** | Verifikasi perusahaan, moderasi listing, penugasan Inspector, memutus sengketa, dan pemantauan dasbor dampak lingkungan serta catatan kepatuhan perusahaan. | `docs/PRD.md` (Bagian 2) |
| **Seller** | Mendaftarkan & mempublikasikan listing sisa produksi (dengan validasi non-B3), menanggapi RFQ, menerbitkan invoice, dan mencatat data logistik pengiriman. | `docs/PRD.md` (Bagian 2) |
| **Buyer** | Mencari & mengajukan *Demand Request*, melakukan RFQ dan negosiasi, menerbitkan *Purchase Order* (PO), mengunggah bukti pembayaran, serta mengkonfirmasi penerimaan barang. | `docs/PRD.md` (Bagian 2) |
| **Inspector** | Memverifikasi dokumen legalitas non-B3 dan mutu material (melalui mode *Desk* atau *Lapangan*) berdasarkan penugasan sistem. | `docs/PRD.md` (Bagian 2) |

## 5. Value Proposition
- **Transparansi & Kepatuhan:** Memastikan setiap material non-B3 terverifikasi dokumennya dan menolak secara otomatis material yang dikategorikan B3 (`is_b3_prohibited = 1`).
- **Efisiensi Rantai Pasok B2B:** Mempertemukan suplai industri dan kebutuhan bahan baku sekunder secara langsung dengan sistem negosiasi terstruktur dan reservasi stok atomik (`lockForUpdate()`).
- **Pengukuran Dampak Terukur:** Kalkulator dampak lingkungan otomatis per transaksi selesai (`ImpactCalculator`) yang menerjemahkan tonase daur ulang menjadi metrik emisi dan penghematan biaya.
- **Sistem Toleransi & Sengketa Otomatis:** Evaluasi penerimaan barang berbasis persentase toleransi tonase dan kadar air (`ToleranceEvaluator`) yang secara otomatis membuka tiket sengketa jika melampaui batas.
