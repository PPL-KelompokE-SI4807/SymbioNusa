# 91 · Usulan perubahan skema (untuk Inaya dan pemilik PBI)

> Diputuskan 17 Nov 2026 oleh Claude atas delegasi PM (Mayhesa), mengikuti prinsip di `03_STATUS_MAP.md`: alur di proposal yang butuh nilai di luar DDL → skema ditambah, bukan alurnya dipangkas.
>
> **Belum ada yang diterapkan.** Berkas ini adalah permintaan kerja. Pelaksana migrasi: **Inaya** (pemilik migrasi, AGENTS.md §4). Setelah migrasi masuk `staging`, PM memperbarui `docs/DDL ERD SymbioNusa.sql` (hot spot) supaya dokumen DDL tetap sama dengan database.

## Aturan pelaksanaan (AGENTS.md §5 dan §9)

- Buat **berkas migrasi baru**; jangan mengedit migrasi yang sudah di-merge.
- Satu PR kecil per usulan, ditinjau pemilik PBI terkait.
- Uji di database lokal/CI saja (`migrate:fresh` dilarang di staging/produksi).
- Laravel 13 bisa mengubah kolom ENUM dengan `->change()` tanpa paket tambahan; tetap cek dokumentasi resmi sebelum menulis.

---

## Usulan 1 — `demand_requests.status` mendapat nilai `DRAF`

| | |
|---|---|
| PBI | PBI-14 Kebutuhan Bahan Baku Sekunder (Zacky) |
| Dasar | Proposal PBI-14: "draf dapat dihapus"; matriks CRUD PBI-14: Delete = "Hapus draf". Mockup 27 dan 29 memakai "Draf" dan "Simpan Draf". |
| Masalah | DDL: `ENUM('TERBUKA','TERPENUHI','KEDALUWARSA') DEFAULT 'TERBUKA'` — tidak ada draf, jadi operasi Delete PBI-14 tidak punya objek. |
| Perubahan | Tambah `DRAF` di awal ENUM dan jadikan default (sama seperti `listings.status`). Baris lama tetap `TERBUKA`. |

```sql
ALTER TABLE `demand_requests`
  MODIFY `status` ENUM('DRAF', 'TERBUKA', 'TERPENUHI', 'KEDALUWARSA') NOT NULL DEFAULT 'DRAF';
```

```php
// database/migrations/2026_11_xx_xxxxxx_add_draf_to_demand_requests_status.php
public function up(): void
{
    Schema::table('demand_requests', function (Blueprint $table) {
        $table->enum('status', ['DRAF', 'TERBUKA', 'TERPENUHI', 'KEDALUWARSA'])->default('DRAF')->change();
    });
}

public function down(): void
{
    // Draf belum pernah dipublikasikan; tandai kedaluwarsa supaya ENUM lama bisa dipasang kembali.
    DB::table('demand_requests')->where('status', 'DRAF')->update(['status' => 'KEDALUWARSA']);

    Schema::table('demand_requests', function (Blueprint $table) {
        $table->enum('status', ['TERBUKA', 'TERPENUHI', 'KEDALUWARSA'])->default('TERBUKA')->change();
    });
}
```

**Dampak kode:** kode yang membuat kebutuhan tanpa menyebut status kini menghasilkan `DRAF`, bukan `TERBUKA`; aksi "Publikasikan" mengubah `DRAF` → `TERBUKA`. Hapus hanya diizinkan untuk `DRAF` (Policy). Command `demand:close-expired` tidak menyentuh draf. Hitungan dashboard yang memakai `TERBUKA` tidak berubah.

---

## Usulan 2 — `reviews` mendapat rating per aspek dan waktu

| | |
|---|---|
| PBI | PBI-15 Ulasan & Rating Mitra (Zacky) |
| Dasar | Proposal PBI-15: satu ulasan per order "(mutu, ketepatan, komunikasi)" dan "menyuntingnya dalam 7 hari"; tabel parameter proposal: "Jendela sunting ulasan 7 hari". Mockup 30 memakai tiga rating bintang. |
| Masalah | DDL `reviews` hanya punya satu `rating` dan tidak punya kolom waktu, sehingga jendela sunting 7 hari tidak bisa ditegakkan dan tanggal ulasan tidak bisa ditampilkan. |
| Perubahan | Tambah tiga kolom rating aspek dan dua kolom waktu. Kolom `rating` yang ada tetap dipakai sebagai skor keseluruhan (rata-rata tiga aspek, dibulatkan) supaya agregasi skor mitra dan data lama tetap jalan. |

