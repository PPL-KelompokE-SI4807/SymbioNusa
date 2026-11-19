# SymbioNusa - PBI-11 Pengiriman & Manifes Angkut

Paket ini adalah **overlay**: salin ke project Laravel 11 baru (lihat langkah di bawah).

## Menjalankan (mode mandiri)

1. `composer create-project laravel/laravel:^11.0 symbionusa`
2. Salin SELURUH isi folder overlay ini ke dalam folder `symbionusa` (pilih Replace/Overwrite bila ditanya).
3. `cd symbionusa`
4. Edit `.env`:

       APP_NAME=SymbioNusa
       APP_LOCALE=id
       APP_TIMEZONE=Asia/Jakarta
       DB_CONNECTION=mysql
       DB_HOST=127.0.0.1
       DB_PORT=3306
       DB_DATABASE=symbionusa
       DB_USERNAME=root
       DB_PASSWORD=
       PBI11_STANDALONE=true

   (Tanpa MySQL: `DB_CONNECTION=sqlite`, hapus baris DB_HOST s.d. DB_PASSWORD, dan buat file kosong `database/database.sqlite`.)
5. Buat database `symbionusa` (phpMyAdmin / `CREATE DATABASE symbionusa;`)
6. `composer require barryvdh/laravel-dompdf`   (opsional: tanpa ini, manifes tampil sebagai halaman cetak HTML)
7. `npm install`
8. `php artisan key:generate`  (lewati bila sudah ada APP_KEY)
9. `php artisan migrate:fresh --seed`
10. Terminal 1: `php artisan serve`  | Terminal 2: `npm run dev`
11. Buka http://127.0.0.1:8000

Tes: `php artisan test`

## Akun demo (sandi: password)
Pilih lewat dropdown "Masuk sebagai (demo)" di sidebar: seller1@, seller2@, buyer@, inspector@, admin@ `symbionusa.test`.

## Titik integrasi dengan PBI lain
| Kebutuhan PBI-11 | Mode mandiri | Saat integrasi |
|---|---|---|
| Login & peran | `DemoActor` middleware | Set `PBI11_STANDALONE=false` -> rute otomatis pakai middleware `auth` |
| `companies`, `purchase_orders`, `document_verifications`, `order_status_history`, kolom `users.role/company_id` | `database/migrations_standalone/` + model stub | Hapus folder itu & model stub; pakai milik PBI-05/08/17 |
| Ubah status order | `SimpleOrderTransition` | Buat adapter ke `OrderStateMachine`, ganti binding di `Pbi11ServiceProvider` |
| Enum `OrderStatus` | stub | Pakai enum PBI-05 (butuh case `Dibayar`, `DalamPengiriman`) |
| Kontrak model `PurchaseOrder` | stub | Lihat komentar di `app/Models/PurchaseOrder.php` |
| Menu sidebar modul lain | tampil abu-abu | Aktif otomatis setelah rute bernama tersedia (`purchase-orders.index`, dst.) |
| Urutan migrasi | 2026_11_10_* | Pastikan setelah migrasi `purchase_orders`; ganti timestamp bila perlu |

Hook ke PBI-12: status `TIBA` + relasi `ShipmentManifest` 1:1 `GoodsReceipt` (isi `shipment_manifest_id` unik).
