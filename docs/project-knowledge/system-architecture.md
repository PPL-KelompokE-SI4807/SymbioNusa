# System Architecture — SymbioNusa

> Dokumen ini menjelaskan arsitektur teknis aplikasi, teknologi yang digunakan, struktur direktori, mekanisme autentikasi, otorisasi, dan pola implementasi.

## 1. Stack Teknologi (Sesuai Proposal & AGENTS.md Bagian 2)
- **Backend:** PHP 8.2+ (CI memakai PHP 8.3), **Laravel 11**.
- **Frontend:** **Livewire 3**, Alpine.js, Tailwind CSS v4, Vite (untuk build aset).
- **Database:** **MySQL 8.0** (InnoDB, mendukung kolom JSON).
- **Autentikasi:** Laravel Breeze (login, logout, reset password).
- **Otorisasi:** Laravel Policy dan Gate untuk manajemen akses berbasis peran (RBAC).
- **Pustaka Pendukung:** 
  - `barryvdh/laravel-dompdf` (PDF invoice dan manifes).
  - `maatwebsite/excel` (ekspor laporan DLH).
  - Pest/PHPUnit (pengujian), Laravel Pint (kode style), Larastan (analisis statis).

## 2. Struktur Direktori Utama
```
SymbioNusa/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controller tipis
│   │   └── Requests/        # Form Request untuk validasi
│   ├── Models/              # Eloquent Models (menyatakan $table, $primaryKey, $timestamps = false)
│   ├── Policies/            # Laravel Policies untuk otorisasi per peran
│   └── Services/            # Logika bisnis terstruktur per modul
├── database/
│   ├── migrations/          # Migrasi Laravel (termasuk skema DDL)
│   ├── seeders/             # Database seeders
│   └── sql/                 # Berkas SQL DDL mentah (clean_ddl.sql)
├── docs/                    # Dokumentasi proyek, PRD, backlog, dan project-knowledge
├── resources/
│   ├── views/               # Blade views (Livewire components & layouts)
│   ├── css/ & js/           # Aset frontend (Tailwind & Alpine)
├── routes/
│   └── web.php              # Definisi rute web
└── tests/
    ├── Feature/             # Pest / PHPUnit feature tests
    └── Unit/                # Unit tests
```

## 3. Autentikasi & Otorisasi
- **Autentikasi:** Menggunakan Laravel Breeze dengan modifikasi pada model `User` (`app/Models/User.php`) untuk menggunakan kolom `password_hash` (`getAuthPassword()`) sesuai DDL.
- **Otorisasi:** Setiap aksi pada Controller dan komponen Livewire **wajib** dilindungi oleh Policy (`AGENTS.md` Bagian 7) berdasarkan peran pengguna (`ADMIN`, `SELLER`, `BUYER`, `INSPECTOR`).

## 4. Pola Implementasi (Architecture Patterns)
- **Thin Controller, Fat Service:** Logika bisnis diletakkan di `app/Services/<Modul>/` atau Action. Controller bertugas menerima request, memanggil Form Request untuk validasi, memeriksa Policy, memanggil Service, dan mengembalikan respons/view.
- **Transaksi Database & Concurrency:** Segala operasi lintas tabel atau reservasi stok wajib dibungkus dalam `DB::transaction()` dengan `lockForUpdate()` (`AGENTS.md` Bagian 5.9).
- **Schema-First Database:** Nama tabel, primary key (`id_<entitas>`), foreign key (`<tabel>_id_<entitas>`), dan tipe data mengikuti persis spesifikasi DDL (`docs/DDL_ERD_SymbioNusa.sql`). Sebagian besar tabel tidak menggunakan `created_at`/`updated_at`, sehingga model Eloquent wajib mendeklarasikan `public $timestamps = false;`.
