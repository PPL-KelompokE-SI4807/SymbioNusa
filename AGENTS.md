# AGENTS.md — Panduan Kerja AI Agent untuk SymbioNusa

Berlaku untuk **semua AI agent/tool** yang dipakai tim (Antigravity, OpenCode, GitHub Copilot, Codex, Cursor, dan lainnya) dan **semua anggota tim**. Baca seluruh berkas ini di awal setiap sesi sebelum menulis kode.

## 1. Konteks proyek

SymbioNusa adalah platform web B2B ekonomi sirkular untuk sisa produksi industri non-B3 (Seller menawarkan, Buyer membutuhkan, Inspector memverifikasi, Admin/DLH mengawasi). Proyek ini bersifat *pure software*: tanpa IoT, tanpa e-wallet, pembayaran lewat invoice dan transfer bank manual. Penilaian dosen berbasis **PBI yang dikerjakan end-to-end** (Migration, Model, Controller/Livewire, Form Request, Policy, View, Test).

Kelompok SI-48-07 B-A, PM: Mayhesa Rachmadiharja. Sumber kebenaran: `docs/Proposal_PPL_Kelompok_SI-48-07_B-A.docx` (aturan PBI, alur, toleransi), `docs/DDL_ERD_SymbioNusa.sql` (skema), dan berkas ini (cara kerja).

## 2. Stack (jangan diganti tanpa persetujuan PM)

- PHP 8.2+ (CI memakai 8.3), **Laravel 11**, **Livewire 3**, Alpine.js, Tailwind CSS, Vite
- **MySQL 8.0** (InnoDB, kolom JSON), Eloquent ORM
- Laravel Breeze (login, logout, reset password), Policy dan Gate untuk RBAC
- `barryvdh/laravel-dompdf` (PDF invoice dan manifes), `maatwebsite/excel` (ekspor DLH)
- Uji: Pest/PHPUnit; Laravel Dusk hanya untuk alur utama; gaya kode: Laravel Pint; analisis statis: Larastan
- Dokumentasi endpoint: Scribe. Notifikasi: tabel `notifications` sesuai DDL via `NotificationService`

Node.js hanya untuk membangun aset (Vite), bukan runtime aplikasi.

