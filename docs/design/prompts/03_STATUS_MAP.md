# 03 · STATUS MAP — ENUM DDL → label UI → varian badge

> **Untuk AI dan tim.** Satu-satunya rujukan label dan warna status di SymbioNusa. Nilai kiri diambil persis dari `docs/DDL ERD SymbioNusa.sql` (per 17 Nov 2026).
>
> Status: **DISETUJUI** — diputuskan 17 Nov 2026 oleh Claude atas delegasi PM (Mayhesa).

## Prinsip keputusan

1. **Alur dan aturan bisnis mengikuti proposal** (bagian PBI). **Nama tabel, kolom, dan nilai mengikuti DDL.**
2. Bila alur di proposal membutuhkan nilai atau kolom yang tidak ada di DDL → **ajukan perubahan skema** (lihat `91_USULAN_PERUBAHAN_SKEMA.md`), jangan memangkas alurnya.
3. Bila layar memakai sesuatu yang tidak ada di proposal maupun DDL → **layar yang diubah**.
4. Status yang bisa diturunkan dari data yang sudah ada (mis. dari tanggal atau dokumen) dibuat sebagai **status turunan**, tanpa kolom baru.

## Aturan pakai

1. Nilai database selalu dari DDL. Label boleh berbeda dari nilai (mis. `TERBIT` → "Menunggu Pembayaran"), tetapi satu nilai hanya punya satu label di seluruh aplikasi.
2. Varian badge mengikuti pasangan warna di DESIGN.md: `success` (#dcfce7 / #166534), `warning` (#fef3c7 / #92400e), `danger` (#fee2e2 / #991b1b), `info` (#dbeafe / #1e40af), `neutral` (#e4e4e7 / #3f3f46). Badge selalu berisi teks.
3. Arti varian: `warning` = menunggu tindakan seseorang; `info` = sedang berjalan di proses; `success` = selesai dengan baik atau aktif; `danger` = gagal, ditolak, atau butuh perhatian; `neutral` = berakhir tanpa masalah atau tidak aktif.
4. Di kode: setiap PHP Enum punya `label(): string` dan `badge(): string`; Blade memakai `<x-ui.badge :variant="$status->badge()">{{ $status->label() }}</x-ui.badge>`. Enum milik modul anggota lain dikerjakan oleh pemiliknya (AGENTS.md §4).
5. Di prompt Stitch: pakai label kolom "Label UI" persis.
6. Baris bertanda ⏳ menunggu migrasi di `91_USULAN_PERUBAHAN_SKEMA.md`. Jangan diimplementasikan di kode sebelum migrasinya masuk ke `staging`.

## purchase_orders.status (enum `OrderStatus`, hanya berubah lewat `OrderStateMachine`)

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `DRAFT` | Draf | warning |
| `MENUNGGU_KONFIRMASI` | Menunggu Konfirmasi | warning |
| `DIKONFIRMASI` | Dikonfirmasi | success |
| `INSPEKSI` | Inspeksi | info |
| `LOLOS_INSPEKSI` | Lolos Inspeksi | success |
| `GAGAL_INSPEKSI` | Gagal Inspeksi | danger |
| `INVOICED` | Menunggu Pembayaran | warning |
| `DIBAYAR` | Dibayar | success |
| `DALAM_PENGIRIMAN` | Dalam Pengiriman | info |
| `DITERIMA` | Diterima | info |
| `SELESAI` | Selesai | success |
| `SENGKETA` | Sengketa | danger |
| `DIBATALKAN` | Dibatalkan | neutral |
| `DITOLAK` | Ditolak | danger |

**Stepper jalur normal (10 langkah):** Draf → Menunggu Konfirmasi → Dikonfirmasi → Inspeksi → Lolos Inspeksi → Menunggu Pembayaran → Dibayar → Dalam Pengiriman → Diterima → Selesai. Cabang akhir: Ditolak, Dibatalkan, Gagal Inspeksi, Sengketa. Di layar sempit atau di layar yang cakupannya terbatas (mis. detail PO untuk PBI-05), stepper boleh menampilkan sebagian tahap, tetapi labelnya tetap dari tabel ini.
**Keputusan:** STITCH_BRIEF.md diperbarui ke 10 langkah.

## rfq_contracts.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `TERBUKA` | Terbuka | warning |
| `NEGOSIASI` | Negosiasi | info |
| `DISEPAKATI` | Disepakati | success |
| `DITOLAK` | Ditolak | danger |
| `KEDALUWARSA` | Kedaluwarsa | neutral |
| `DITARIK` | Ditarik | neutral |

**Keputusan:** "Negosiasi" ditambahkan ke brief dan SCREENS 07 (RFQ yang sudah mendapat penawaran balik).

## companies.status_verifikasi

| Nilai DDL / kondisi | Label UI | Badge |
|---|---|---|
| `PENDING`, belum pernah terverifikasi | Menunggu Verifikasi | warning |
| `PENDING` dan punya dokumen wajib berstatus `KEDALUWARSA` | Tinjauan Ulang | warning |
| `TERVERIFIKASI` | Terverifikasi | success |
| `DITOLAK` | Ditolak | danger |

`companies.tipe`: `SELLER` Seller · `BUYER` Buyer · `KEDUANYA` Seller & Buyer.
**Keputusan:** proposal PBI-08 meminta status "kembali ke Tinjauan Ulang bila dokumen wajib kedaluwarsa". Tanpa kolom baru: saat dokumen wajib kedaluwarsa, `status_verifikasi` diubah ke `PENDING` (perusahaan masuk antrean verifikasi lagi dan tidak bisa bertransaksi), dan label "Tinjauan Ulang" diturunkan dari dokumen yang kedaluwarsa. Pemilik: PBI-08 (Haris).

## company_documents.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `PENDING` | Menunggu Verifikasi | warning |
| `VALID` | Valid | success |
| `KEDALUWARSA` | Kedaluwarsa | neutral |

## document_verifications.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `MENUNGGU` | Menunggu Verifikasi | warning |
| `SETUJU` | Disetujui | success |
| `DITOLAK` | Ditolak | danger |
| `DIBATALKAN` | Dibatalkan | neutral |

## users.status · listing_attachments.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `AKTIF` | Aktif | success |
| `NONAKTIF` | Nonaktif | neutral |

`users.role`: `ADMIN` Admin Platform / DLH · `SELLER` Seller · `BUYER` Buyer · `INSPECTOR` Inspector.
`listing_attachments.jenis`: `SURAT_NON_B3` Surat pernyataan non-B3 · `FOTO_SAMPEL` Foto sampel · `SERTIFIKAT_LAB` Sertifikat uji lab.

## categories

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status` | `AKTIF` | Aktif | success |
| `status` | `ARSIP` | Diarsipkan | neutral |
| `is_b3_prohibited` | `1` | Dilarang (B3) | danger |
| `tingkat_risiko` | `RENDAH` | Risiko Rendah | neutral |
| `tingkat_risiko` | `TINGGI` | Risiko Tinggi | warning |

## inspector_profiles (status turunan, bukan ENUM)

| Kondisi | Label UI | Badge |
|---|---|---|
| `status_aktif = 1` dan `berlaku_sampai` > 30 hari lagi | Aktif | success |
| `status_aktif = 1` dan `berlaku_sampai` ≤ 30 hari lagi | Segera Berakhir | warning |
| `status_aktif = 0` atau sertifikat lewat | Nonaktif | neutral |

**Keputusan:** `wilayah_tugas` tetap satu nilai (VARCHAR(100) di DDL); proposal PBI-01 dan PBI-18 tidak mensyaratkan lebih dari satu wilayah. SCREENS 03 diubah dari multi-pilih menjadi satu pilihan wilayah. Pemilik: PBI-01 (Mayhesa).

## impact_factors.aktif · watchlists.aktif

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `1` | Aktif | success |
| `0` | Nonaktif | neutral |

## listings.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `DRAF` | Draf | warning |
| `PUBLISHED` | Dipublikasikan | success |
| `DIARSIPKAN` | Diarsipkan | neutral |

**Keputusan:** status "Ditolak" dihapus dari SCREENS 20; proposal PBI-09 maupun DDL tidak mengenalnya. **Moderasi listing (UC-09) oleh Admin = mengarsipkan listing yang melanggar** (`DIARSIPKAN`) dengan alasan wajib, dicatat di `audit_logs` (`aksi` = `listing.arsip_moderasi`, `nilai_lama` berisi status sebelumnya dan alasan). Tidak perlu kolom baru. Pemilik: PBI-09 (Haris).

## supply_schedules

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status` | `AKTIF` | Aktif | success |
| `status` | `DIHENTIKAN` | Dihentikan | neutral |
| `frekuensi` | `MINGGUAN` / `BULANAN` | Mingguan / Bulanan | – |

## demand_requests

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status` | ⏳ `DRAF` | Draf | warning |
| `status` | `TERBUKA` | Terbuka | warning |
| `status` | `TERPENUHI` | Terpenuhi | success |
| `status` | `KEDALUWARSA` | Kedaluwarsa | neutral |
| `frekuensi` | `SEKALI` / `MINGGUAN` / `BULANAN` | Sekali / Mingguan / Bulanan | – |

**Keputusan:** proposal PBI-14 menyebut "draf dapat dihapus" dan matriks CRUD-nya memakai "Hapus draf" sebagai operasi Delete, jadi draf dipertahankan dan `DRAF` diajukan ke ENUM (`91`, usulan 1). Label "Aktif" di SCREENS 27 diganti "Terbuka". Pemilik: PBI-14 (Zacky); migrasi: Inaya.

## invoices.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `TERBIT` | Menunggu Pembayaran | warning |
| `DIBAYAR` | Dibayar | success |
| `VOID` | Void | neutral |

## payments.status_verifikasi

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `MENUNGGU` | Menunggu Verifikasi | warning |
| `VALID` | Terverifikasi | success |
| `DITOLAK` | Ditolak | danger |

## shipments_manifests.status

| Nilai DDL | Label UI | Badge |
|---|---|---|
| `DRAF` | Draf | warning |
| `DALAM_PERJALANAN` | Dalam Perjalanan | info |
| `TIBA` | Tiba | success |
| `DIBATALKAN` | Dibatalkan | neutral |

**Keputusan:** tracker SCREENS 24 memakai "Dalam Perjalanan"; "Dalam Pengiriman" tetap khusus status order.

## goods_receipts

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status_toleransi` | `DALAM` | Dalam toleransi | success |
| `status_toleransi` | `DI_LUAR` | Di luar toleransi | danger |
| `status_penyesuaian` | `TIDAK_ADA` | Tanpa penyesuaian | neutral |
| `status_penyesuaian` | `MENUNGGU` | Menunggu Penyesuaian | warning |
| `status_penyesuaian` | `SELESAI` | Penyesuaian Selesai | success |

## disputes

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status` | `DIBUKA` | Dibuka | danger |
| `status` | `DIPROSES` | Diproses | info |
| `status` | `SELESAI` | Selesai | success |
| `status` | `DITARIK` | Ditarik | neutral |
| `jenis` | `SELISIH_TONASE` / `KADAR_AIR` / `MUTU` / `LAINNYA` | Selisih tonase / Kadar air / Mutu / Lainnya | – |

**Keputusan:** status tiket ditambahkan ke SCREENS 26.

## inspections

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status` | `DITUGASKAN` | Ditugaskan | warning |
| `status` | `DIPROSES` | Diproses | info |
| `status` | `SELESAI` | Selesai | success |
| `status` | `DIBATALKAN` | Dibatalkan | neutral |
| `mode_verifikasi` | `DESK` / `LAPANGAN` | Desk / Lapangan | – |

## inspection_results

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `hasil` | `LULUS` | Lolos | success |
| `hasil` | `GAGAL` | Tidak lolos | danger |
| `hasil` | `NULL` | Belum diisi | warning |
| `status` | `DRAF` | Draf | warning |
| `status` | `FINAL` | Final | success |
| `sumber_bukti` | `FOTO` / `LAB` / `LAPANGAN` | Foto / Lab / Lapangan | – |
| (turunan) | sebagian parameter gagal | Sebagian lolos | warning |

## compliance_notes

| Kolom | Nilai DDL | Label UI | Badge |
|---|---|---|---|
| `status_tindak_lanjut` | `TEMUAN` | Temuan | danger |
| `status_tindak_lanjut` | `PERINGATAN` | Peringatan | danger |
| `status_tindak_lanjut` | `DITINDAKLANJUTI` | Ditindaklanjuti | info |
| `status_tindak_lanjut` | `SELESAI` | Selesai | success |
| `jenis` | `TEMUAN` / `PERINGATAN` | Temuan / Peringatan | – |

## reviews

Tidak ada kolom status; tidak ada badge status.

**Keputusan:**
- **Moderasi = hapus**, sesuai proposal PBI-15 ("Admin dapat menghapus ulasan yang melanggar ketentuan") dan matriks CRUD ("Hapus oleh moderator"). Tidak ada status Disetujui/Disembunyikan. Alasan wajib diisi dan dicatat di `audit_logs` (`aksi` = `review.hapus_moderasi`, `nilai_lama` berisi isi ulasan dan alasan) sebelum baris dihapus. SCREENS 31 diubah.
- **Jendela sunting 7 hari** butuh waktu pembuatan ulasan → ⏳ kolom `dibuat_pada` diajukan (`91`, usulan 2). Sampai migrasi masuk, kolom "Tanggal" di layar moderasi belum bisa diisi.
- **Rating tiga aspek** (mutu, ketepatan, komunikasi) dari proposal dan mockup 30 → ⏳ tiga kolom rating diajukan (`91`, usulan 2); kolom `rating` yang ada tetap dipakai sebagai skor keseluruhan (rata-rata ketiganya, dibulatkan) supaya agregasi skor mitra tidak berubah.
- Pemilik: PBI-15 (Zacky); migrasi: Inaya.
