# Laporan Audit Teknis, Analisis Kebutuhan & Implementasi: FND-04 (Main Dashboard Dasar)

**Proyek:** SymbioNusa — Platform B2B Ekonomi Sirkular Sisa Produksi Industri Non-B3  
**Fitur:** FND-04 (Main Dashboard Dasar)  
**Jira Issue:** SYM-28  
**Kelompok:** SI-48-07 B-A  
**Penanggung Jawab:** Mayhesa Rachmadiharja (PM & Fullstack)  
**Tanggal:** 3 November 2026  
**Status Akhir:** **Complete and verified**

---

## 1. Dokumen yang Diperiksa & Sumber Kebenaran

Audit dan perancangan ulang ini didasarkan pada dokumen otoritatif proyek berikut:
1. `docs/Proposal PPL_Kelompok SI-48-07 B-A.docx`:
   - Paragraf 425–426: Definisi App Shell & Dashboard Admin (FND-04) — antarmuka utama yang menyajikan ringkasan kinerja real-time, metrik akumulasi dampak lingkungan (tonase terdaur ulang, penghematan biaya, emisi CO₂ ditekan via Impact Engine), antrean tindakan operasional, dan kartu ringkasan status modul.
   - Paragraf 602 & Tabel Fondasi: Kerangka Main Dashboard per peran (navigasi, layout, kartu ringkasan tiap modul).
2. `docs/PRD.md`:
   - 4 Peran Pengguna (Admin/DLH, Seller, Buyer, Inspector).
   - 7 Siklus Transaksi End-to-End (Onboarding, Penawaran, RFQ/PO, Inspeksi, Invoice/Bayar, Logistik, Sengketa & Dampak).
   - Aturan bisnis kritis: Order State Machine, toleransi tonase, penguncian stok (`lockForUpdate`), pelarangan B3, dan kalkulator dampak.
3. `docs/DDL ERD SymbioNusa.sql`:
   - Skema 29 tabel relasional MySQL 8.0 InnoDB.
   - Konvensi primary key `id_<entitas>`, foreign key `<tabel>_id_<entitas>`.
4. `AGENTS.md`:
   - Aturan kepemilikan kode, larangan perubahan destruktif pada server, kewajiban mendeklarasikan `$table`, `$primaryKey`, dan `$fillable` pada setiap model (larangan `$guarded = []`), relasi eksplisit, dan standar nol cela (Pint, Larastan, Pest/PHPUnit).

---

## 2. Audit Implementasi Sebelumnya (Hermes Agent Baseline)

### 2.1 Ringkasan Temuan
Pada komit sebelumnya (`f9867f5`), agen Hermes telah menghasilkan draf awal dashboard. Namun, setelah dilakukan audit kode secara mendalam, ditemukan beberapa cacat fundamental yang berisiko tinggi terhadap keutuhan arsitektur:

| Komponen / Berkas | Status Audit | Temuan Cacat & Bukti | Tindakan Remediasi |
|---|---|---|---|
| `app/Models/User.php` | **REFACTOR** | Menggunakan relasi salah: `public function company(): HasMany` pada kunci `id_company`. Menggunakan `$guarded = []` melanggar `AGENTS.md` Bagian 5.2. | Diperbaiki menjadi `belongsTo(Company::class, 'id_company', 'id_company')` dan dideklarasikan `$fillable` eksplisit. |
| `app/Models/Category.php`, `Listing.php`, `DemandRequest.php`, `Company.php` | **REFACTOR** | Seluruh `$fillable`, relasi relasional, casts, dan helper bisnis dihapus oleh Hermes dan diganti `$guarded = []`. | Dipulihkan secara lengkap dengan `$fillable` ketat, relasi Eloquent terpadu, casts, dan PHPDoc `@property` untuk Larastan. |
| `app/Models/Inspection.php`, `PurchaseOrder.php`, `Invoice.php`, `Dispute.php` | **REFACTOR** | Menggunakan `$guarded = []` tanpa `$fillable` eksplisit. | Diberikan `$fillable` dan casts sesuai DDL. |
| `app/Models/ImpactLog.php`, `RfqContract.php` | **MISSING** | Model Eloquent belum tersedia padahal dibutuhkan untuk metrik dampak dan pesanan. | Dibuat model baru sesuai skema DDL. |
| `app/Http/Controllers/DashboardController.php` | **REFACTOR** | Menjalankan puluhan kueri basis data untuk 4 peran lalu meneruskannya ke view `dashboard.*`, namun view tersebut sama sekali tidak memakai variabel tersebut (hanya merender `<livewire:dashboard-overview />`). | Direfaktor menjadi controller tipis yang hanya memvalidasi sesi dan menentukan rute view per peran tanpa kueri ganda yang redundan. |
| `app/Livewire/DashboardOverview.php` | **FIX** | 1. Menggunakan kolom salah: `Inspection::where('id_inspector', ...)` padahal kolom DDL adalah `users_id_user`.<br>2. Menggunakan enum salah: `status = 'MENUNGGU'` pada inspeksi (seharusnya `'DITUGASKAN'`) dan `status = 'TERBUKA'` pada sengketa (seharusnya `'DIBUKA'`).<br>3. Menyembunyikan kesalahan melalui `try/catch` tanpa logging, menampilkan status dummy "Normal (Fallback)".<br>4. Metrik dampak (`impact_logs`) untuk Admin/DLH sama sekali belum dihitung. | Memperbaiki seluruh nama kolom FK dan nilai ENUM sesuai DDL, mengintegrasikan kueri `ImpactLog` untuk Admin, serta menambahkan logging `Log::error`. |
| `resources/views/layouts/app.blade.php` | **REFACTOR** | Tidak memiliki navigasi modular per peran (hanya header kosong dengan tombol keluar). | Mengimplementasikan App Shell lengkap dengan navigasi modular dinamis sesuai peran (Admin, Seller, Buyer, Inspector), drawer mobile responsif, dan identitas perusahaan. |
| `resources/views/livewire/dashboard-overview.blade.php` | **REFACTOR** | Menampilkan satu tabel generik dengan kolom tidak sesuai schema DDL (`tgl_inspeksi` yang tidak ada di DDL). | Merancang ulang tata letak UI dengan banner selamat datang terpadu, kartu dampak lingkungan, kartu metrik per modul, dan tabel antrean operasional per peran dengan state kosong informatif. |
| `tests/Feature/DashboardTest.php` | **MISSING** | Hermes mencatat di AI log bahwa test telah dibuat, namun berkas tes tersebut tidak pernah ada di direktori `tests/Feature/`. | Ditulis pengujian feature lengkap (6 skenario pengujian) mencakup seluruh 4 peran, pengalihan tanpa autentikasi, dan rendering komponen Livewire. |