## 3. Perintah penting

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed        # HANYA di lokal dan CI, jangan di staging/produksi
php artisan test                         # seluruh Pest/PHPUnit
./vendor/bin/pint                        # rapikan gaya kode (wajib sebelum commit)
./vendor/bin/phpstan analyse             # Larastan
npm ci && npm run build                  # aset Vite
php artisan schedule:list                # cek command terjadwal
```

## 4. Kepemilikan modul (CODEOWNERS)

| Anggota | Branch | PBI | Kelas/komponen utama |
|---|---|---|---|
| Mayhesa (PM) | `mayhesa` | 01, 02, 03 | `InspectorProfileController`, `CategoryService::canArchive`, `ImpactFactorService::activate`, `ImpactCalculator` |
| Dhafi | `dhafi` | 04, 05, 06 | `OrderStateMachine`, `RfqService`, `InvoiceNumberGenerator` |
| Haris | `haris` | 08, 09, 10 | `EnsureCompanyVerified`, `ListingRequest`, `SupplyScheduleService` |
| Inaya | `inaya` | 11, 12, 13 | `ManifestGenerator`, `ToleranceEvaluator`, `DisputeService`; pemilik migrasi |
| Zacky | `zacky` | 07, 14, 15, 16 | `NotificationService`, `PaymentController`, `MatchWatchlists` |
| Rosita | `rosita` | 17, 18, 19, 20 | `InspectionModeAdvisor`, `InspectorMatchingService`, `InspectionEvaluator` |

Jangan mengubah berkas milik anggota lain tanpa koordinasi di Slack. Perubahan lintas modul (misalnya status order) dilakukan lewat PR kecil yang ditinjau pemilik modul.

## 5. Aturan database (mengikuti DDL, bukan konvensi Laravel default)

1. Pakai **nama tabel dan kolom persis seperti `docs/DDL_ERD_SymbioNusa.sql`**. Jangan mengganti nama kolom.
2. Primary key berpola `id_<entitas>` (contoh `id_company`, `id_user`, `id_purchase_order`; perhatikan `id_categories` dan `id_listings` memakai bentuk jamak). Setiap model **wajib** mendeklarasikan `$table`, `$primaryKey`, dan `$fillable`.
3. Foreign key berpola `<tabel>_id_<entitas>` (contoh `companies_id_seller`, `rfq_contracts_id_rfq_contract`); `users.id_company` pengecualian. Definisikan relasi dengan kunci eksplisit: `belongsTo(Company::class, 'companies_id_seller', 'id_company')`.
4. DDL tidak memiliki `created_at` dan `updated_at`: set `public $timestamps = false;` kecuali tabel tersebut memang memilikinya.
5. `users.password_hash` menggantikan `password`: override `getAuthPassword()` pada model `User` dan sesuaikan Breeze (`password_hash`, tanpa `remember_token` dan `email_verified_at` kecuali ditambahkan lewat migrasi).
6. Tabel bawaan framework yang tidak ada di DDL (`password_reset_tokens`, `sessions`, `jobs`, `failed_jobs`, `cache`) dibuat lewat migrasi framework terpisah dan **tidak** diubah.
7. Nilai ENUM dipetakan ke **PHP Enum** (contoh `OrderStatus`: DRAFT, MENUNGGU_KONFIRMASI, DIKONFIRMASI, INSPEKSI, LOLOS_INSPEKSI, GAGAL_INSPEKSI, INVOICED, DIBAYAR, DALAM_PENGIRIMAN, DITERIMA, SELESAI, SENGKETA, DIBATALKAN, DITOLAK). Nilainya harus sama dengan DDL.
8. Migrasi bersifat *schema-first*: satu berkas migrasi per tabel (urutan mengikuti FK), dimiliki Inaya. **Jangan pernah mengedit migrasi yang sudah di-merge**; buat migrasi baru dan beri tahu pemilik.
9. Operasi lintas tabel dibungkus `DB::transaction()`. Pemesanan stok memakai `lockForUpdate()`.

## 6. Aturan bisnis yang tidak boleh dilanggar

- Kategori `is_b3_prohibited = 1` **ditolak** saat membuat listing.
- **Semua perubahan status order hanya lewat `OrderStateMachine`**, dan setiap perubahan dicatat di `order_status_history`. Jangan `update(['status' => ...])` langsung.
- Toleransi: `purchase_orders` menyalin `toleransi_persen`, `kadar_air_maks_persen`, `toleransi_kadar_air_poin` dari kategori saat PO dibuat. Batas `B_min = V × (1 − t)`, `B_max = V × (1 + t)`. Berat dikirim tidak boleh melebihi `B_max`. Evaluasi penerimaan dilakukan `ToleranceEvaluator`: dalam toleransi → SELESAI + `nilai_penyesuaian_rp`; di luar toleransi → SENGKETA + tiket otomatis.
- Pengiriman hanya boleh jika order `DIBAYAR` dan verifikasi dokumen non-B3 disetujui.
- Seller dan Buyer tidak boleh sama dalam satu RFQ/PO. Data transaksi terisolasi per `id_company` (global scope atau Policy).
- Hanya satu versi `impact_factors` aktif per kategori (dijaga `ImpactFactorService` dalam transaksi).
- Satu ulasan per pihak per order; sunting maksimal 7 hari; hanya setelah order SELESAI.
- Platform tidak memproses uang: pembayaran hanya unggah dan verifikasi bukti transfer.

## 7. Standar kode

- Logika bisnis di `app/Services/<Modul>/` atau Action; **controller tipis** (validasi lewat Form Request, otorisasi lewat Policy, lalu panggil service).
- Setiap aksi controller dan komponen Livewire wajib punya otorisasi (Policy atau middleware peran).
- Validasi unggahan: tipe (PDF/JPG/PNG), ukuran maksimal 5 MB, simpan lewat `Storage`.
- Gunakan Eloquent atau query builder berparameter; tanpa SQL mentah dari input pengguna.
- Antarmuka berbahasa Indonesia, mata uang Rupiah, satuan kg/ton, zona waktu WIB. Komentar kode singkat dalam Bahasa Indonesia untuk logika yang tidak jelas.
- Jangan menambah paket Composer/NPM tanpa persetujuan PM.

## 8. Alur kerja agen (ikuti urutannya)

1. **Pahami tugas.** Baca issue/PBI (ID, SP, acceptance criteria), bagian terkait proposal, dan skema tabel di DDL.
2. **Sinkronkan.** `git fetch origin && git rebase origin/staging` pada branch Anda (lihat bagian 9). Lakukan **setiap hari sebelum mulai**.
3. **Rencanakan.** Tulis rencana singkat (maksimal 10 baris): berkas yang akan dibuat/diubah dan risikonya. Tanyakan bila ambigu; jangan menebak aturan bisnis.
4. **Kerjakan kecil-kecil.** Urutan per PBI: Migration/Model → Form Request dan Policy → Service → Controller/Livewire → View → Test.
5. **Uji.** Tulis Pest test untuk jalur normal, validasi gagal, dan akses tidak sah (minimal satu feature test per PBI). Jalankan `php artisan test`.
6. **Rapikan.** `./vendor/bin/pint` dan `./vendor/bin/phpstan analyse` harus bersih.
7. **Commit** dengan Conventional Commits dan ID PBI: `feat(PBI-04): add counter-offer negotiation logic`, `fix(PBI-12): adjust weight tolerance calculation`, `test(PBI-05): cover stock reservation lock`.
8. **PR** ke `staging` memakai templat PR. Isi tool AI yang dipakai dan tautan log.
9. **Catat log AI** (bagian 10). Jangan menutup sesi sebelum log tersimpan.

## 9. Git dan branch (sesuai proposal)

- Setiap anggota memakai **satu branch bernama sesuai nama depan** (`mayhesa`, `dhafi`, `haris`, `inaya`, `zacky`, `rosita`) untuk seluruh PBI miliknya. Branch `main` dan `staging` dilindungi: perubahan hanya lewat Pull Request setelah ditinjau anggota lain.
- Karena branch bertahan lama, **PR dibuka per PBI atau per tonggak yang sudah lolos test**, jangan menumpuk lebih dari satu hari kerja tanpa PR. Rebase ke `staging` setiap pagi.
- Satu commit hanya untuk satu PBI. PR berisi paling banyak 400 baris perubahan bila memungkinkan.
- Dilarang: `git push --force` ke `main`/`staging`, commit `.env`, commit hasil `vendor/` atau `node_modules/`.
- `staging` → `main` hanya oleh PM di akhir sprint, lalu diberi tag (`v0.1.0-sprint1`, `v1.0.0`).

## 10. Log penggunaan AI (wajib)

Setiap sesi AI menghasilkan satu berkas `docs/ai-log/YYYY-MM-DD_<tahap>_<tool>_<nama>.md` berisi: tujuan, prompt (atau tautan), ringkasan hasil, bagian yang dipakai atau ditolak, dan tautan commit/PR. Gunakan `docs/ai-log/TEMPLATE.md`. Nama tool dicatat sesuai spreadsheet kelas (platform, bukan hanya nama model). Log ini bahan evaluasi sprint dan laporan akhir, jadi isi dengan jujur, termasuk hasil AI yang salah.

## 11. Definition of Done (per PBI)

- [ ] Operasi CRUD dan aturan bisnis PBI berfungsi sesuai proposal
- [ ] Form Request, Policy, dan otorisasi per peran terpasang
- [ ] Minimal satu Pest feature test, `php artisan test` hijau
- [ ] Pint dan Larastan bersih; CI hijau
- [ ] Tampilan responsif dan berbahasa Indonesia
- [ ] Ditinjau satu anggota lain; PR sudah di-merge ke `staging`
- [ ] Log AI tersimpan dan endpoint terdokumentasi (Scribe) bila ada

## 12. Larangan

- Mengarang hasil uji, data, atau status "selesai" yang tidak benar.
- Menghapus atau melemahkan test agar CI hijau.
- Menjalankan `migrate:fresh` atau perintah destruktif di server staging/produksi.
- Menyimpan rahasia (kunci, kata sandi, token) di repositori.
- Mengubah DDL, Enum, atau aturan toleransi tanpa persetujuan PM.

## 13. Bila terblokir

1. Tulis blocker di Slack `#ppl-b-a` (apa, siapa yang dibutuhkan, sejak kapan).
2. Gunakan factory/seeder sebagai pengganti sementara (`database/seeders/DemoScenarioSeeder`).
3. Ambil **backup task** dari `docs/plan` (tes tambahan, dokumentasi, review PR, polishing UI) agar tetap ada progres harian untuk daily report.

## 14. Prompt awal sesi (salin ke agent)

```
Baca AGENTS.md dan docs/DDL_ERD_SymbioNusa.sql. Saya mengerjakan PBI-XX (ringkas: ...).
Ikuti alur kerja bagian 8. Mulai dengan rencana singkat, lalu tunggu persetujuan saya
sebelum mengubah banyak berkas. Setelah selesai: jalankan test, Pint, Larastan, dan
buatkan draf log AI untuk docs/ai-log.
```