```sql
ALTER TABLE `reviews`
  ADD COLUMN `rating_mutu` TINYINT NULL AFTER `rating`,
  ADD COLUMN `rating_ketepatan` TINYINT NULL AFTER `rating_mutu`,
  ADD COLUMN `rating_komunikasi` TINYINT NULL AFTER `rating_ketepatan`,
  ADD COLUMN `dibuat_pada` TIMESTAMP(6) NULL AFTER `komentar`,
  ADD COLUMN `diubah_pada` TIMESTAMP(6) NULL AFTER `dibuat_pada`;
```

```php
// database/migrations/2026_11_xx_xxxxxx_add_aspect_ratings_and_times_to_reviews.php
public function up(): void
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->tinyInteger('rating_mutu')->nullable()->after('rating');
        $table->tinyInteger('rating_ketepatan')->nullable()->after('rating_mutu');
        $table->tinyInteger('rating_komunikasi')->nullable()->after('rating_ketepatan');
        $table->timestamp('dibuat_pada', 6)->nullable()->after('komentar');
        $table->timestamp('diubah_pada', 6)->nullable()->after('dibuat_pada');
    });
}

public function down(): void
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->dropColumn(['rating_mutu', 'rating_ketepatan', 'rating_komunikasi', 'dibuat_pada', 'diubah_pada']);
    });
}
```

**Dampak kode:**
- Model `Review`: kolom waktu memakai nama DDL, jadi pakai `const CREATED_AT = 'dibuat_pada';` dan `const UPDATED_AT = 'diubah_pada';` dengan `$timestamps = true`, atau isi manual di service. Pilih salah satu dan konsisten.
- Validasi (Form Request): tiap aspek 1–5 dan wajib untuk ulasan baru; `rating` diisi `round((mutu + ketepatan + komunikasi) / 3)` di service, bukan dari input.
- `ReviewPolicy::update`: hanya penulis, dan hanya bila `now() <= dibuat_pada + 7 hari`. Ulasan lama dengan `dibuat_pada` NULL dianggap sudah lewat jendela sunting.
- Moderasi (tanpa perubahan skema): Admin menghapus ulasan dengan alasan wajib; sebelum dihapus, catat ke `audit_logs` (`aksi` = `review.hapus_moderasi`, `auditable_type` = `reviews`, `auditable_id`, `nilai_lama` = isi ulasan + alasan).

---

## Keputusan yang sengaja **tanpa** perubahan skema

| Topik | Keputusan | Pemilik |
|---|---|---|
| "Tinjauan Ulang" perusahaan (PBI-08) | Saat dokumen wajib kedaluwarsa, `status_verifikasi` → `PENDING`; label "Tinjauan Ulang" diturunkan dari dokumen `KEDALUWARSA` | Haris |
| Moderasi listing (UC-09, PBI-09) | Admin mengarsipkan listing (`DIARSIPKAN`) dengan alasan wajib di `audit_logs`; tidak ada status "Ditolak" | Haris |
| Wilayah tugas Inspector (PBI-01) | Satu wilayah per Inspector (`VARCHAR(100)`) | Mayhesa |
| Status order 10 langkah, RFQ "Negosiasi", label manifes "Dalam Perjalanan", status tiket sengketa | Hanya label dan layar; nilai DDL sudah ada | Dhafi, Inaya |

## Checklist

- [ ] Inaya membuat dua migrasi baru (usulan 1 dan 2) di PR terpisah
- [ ] Zacky meninjau dan menyesuaikan model, Form Request, Policy, dan service PBI-14 / PBI-15
- [ ] PM memperbarui `docs/DDL ERD SymbioNusa.sql` setelah migrasi masuk `staging`
- [ ] Hapus tanda ⏳ di `03_STATUS_MAP.md` setelah migrasi masuk
