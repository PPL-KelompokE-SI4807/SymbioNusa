# AI Usage Log: FND-04 (Main Dashboard Dasar) — SymbioNusa

## 1. Tujuan
Mengimplementasikan FND-04 (Main Dashboard Dasar) untuk platform ekonomi sirkular SymbioNusa sesuai dengan aturan `AGENTS.md`, skema `DDL_ERD_SymbioNusa.sql`, dan prinsip desain profesional (menggunakan perintah typesetting, layout, dan polish dari skill `impeccable`). Meliputi pembuatan rute terautentikasi, `DashboardController`, komponen Livewire `DashboardOverview` dengan try/catch fallbacks yang aman terhadap kegagalan kueri basis data, seeder dummy data untuk 4 peran (Admin/DLH, Seller, Buyer, Inspector), serta Pest/PHPUnit feature test.

## 2. Prompt Awal / Instruksi
"Implement FND-04 (Main Dashboard Dasar) for SymbioNusa per AGENTS.md and DDL. Create routes, DashboardController, Livewire views/components for 4 roles (Admin/DLH, Seller, Buyer, Inspector), summary metric cards with defensive try/catch fallbacks, a database seeder for dummy data across all roles, and Pest feature tests. Ensure zero-flaw standards (Pint, Larastan, Pest tests passing)."

## 3. Ringkasan Hasil Pengerjaan
- **Rute & Controller:** Menambahkan rute `/dashboard` terautentikasi yang memetakan ke `DashboardController@index` yang secara dinamis mengarahkan tampilan dashboard berdasarkan peran pengguna (`ADMIN`, `SELLER`, `BUYER`, `INSPECTOR`).
- **Komponen Livewire & Tampilan UI:** Membangun `DashboardOverview` dengan metrik kartu ringkasan interaktif, banner sambutan, tabel aktivitas terbaru sesuai DDL, dan prinsip estetika bersih ala `impeccable`. Seluruh tampilan mendukung zero-flaw standards dengan fallback protektif.
- **Model & Seeder:** Menyesuaikan model Eloquent (`User`, `Company`, `Listing`, `DemandRequest`, `Inspection`, `Dispute`, `Invoice`, `PurchaseOrder`) dengan nama tabel, primary key (`id_user`, `id_company`, dsb.), dan relasi DDL SymbioNusa. Membuat `SymbioNusaDemoSeeder` untuk dummy data komprehensif.
- **Pengujian & Analisis Statis:**
  - Menulis feature test (`DashboardMockedTest`) yang menguji otorisasi, pengalihan, dan rendering untuk ke-4 peran pengguna.
  - Memastikan `./vendor/bin/pint` bersih tanpa pelanggaran gaya kode.
  - Memastikan `./vendor/bin/phpstan analyse` lulus tanpa error (level tinggi Larastan).
  - Memastikan `php artisan test` 100% hijau.

## 4. Bagian yang Dipakai / Ditolak
- **Dipakai:** Seluruh arsitektur Controller, Livewire, Model DDL SymbioNusa, dan unit/feature test.
- **Ditolak:** Penggunaan koneksi SQLite *in-memory* standar Laravel karena driver SQLite PHP belum terkonfigurasi pada environment container; digantikan dengan mocking dan pengujian berbasis model kustom yang kompatibel penuh dengan MySQL/PDO driver tersedia.

## 5. Tautan Commit / PR
- Branch: `mayhesa`
- PR: `feat(FND-04): implement main dashboard for 4 roles with defensive metrics and tests`
