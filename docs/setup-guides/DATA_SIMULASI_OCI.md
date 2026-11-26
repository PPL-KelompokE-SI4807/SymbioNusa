# Data simulasi untuk server demo (OCI VM)

Seeder: `database/seeders/DemoScenarioSeeder.php` (dipanggil otomatis oleh `DatabaseSeeder`).
Semua data fiktif: nama perusahaan, orang, NIB, plat nomor, dan rekening bukan data nyata.

## Isi

| Bagian | Jumlah (setelah `migrate:fresh --seed`) |
|---|---|
| Perusahaan | 18 (Seller, Buyer, Keduanya; 3 menunggu verifikasi, 1 ditolak) |
| Kategori | 9 (7 aktif dengan `spec_schema` + faktor dampak versi 1 dan 2, 1 B3 terlarang, 1 arsip) |
| Inspector | 7 (aktif, segera berakhir ≤ 7 hari, kedaluwarsa, nonaktif) |
| Listing | 16 + lampiran (surat non-B3, foto sampel, sertifikat lab) dan 3 jadwal pasokan |
| Permintaan (demand) | 11, semua status |
| RFQ | 59, semua status termasuk negosiasi bertingkat |
| Purchase order | 49, **semua 14 status terisi**, riwayat status lengkap ±6 bulan |
| Turunan order | inspeksi + hasil, verifikasi dokumen, invoice, pembayaran (menunggu/valid/ditolak), manifes + surat jalan, penerimaan barang (dalam/di luar toleransi), sengketa, impact log, ulasan |
| Tata kelola | catatan kepatuhan, watchlist, audit log, notifikasi |

Tanggal dihitung relatif terhadap hari seeder dijalankan, jadi "Sedang berjalan" dan jadwal 7 hari di dasbor
selalu terisi. Angka dampak dihitung dengan rumus PRD §4.5 dari faktor dampak versi aktif.
Berkas lampiran berupa PDF kecil bertanda "DATA DEMO" agar tautan unduh tidak 404.

## Akun (kata sandi semua: `password`)

| Peran | Email |
|---|---|
| Admin | `admin@symbionusa.test` |
| Seller | `seller@symbionusa.test`, `hendra@plastindo.test`, `sri@kayulestari.test`, `asep@tahuberkah.test`, `lina@garmensentosa.test`, `bambang@kemasansurya.test`, `yohanes@alumindo.test` |
| Buyer | `buyer@symbionusa.test`, `rizky@biomassa.test`, `dedi@pakanmakmur.test`, `maya@kertasdaur.test`, `wahyu@isibantal.test`, `fransiska@recyclepolimer.test` |
| Keduanya | `galih@sirkularjaya.test` |
| Inspector | `inspector@symbionusa.test`, `agus.inspector@symbionusa.test`, `dewi.inspector@symbionusa.test`, `nurul.inspector@symbionusa.test` |

Ganti kata sandi ini bila server demo bisa diakses publik.

## Menjalankan di VM

Dari folder aplikasi di VM, setelah kode terbaru ditarik:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate:fresh --seed --force   # MENGHAPUS seluruh isi database lalu mengisi ulang
php artisan storage:link
```

Database sudah berisi data dasar dan hanya ingin menambah data simulasi:

```bash
php artisan db:seed --class=DemoScenarioSeeder --force
```

Seeder hanya berjalan sekali: bila perusahaan penanda (NIB `9120000000001`) sudah ada, ia berhenti tanpa
menulis apa pun. Untuk memuat ulang dari nol, pakai `migrate:fresh --seed`.

Bila web server berjalan sebagai user lain (mis. `www-data`), pastikan berkas yang dibuat seeder bisa dibaca:

```bash
sudo chown -R www-data:www-data storage
```

Catatan: `RfqSeeder` dan `PurchaseOrderSeeder` (milik modul lain) menambah baris setiap kali dijalankan, jadi
jangan jalankan `php artisan db:seed` tanpa `--class` berulang kali pada database yang sama.
