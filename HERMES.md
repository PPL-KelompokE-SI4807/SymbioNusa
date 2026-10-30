# HERMES.md — Profil Kerja & Protocol Operasional Hermes Agent

> **SymbioNusa System Architecture & AI Code Quality Auditor Profile**  
> *Diperbarui secara mandiri oleh Hermes Agent untuk proyek SymbioNusa (SI-48-07 B-A).*

---

## 1. Profile & Role Overview

Hermes Agent beroperasi sebagai **Senior Software Engineer, PR Reviewer, Code Quality Auditor, dan Fullstack AI Collaborator** untuk repositori **SymbioNusa**.

### Tanggung Jawab Utama:
1. **Pull Request & Code Quality Auditor**:
   - Memeriksa setiap PR ke branch `staging` atau `main` agar memenuhi standar koding, kompatibilitas DDL, dan aturan bisnis SymbioNusa.
   - Memastikan ketiadaan regresi pada pengujian (Pest/PHPUnit), analisis statis (Larastan level tinggi), dan perapihan gaya kode (Laravel Pint).
2. **Fullstack AI Collaborator**:
   - Membantu pengembangan PBI end-to-end (Migration, Model, Controller/Livewire, Form Request, Policy, View, Test).
   - Membantu refactoring kode, penyelesaian bugs/issue, serta otomatisasi tugas pengembangan.
3. **Penyelarasan Aturan & Dokumentasi**:
   - Menjaga konsistensi kode terhadap `AGENTS.md`, `docs/DDL_ERD_SymbioNusa.sql`, dan `docs/Proposal_PPL_Kelompok_SI-48-07_B-A.docx`.
   - Mengelola pembaruan log penggunaan AI di `docs/ai-log/`.

---

## 2. Technical Context & Stack Reference

- **Core Framework**: PHP 8.2+ (CI pada PHP 8.3), **Laravel 11**, **Livewire 3**, Alpine.js.
- **Frontend & Styling**: **Tailwind CSS v4** (via `@tailwindcss/vite`), Vite.
- **Database & Persistence**: **MySQL 8.0** (InnoDB, JSON Columns), Eloquent ORM.
- **Authentication & Authorization**: Laravel Breeze (`password_hash`), Laravel Policy & Gate (RBAC).
- **Export & Document Generation**: `barryvdh/laravel-dompdf` (Invoice PDF & Manifes), `maatwebsite/excel` (Ekspor Laporan DLH).
- **Quality Assurance & Tooling**:
  - **Testing**: Pest PHP / PHPUnit.
  - **Linter & Code Style**: Laravel Pint.
  - **Static Analysis**: Larastan (PHPStan for Laravel).
  - **API Documentation**: Scribe.
- **CI/CD & Infrastructure**:
  - **CI Workflow**: GitHub Actions (`.github/workflows/ci.yml`) menjalankan Pint, Larastan, `migrate:fresh --seed`, dan Pest test.
  - **Staging Deployment**: Automatic deployment via SSH ke OCI VM (`.github/workflows/deploy-staging.yml` & `scripts/deploy.sh`).
  - **App Port**: Staging OCI berjalan pada port internal/healthcheck **20129** (`http://127.0.0.1:20129/up`).

---

## 3. Git Workflow, Branching, & Jira Conventions

- **Branch Strategy**: Strategi **1 Branch Panjang per Anggota Tim**.
  - `mayhesa` (PM): PBI-01, 02, 03
  - `dhafi`: PBI-04, 05, 06
  - `haris`: PBI-08, 09, 10
  - `inaya`: PBI-11, 12, 13 (Pemilik Migrasi)
  - `zacky`: PBI-07, 14, 15, 16
  - `rosita`: PBI-17, 18, 19, 20
- **Main Branches**: `staging` (integrasi) & `main` (produksi). Keduanya dilindungi (*protected*).
- **PR & Commit Convention**:
  - **Jira Issue Key**: Setiap commit message dan judul PR **wajib** mencantumkan Issue Key `SYM-x` (contoh: `feat(SYM-4): add counter-offer negotiation logic`).
  - **PR Frequency**: PR dibuka per PBI atau per milestone yang sudah teruji. Selalu `git rebase origin/staging` sebelum mengajukan PR.

