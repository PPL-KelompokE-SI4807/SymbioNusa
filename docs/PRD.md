# PRD — SymbioNusa: Platform B2B Ekonomi Sirkular Sisa Produksi Non-B3

> Dokumen ini menjadi acuan utama bagi seluruh AI agent dan anggota tim dalam mengembangkan SymbioNusa.
> Berlaku sebagai baseline sampai ada revisi resmi dari PM.

---

## 1. Ringkasan Eksekutif

**Produk:** SymbioNusa — Marketplace B2B sekaligus sistem manajemen transaksi sisa produksi industri Non-B3.

**Visi:** Menyatukan penghasil sisa produksi (Seller) dan pengolah/pembeli bahan baku sekunder (Buyer) dalam satu platform terstruktur yang transparan, terverifikasi, dan berdampak terukur terhadap lingkungan (SDG 12).

**Konteks Regulasi:** UU No. 32 Tahun 2009 & PP No. 22 Tahun 2021 tentang pengelolaan limbah Non-B3.

**Batasan Platform:** SymbioNusa adalah penyelenggara sistem elektronik — bukan penghasil, pengangkut, pengolah, atau penerbit izin. Verifikasi platform bukan pengganti penetapan resmi instansi berwenang.

---

## 2. Empat Peran Pengguna

| Peran | Tanggung Jawab Utama |
|---|---|
| **Admin / DLH** | Verifikasi perusahaan, moderasi listing, penugasan Inspector, memutus sengketa, pantau dampak lingkungan |
| **Seller** | Daftarkan & publikasikan listing sisa produksi, tanggapi RFQ, terbit invoice, catat pengiriman |
| **Buyer** | Cari & ajukan RFQ, terbit PO, bayar invoice, konfirmasi penerimaan barang |
| **Inspector** | Verifikasi dokumen legalitas Non-B3, verifikasi mutu material (mode Desk / Lapangan) |

---

## 3. Siklus Transaksi End-to-End (7 Fase)

### Fase A — Onboarding & Verifikasi Perusahaan
1. Perusahaan (Seller/Buyer) mendaftar → upload dokumen legalitas + NIB + dokumen lingkungan.
2. Admin memverifikasi → status jadi `verified` → perusahaan boleh transaksi.
3. (B3-prohibited: material kategori B3 otomatis ditolak sistem.)

### Fase B — Penawaran & Permintaan Material
1. Seller mempublikasikan **Listing** sisa produksi (kategori, spesifikasi, volume, harga, bukti Non-B3).
2. Buyer memublikasikan **Demand Request** (kebutuhan bahan baku).
3. Sistem mencocokkan listing/demand baru vs **Watchlist** → notifikasi in-app.

### Fase C — RFQ & Negosiasi → Purchase Order
1. Buyer mengajukan **RFQ** (volume, harga, tenggat) terhadap listing aktif.
2. Seller bisa terima / tolak / **counter-offer**. Negosiasi berlangsung bolak-balik.
3. Kesepakatan → Buyer terbit **Purchase Order** (PO). Snapshot toleransi tonase & kadar air dari kategori.
4. Seller konfirmasi PO → `lockForUpdate()` reservasi stok → status: `Dikonfirmasi`.

### Fase D — Inspeksi & Verifikasi
1. Admin menugaskan **Inspector** (rekomendasi sistem: mode Desk / Lapangan).
2. Inspector verifikasi dokumen Non-B3 + mutu material (bukti digital atau pemeriksaan fisik).
3. Lulus → status: `Lolos Inspeksi`. Gagal → revisi / inspeksi ulang / order dibatalkan.

### Fase E — Invoice & Pembayaran
1. Seller terbit **Invoice** atas PO yang sudah Lolos Inspeksi.
2. Buyer transfer bank → upload bukti bayar.
3. Seller verifikasi bukti bayar → status: `Dibayar`.

### Fase F — Pengiriman & Penerimaan
1. Seller catat data logistik → sistem terbitkan **Manifes Angkut PDF** otomatis (syarat: sudah Dibayar + verifikasi Non-B3 OK + berat kirim ≤ batas atas toleransi).
2. Buyer konfirmasi penerimaan: berat aktual, kadar air, kondisi fisik.
3. **ToleranceEvaluator** mengevaluasi terhadap toleransi PO:
   - **Dalam toleransi** → Order `Selesai`, hitung adjustment note.
   - **Di luar toleransi** → Order `Sengketa`, tiket otomatis terbuka.