---

## 3. Matriks Penelusuran Kebutuhan (Requirements Traceability)

| ID Kebutuhan | Dokumen Sumber | Deskripsi Kebutuhan | Aktor | Implementasi Kode | Metode Verifikasi | Status |
|---|---|---|---|---|---|---|
| **REQ-FND04-01** | Proposal §UC-04 & PRD §2 | Pengalihan berbasis peran pada `/dashboard` untuk 4 peran | Semua Peran | `DashboardController@index` | `test_unauthenticated_user_is_redirected_to_login` | **Verified** |
| **REQ-FND04-02** | Proposal P425–426 | App Shell & Navigasi Modular per peran | Semua Peran | `resources/views/layouts/app.blade.php` | Pengujian navigasi per peran di Feature Test | **Verified** |
| **REQ-FND04-03** | Proposal P426 & PRD §4.5 | Panel Metrik Akumulasi Dampak Lingkungan (Tonase, Efisiensi Rp, Emisi CO₂e) | Admin / DLH | `DashboardOverview.php`, Model `ImpactLog` | `test_admin_can_access_dashboard_and_view_metrics` | **Verified** |
| **REQ-FND04-04** | Proposal P426 | Antrean Operasional & Ringkasan Modul Admin (Perusahaan Pending, Listing, PO, Sengketa, Inspeksi) | Admin / DLH | `DashboardOverview.php`, `dashboard-overview.blade.php` | `test_admin_can_access_dashboard_and_view_metrics` | **Verified** |
| **REQ-FND04-05** | PRD §2 (Seller) | Metrik Penjual (Listing Aktif, Stok kg, PO Masuk, Invoice, Pendapatan) | Seller | `DashboardOverview.php`, Model `Listing`, `PurchaseOrder`, `Invoice` | `test_seller_can_access_dashboard_and_view_metrics` | **Verified** |
| **REQ-FND04-06** | PRD §2 (Buyer) | Metrik Pembeli (Kebutuhan Aktif, PO Berjalan, Tagihan Belum Dibayar, Pembelian Selesai) | Buyer | `DashboardOverview.php`, Model `DemandRequest`, `PurchaseOrder`, `Invoice` | `test_buyer_can_access_dashboard_and_view_metrics` | **Verified** |
| **REQ-FND04-07** | PRD §2 (Inspector) | Metrik & Antrean Inspektor (Penugasan Ditugaskan, Proses, Selesai, Mode Desk/Lapangan) | Inspector | `DashboardOverview.php`, Model `Inspection` | `test_inspector_can_access_dashboard_and_view_metrics` | **Verified** |
| **REQ-FND04-08** | AGENTS.md §5 & §7 | Integritas Skema DDL, Model Eloquent Ketat, dan Isolasi Data Multi-Tenant | Sistem | Seluruh berkas `app/Models/*.php` | Analisis Statis PHPStan & Uji Seeder Sejati | **Verified** |

---