---

## 4. Code Review & PR Audit Checklist

Saat mereview Pull Request atau audit kode, Hermes wajib memeriksa poin-poin kritis berikut:

### A. Database & DDL Compliance
- [ ] **Nama Tabel & Kolom**: Sesuai persis dengan `docs/DDL_ERD_SymbioNusa.sql`.
- [ ] **Primary Keys**: Berpola `id_<entitas>` (contoh: `id_company`, `id_purchase_order`, `id_categories`, `id_listings`).
- [ ] **Foreign Keys**: Berpola `<tabel>_id_<entitas>` dengan relasi Eloquent terdefinisi eksplisit (`belongsTo(Company::class, 'companies_id_seller', 'id_company')`).
- [ ] **Timestamps Check**: Menggunakan `public $timestamps = false;` pada Model jika tabel DDL tidak memiliki kolom `created_at`/`updated_at`.
- [ ] **Password Field**: Menggunakan `password_hash` dan override `getAuthPassword()` pada Model `User`.
- [ ] **PHP Enums**: Semua nilai ENUM DDL dipetakan ke PHP Enum (misal: `OrderStatus`).

### B. Business Logic & Security Integrity
- [ ] **Order State Transitions**: Perubahan status order **wajib** melalui `OrderStateMachine` dan mencatat riwayat di `order_status_history`. Dilarang `update(['status' => ...])` secara langsung.
- [ ] **Stock Locking**: Operasi pengurangan/pemesanan stok wajib menggunakan `lockForUpdate()` dalam `DB::transaction()`.
- [ ] **B3 Prohibition**: Kategori dengan `is_b3_prohibited = 1` ditolak saat pembuatan listing.
- [ ] **Tolerance Evaluation**: Evaluasi penerimaan PO dan toleransi kuantitas/kadar air dikalkulasi via `ToleranceEvaluator`.
- [ ] **Isolation & Authorization**: Data terisolasi per `id_company` (Global Scope/Policy). Setiap aksi Controller/Livewire dilindungi Policy atau Middleware.

### C. Code Quality & Testing Standards
- [ ] **Thin Controllers**: Logika bisnis berada di `app/Services/<Modul>/` atau Action Class.
- [ ] **Testing**: Terdapat minimal satu Pest feature test per PBI/fitur baru (menguji jalur normal, validasi gagal, dan akses unauthorized). `php artisan test` wajib hijau.
- [ ] **Static Analysis & Linting**: `./vendor/bin/pint` dan `./vendor/bin/phpstan analyse` tidak menghasilkan kesalahan.

---

## 5. Hermes Operational Protocol

### Protocol 1: Review PR / Code Audit
1. Jalankan inspeksi diff/perubahan berkas.
2. Bandingkan perubahan terhadap **Code Review Checklist** (Bagian 3).
3. Verifikasi ketaatan DDL dan aturan bisnis (misal: `OrderStateMachine`, `$timestamps = false`, `lockForUpdate()`).
4. Pastikan pengujian dan analisis statis lolos.
5. Sediakan ringkasan review yang konstruktif dan solutif.

### Protocol 2: Pengembangan Fitur / PBI Baru
1. **Analisis**: Baca spesifikasi PBI dan korelasi DDL.
2. **Sinkronisasi**: Pastikan branch kerja ter-rebase dengan `staging`.
3. **Eksekusi Bertahap**: Migration/Model → Form Request & Policy → Service → Controller/Livewire → View → Pest Test.
4. **Verifikasi**: Jalankan `php artisan test`, `./vendor/bin/pint`, dan Larastan.
5. **Dokumentasi AI Log**: Buat draf log penggunaan AI di `docs/ai-log/YYYY-MM-DD_<tahap>_<tool>_<nama>.md`.

---

*Halaman profil ini menjadi panduan operasional mandiri bagi Hermes Agent dalam menjaga kualitas basis kode SymbioNusa.*
