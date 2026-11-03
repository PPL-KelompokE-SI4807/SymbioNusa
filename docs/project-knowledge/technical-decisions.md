# Technical Decisions — SymbioNusa

> Dokumen ini mencatat keputusan teknis yang telah ditetapkan dan disepakati untuk proyek SymbioNusa, diklasifikasikan ke dalam CONFIRMED, EXISTING, PROPOSED, dan PENDING.

## 1. Stack Teknologi & Pustaka
- **PHP & Framework:** PHP 8.2+ (CI: PHP 8.3), Laravel 11. (`CONFIRMED` via `AGENTS.md` Bagian 2)
- **Frontend:** Livewire 3, Alpine.js, Tailwind CSS v4, Vite. (`CONFIRMED` via `AGENTS.md` Bagian 2)
- **Database:** MySQL 8.0 dengan mesin InnoDB dan dukungan kolom JSON. (`CONFIRMED` via `AGENTS.md` Bagian 2)
- **Pustaka PDF & Excel:** `barryvdh/laravel-dompdf` untuk invoice dan manifes; `maatwebsite/excel` untuk ekspor laporan DLH. (`CONFIRMED` via `AGENTS.md` Bagian 2)
- **Pengujian & Linting:** Pest/PHPUnit, Laravel Pint, Larastan. (`CONFIRMED` via `AGENTS.md` Bagian 2)
- **Aturan Paket:** Dilarang menambah paket Composer atau NPM baru tanpa persetujuan Project Manager. (`CONFIRMED` via `AGENTS.md` Bagian 7)

## 2. Aturan Schema-First Database
- Nama tabel, primary key (`id_<entitas>`), dan foreign key (`<tabel>_id_<entitas>`) wajib mengikuti persis dokumen DDL (`docs/DDL_ERD_SymbioNusa.sql`). (`CONFIRMED` via `AGENTS.md` Bagian 5)
- Model Eloquent wajib mendeklarasikan `$table`, `$primaryKey`, `$fillable`, dan `public $timestamps = false;` (karena DDL tidak menggunakan kolom `created_at`/`updated_at` standar). (`CONFIRMED` via `AGENTS.md` Bagian 5)
- Kolom password pada tabel `users` bernama `password_hash`. Model `User` mengoverride `getAuthPassword()`. (`EXISTING` via `app/Models/User.php`)

## 3. Pola Arsitektur Logika Bisnis
- Logika bisnis utama diletakkan di dalam direktori `app/Services/<Modul>/` atau Action. (`CONFIRMED` via `AGENTS.md` Bagian 7)
- Controller bersifat tipis (*thin controller*): bertanggung jawab menerima request, memanggil Form Request untuk validasi, memeriksa Policy, memanggil Service, dan mengembalikan respons/view. (`CONFIRMED` via `AGENTS.md` Bagian 7)
- Semua perubahan status `purchase_orders` wajib melewati service `OrderStateMachine` dan dicatat ke `order_status_history`. (`CONFIRMED` via `AGENTS.md` Bagian 6)
- Reservasi stok listing wajib dibungkus dalam `DB::transaction()` disertai `lockForUpdate()`. (`CONFIRMED` via `AGENTS.md` Bagian 5.9)

## 4. Pemisahan Modul (CODEOWNERS)
- Setiap anggota tim bertanggung jawab atas modul spesifiknya di branch masing-masing (`mayhesa`, `dhafi`, `haris`, `inaya`, `zacky`, `rosita`) untuk menghindari konflik lintas modul tanpa koordinasi. (`CONFIRMED` via `AGENTS.md` Bagian 4)

## 5. Status & Usulan Tambahan (`PENDING` / `PROPOSED`)
- Penggunaan Laravel Scheduler untuk pembatalan otomatis PO kedaluwarsa. (`PENDING` - Menunggu konfirmasi PM)
- Mekanisme queue asinkron untuk perhitungan `ImpactCalculator`. (`PROPOSED` - Usulan teknis opsional)