### Fase G — Sengketa, Dampak & Pengawasan
1. Admin memutus sengketa (penyesuaian harga / pengembalian / pembatalan).
2. **ImpactCalculator** menghitung otomatis: tonase terdaur ulang, penghematan biaya (Rp), emisi CO₂ ditekan → disimpan ke `impact_logs`.
3. Seller & Buyer saling berikan **ulasan & rating**.
4. Admin / DLH pantau **Dasbor Analitik Dampak**.

---

## 4. Aturan Bisnis Kritis (Wajib Dipatuhi Kode)

### 4.1 Order State Machine
> Semua perubahan status order HARUS melalui service `OrderStateMachine`. Dilarang `update(['status' => ...])` langsung.

Status order berurutan: `Pending → Dikonfirmasi → Inspeksi → Lolos Inspeksi → Dibayar → Dalam Pengiriman → Selesai`
Cabang: kapan saja bisa → `Sengketa` (evaluasi toleransi gagal / perselisihan) → `Selesai` atau `Dibatalkan`.

Riwayat setiap transisi HARUS dicatat ke tabel `order_status_history`.

### 4.2 Toleransi Tonase & Kadar Air
- Saat PO dibuat: `t_tolerance`, `qty_min`, `qty_max`, `moisture_max` DISALIN dari tabel `categories` ke PO (bukan referensi).
- Evaluasi penerimaan (`PBI-12`):
  - Dalam toleransi JIKA `qty_received >= qty_min` DAN (`kadar_air_terukur` opsional) `kadar_air_terukur <= moisture_max`.
  - Qty_max adalah batas atas pengiriman; kirim di bawah qty_min → sengketa otomatis.
- Adjustment note: `nilai_final = qty_received × harga_per_kg`.
- Dampak dihitung pakai `qty_received` sebagai berat as-received.

### 4.3 Stock Locking
> Operasi reservasi/pengurangan stok Wajib menggunakan `DB::transaction()` + `lockForUpdate()` pada baris listing terkait.

### 4.4 B3 Prohibition
> Setiap kali listing dibuat/diubah, sistem WAJIB menolak jika `categories.is_b3_prohibited = 1`.

### 4.5 Impact Calculator
Perhitungan dampak per order selesai (setiap variabel diambil dari versi faktor aktif saat itu):
```
tonase_daur_ulang = qty_received
penghematan_seller = qty_sent × (biaya_buang_per_kg)
penghematan_buyer = qty_received × (harga_primer_per_kg - harga_transaksi_per_kg)
emisi_co2_dihindarkan = qty_received × SR × (e_disposal - e_secondary_processing)
```
Faktor: `e_dp`, `e_pp`, `e_sp`, `SR` (substitution rate), `d` (jarak Haversine), `e_t` dikelola Admin per kategori di `PBI-03` (berversi).

### 4.6 Parameter Kebijakan Awal (config/symbionusa.php)

| Kelompok Material | t_tolerance | qty_min_default | moisture_max_default |
|---|---|---|---|
| Serat / Kain Perca | 5% | 1.000 kg | 15% |
| Ampas Organik (Tahu, Kopi, Roti) | 8% | 500 kg | 80% |
| Serbuk / Potongan Kayu | 5% | 2.000 kg | 20% |
| Plastik non-B3 | 5% | 1.000 kg | 5% |
| Lainnya | 5% | 1.000 kg | 10% |

---

## 5. Kebutuhan Non-Fungsional

| Aspek | Standar |
|---|---|
| Bahasa | PHP 8.2+ (CI: PHP 8.3), Laravel 11 |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS v4 |
| Database | MySQL 8.0 (InnoDB, ACID transaksi) |
| Autentikasi | Laravel Breeze + Policy/RBAC |
| Testing | Pest Feature/Unit — semua PBI harus punya test |
| Linting | Pint + Larastan level tinggi — 0 error |
| API Docs | Scribe |
| CI/CD | GitHub Actions: Pint → Larastan → `migrate:fresh --seed` → Pest |
| Deploy | OCI VM Staging port 20129 |
| Export | Invoice PDF: `barryvdh/laravel-dompdf`; Laporan DLH: `maatwebsite/excel` |

---

## 6. Dokumen Pendukung yang Sudah Ada

| File | Lokasi |
|---|---|
| Proposal Akademis | `docs/Proposal PPL_Kelompok SI-48-07 B-A.docx` |
| ERD & Skema 29 tabel | `docs/DDL ERD SymbioNusa.sql` |
| ERD Visual | `docs/ERD.png` |
| Aturan AI Agent | `AGENTS.md` |
| Protokol Hermes | `HERMES.md` |
| AI Log Template | `docs/ai-log/TEMPLATE.md` |

---

*Dokumen ini adalah living document. Revisi hanya melalui approval PM (Mayhesa).*
*Setiap commit & PR wajib mencantumkan Jira Issue Key `SYM-x`.*
