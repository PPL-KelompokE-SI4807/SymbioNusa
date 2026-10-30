a/D:\PPL\SymbioNusa\.hermes\IDEA.md → b/D:\PPL\SymbioNusa\.hermes\IDEA.md
@@ -0,0 +1,32 @@
+# IDEA.md — Konsep & Arsitektur Proyek SymbioNusa
+
+## 1. Visi & Ringkasan Proyek
+**SymbioNusa** adalah platform B2B ekonomi sirkular web-based untuk perdagangan sisa produksi industri non-B3 (Bahan Berbahaya dan Beracun).
+Platform ini menghubungkan empat entitas utama:
+- **Seller (Penjual)**: Industri penghasil sisa produksi non-B3.
+- **Buyer (Pembeli)**: Industri pengolah/pengguna sisa produksi sebagai bahan baku sekunder.
+- **Inspector (Verifikator Independent)**: Pihak ketiga yang melakukan uji fisik & lab kadar air/toleransi material.
+- **Admin / DLH (Dinas Lingkungan Hidup)**: Regulator dan pengawas kepatuhan transaksi & manifes lingkungan.
+
+## 2. Aturan Bisnis & Core Principles
+1. **Pure Software & Invoicing Manual**: Tidak ada fitur e-wallet atau pembayaran otomatis. Transaksi dilakukan dengan pengunggahan bukti transfer bank manual dan verifikasi invoice.
+2. **Strict Non-B3 Prohibition**: Kategori dengan bendera `is_b3_prohibited = 1` dilarang keras diperjualbelikan di platform.
+3. **State Machine Order Management**: Seluruh transisi status transaksi wajib dikendalikan oleh `OrderStateMachine` dan dicatat di `order_status_history`.
+4. **Reservasi Stok & Transaksi Safe**: Pengurangan dan pemesanan stok material wajib menggunakan pessimistic locking (`lockForUpdate()`) dalam pembungkus `DB::transaction()`.
+5. **Evaluasi Toleransi Otomatis**: Hasil timbang dan uji kadar air saat penerimaan dievaluasi oleh `ToleranceEvaluator`. Bila dalam batas toleransi → SELESAI + penyesuaian nominal; bila di luar batas → SENGKETA + auto-ticket.
+6. **Isolasi Data**: Data antar perusahaan terisolasi penuh berdasarkan `id_company` (Global Scope / Policy).
+
+## 3. Tech Stack & Infrastructure
+- **Backend Framework**: Laravel 11 (PHP 8.2+ lokal, PHP 8.3 CI)
+- **Frontend & Dynamic UI**: Livewire 3, Alpine.js, Tailwind CSS v4, Vite
+- **Database**: MySQL 8.0 (InnoDB, JSON Columns, Custom Primary Keys `id_<entitas>`)
+- **Testing & Quality Assurance**: Pest PHP / PHPUnit, Larastan, Laravel Pint
+- **CI/CD & Server**: GitHub Actions, OCI VM Staging (Internal Port 20129)
+
+## 4. PBI & Alokasi Tim (20 PBI, 4 FND Tasks)
+- **Mayhesa (PM)**: PBI-01, 02, 03 (Inspector Profile, Category Service, Impact Calculator)
+- **Dhafi**: PBI-04, 05, 06 (Order State Machine, RFQ Service, Invoice Generator)
+- **Haris**: PBI-08, 09, 10 (Company Verification, Listing Request, Supply Schedule)
+- **Inaya**: PBI-11, 12, 13 (Manifest Generator, Tolerance Evaluator, Dispute Service)
+- **Zacky**: PBI-07, 14, 15, 16 (Notification Service, Payment Controller, Watchlists)
+- **Rosita**: PBI-17, 18, 19, 20 (Inspection Advisor, Inspector Matching, Evaluator)