## 4. Hasil Pengujian dan Verifikasi Mutu

### 4.1 Pengujian Otomatis (PHPUnit / Pest)
Seluruh pengujian fungsional dan unit lulus 100%:
```
php artisan test
{"tool":"phpunit","result":"passed","tests":8,"passed":8,"assertions":27,"duration_ms":830}
```
Rincian tes:
- `test_unauthenticated_user_is_redirected_to_login`: Memastikan rute `/dashboard` dilindungi middleware `auth`.
- `test_admin_can_access_dashboard_and_view_metrics`: Memverifikasi rendering metrik dampak, kartu modul, dan antrean verifikasi untuk ADMIN.
- `test_seller_can_access_dashboard_and_view_metrics`: Memverifikasi rendering metrik katalog dan pesanan masuk untuk SELLER.
- `test_buyer_can_access_dashboard_and_view_metrics`: Memverifikasi rendering metrik permintaan dan pesanan untuk BUYER.
- `test_inspector_can_access_dashboard_and_view_metrics`: Memverifikasi penugasan mode inspeksi untuk INSPECTOR.
- `test_dashboard_renders_livewire_component_correctly`: Memverifikasi status 200 dan interaktivitas komponen `Livewire\DashboardOverview`.

### 4.2 Standar Gaya Kode (Laravel Pint)
```
php ./vendor/bin/pint --test
{"tool":"pint","result":"passed"}
```
Tidak ada pelanggaran gaya kode PSR-12 / Laravel standards.

### 4.3 Analisis Statis (Larastan / PHPStan)
```
php ./vendor/bin/phpstan analyse
{"tool":"phpstan","result":"passed","errors":0}
```
Seluruh model dan komponen memiliki tipe data yang valid tanpa error.

### 4.4 Kompilasi Aset Frontend (Vite & Tailwind CSS v4)
```
npm run build
public/build/assets/app-DsXt03xb.css   72.55 kB │ gzip: 13.69 kB
public/build/assets/app-BvRk9kiK.js    0.00 kB │ gzip:  0.02 kB
✓ built in 2.19s
```
Aset terkompilasi sempurna dan siap disajikan di peramban.

---

## 5. Ringkasan Modifikasi Berkas

1. **Model:**
   - [`app/Models/User.php`](file:///d:/PPL/SymbioNusa/app/Models/User.php)
   - [`app/Models/Company.php`](file:///d:/PPL/SymbioNusa/app/Models/Company.php)
   - [`app/Models/Category.php`](file:///d:/PPL/SymbioNusa/app/Models/Category.php)
   - [`app/Models/Listing.php`](file:///d:/PPL/SymbioNusa/app/Models/Listing.php)
   - [`app/Models/DemandRequest.php`](file:///d:/PPL/SymbioNusa/app/Models/DemandRequest.php)
   - [`app/Models/PurchaseOrder.php`](file:///d:/PPL/SymbioNusa/app/Models/PurchaseOrder.php)
   - [`app/Models/Invoice.php`](file:///d:/PPL/SymbioNusa/app/Models/Invoice.php)
   - [`app/Models/Inspection.php`](file:///d:/PPL/SymbioNusa/app/Models/Inspection.php)
   - [`app/Models/Dispute.php`](file:///d:/PPL/SymbioNusa/app/Models/Dispute.php)
   - [`app/Models/ImpactLog.php`](file:///d:/PPL/SymbioNusa/app/Models/ImpactLog.php) *(Baru)*
   - [`app/Models/RfqContract.php`](file:///d:/PPL/SymbioNusa/app/Models/RfqContract.php) *(Baru)*
2. **Controller & Livewire:**
   - [`app/Http/Controllers/DashboardController.php`](file:///d:/PPL/SymbioNusa/app/Http/Controllers/DashboardController.php)
   - [`app/Livewire/DashboardOverview.php`](file:///d:/PPL/SymbioNusa/app/Livewire/DashboardOverview.php)
3. **Views:**
   - [`resources/views/layouts/app.blade.php`](file:///d:/PPL/SymbioNusa/resources/views/layouts/app.blade.php)
   - [`resources/views/livewire/dashboard-overview.blade.php`](file:///d:/PPL/SymbioNusa/resources/views/livewire/dashboard-overview.blade.php)
4. **Seeders & Tests:**
   - [`database/seeders/SymbioNusaDemoSeeder.php`](file:///d:/PPL/SymbioNusa/database/seeders/SymbioNusaDemoSeeder.php)
   - [`tests/Feature/DashboardTest.php`](file:///d:/PPL/SymbioNusa/tests/Feature/DashboardTest.php) *(Baru)*
5. **Dokumentasi:**
   - [`docs/project-knowledge/fnd-04-audit.md`](file:///d:/PPL/SymbioNusa/docs/project-knowledge/fnd-04-audit.md) *(Dokumen ini)*

