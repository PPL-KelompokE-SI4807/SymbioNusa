# Implementation Status — SymbioNusa

> Dokumen ini mencatat status implementasi aktual dari setiap komponen berdasarkan pemeriksaan berkas kode di dalam repositori pada branch `staging`.

## 1. Status Global
- **Fondasi Framework:** Laravel 11, Livewire 3, Tailwind CSS v4, dan struktur dasar direktori sudah terpasang.
- **Migrasi Database:** Berkas migrasi DDL (`database/migrations/2026_10_30_000001_create_symbionusa_ddl_tables.php`) bersama skema SQL mentah (`database/sql/clean_ddl.sql`) telah disiapkan untuk membuat 29 tabel relasional.
- **Model Dasar:** Model `User` (`app/Models/User.php`) sudah disesuaikan dengan kolom DDL (`password_hash`, `id_company`, `timestamps = false`).
- **Modul Fungsional (PBI-01 s.d. PBI-20):** **Belum diimplementasikan**. Belum ada file Service, Form Request, Policy, Controller, atau komponen Livewire khusus untuk modul-modul fungsional di dalam direktori `app/`.

## 2. Evidensi Kode Aktual
| Komponen / Berkas | Lokasi Berkas | Status Aktual | Catatan |
|---|---|---|---|
| Skema DDL SQL | `database/sql/clean_ddl.sql` | **Ada (Lengkap)** | Memuat 29 pernyataan `CREATE TABLE IF NOT EXISTS`. |
| Migrasi DDL Laravel | `database/migrations/2026_10_30_000001_create_symbionusa_ddl_tables.php` | **Ada** | Menjalankan SQL DDL dari `clean_ddl.sql`. |
| Model User | `app/Models/User.php` | **Ada (Disesuaikan)** | Menggunakan `$table = 'users'`, `$primaryKey = 'id_user'`, `$timestamps = false`, dan `password_hash`. |
| Konfigurasi Kustom | `config/symbionusa.php` | **Ada** | Berisi parameter batas waktu RFQ, negosiasi, dan upload. |
| Rute Web | `routes/web.php` | **Dasar Saja** | Hanya mengembalikan halaman `welcome.blade.php`. Rute Breeze belum dipasang. |
| Layanan Modul Fungsional | `app/Services/` | **Belum Ada** | Belum ada direktori atau kelas service bisnis. |
| Policy Modul Fungsional | `app/Policies/` | **Belum Ada** | Belum ada Policy otorisasi per peran. |
