# 90 · Temuan audit paket Stitch-SymbioNusa dan usulan perbaikan

> Untuk PM dan tim. Disusun 17 Nov 2026.
>
> **Status penerapan (17 Nov 2026):**
> - **B (token DESIGN.md): diterapkan** atas persetujuan PM.
> - **A (selisih DDL): diputuskan** atas delegasi PM; `STITCH_BRIEF.md`, `SCREENS.md`, dan `03_STATUS_MAP.md` diperbarui; dua perubahan skema diajukan di `91_USULAN_PERUBAHAN_SKEMA.md` dan belum dikerjakan.
> - C, D, dan E masih usulan.

## Kesimpulan

Paket `docs/design` **sudah baik untuk sisi visual**, dan lebih lengkap daripada yang akan dihasilkan `STITCH_PROMPT_GENERATOR.md` untuk proyek ini. Kelemahannya ada di **keselarasan data dengan DDL** dan beberapa celah token (focus, disabled, ikon, sidebar gelap).

Karena itu generator tidak dipakai untuk membuat DESIGN.md baru. Ia diubah menjadi `01_STITCH_AUDITOR.md` untuk mengaudit hasil, mengambil pola dari inspirasi, dan menulis prompt layar baru.

## Cara audit dilakukan

1. **Analisis visual ala generator (Phase 1)** pada mockup `01-admin-dashboard.png` dan `12-seller-po-detail-konfirmasi.png`, lalu dibandingkan dengan DESIGN.md.
2. **Skrip** yang membaca frontmatter DESIGN.md: menghitung 30 pasangan kontras WCAG, mengecek kelipatan 4 px, tinggi baris, ukuran huruf minimum, radius, dan state.
3. **Perbandingan** status di `STITCH_BRIEF.md` dan `SCREENS.md` dengan ENUM di `docs/DDL ERD SymbioNusa.sql` (29 tabel).
4. **Perbandingan** dengan kode repo (`app.css`, `layouts/app.blade.php`, `DashboardOverview`).

## Generator vs paket yang sudah ada

| Keluaran generator | Paket Stitch-SymbioNusa | Penilaian |
|---|---|---|
| Audit 13 dimensi dari 1–3 gambar | DESIGN.md diturunkan dari 37 mockup dan proposal, dengan koreksi kontras teks redup (#71717a → #52525b) | Paket lebih kuat |
| DESIGN.md templat: ±11 warna, 4 gaya huruf, 4 komponen | 27 warna termasuk pasangan `on-*` dan 5 pasangan status, 10 gaya huruf, 16 token komponen, prosa lengkap dan Do/Don't | Paket jauh lebih lengkap |
| Pertanyaan konteks bisnis | `STITCH_BRIEF.md`: produk, 4 peran + menu persis, alur order, kosakata status, data demo, aturan global | Sudah terjawab |
| Satu prompt panjang per layar (11 blok Zoom-Out → Zoom-In) | Brief bersama + 37 prompt layar satu paragraf | Lebih hemat dan konsisten; sesuai aturan generator #7 (DESIGN.md dibagi antar layar) |
| Blok INTERACTIONS (hover, focus 2 px, loading) | Hanya aturan global; focus tidak disebut sama sekali | Celah |
| Blok RESPONSIVE | Prosa responsif di DESIGN.md; prompt mobile masih berupa saran | Sebagian |
| Cek kepatuhan 10 aturan | Belum ada | Ditambahkan sebagai `02_ATURAN_UI_SYMBIONUSA.md` |
| Cek data vs skema | Tidak ada di generator maupun paket | Celah terbesar → `03_STATUS_MAP.md` |

Hasil analisis visual mockup cocok dengan DESIGN.md: sidebar putih ≈ 240 px dengan logo "S" hijau, label grup caps ≈ 11 px, item aktif mint; topbar dengan breadcrumb, "Notifikasi" + badge merah, avatar; judul ≈ 26 px semibold; 4 kartu KPI angka ≈ 28 px bold; kartu putih datar dengan border tipis radius ≈ 8 px; badge pill amber; tombol hijau terisi dan tombol danger bergaris radius ≈ 6 px. Teks redup di mockup ≈ #71717a; DESIGN.md sengaja menggelapkannya ke #52525b supaya lolos kontras. Itu koreksi yang benar.

## Hasil cek otomatis DESIGN.md

| Aturan | Hasil |
|---|---|
| Kontras teks (21 pasangan token) | Semua lolos AA kecuali `neutral` #71717a di atas `background` (4,40:1), yang memang sudah dilarang DESIGN.md |
| Kontras non-teks | Border input 4,83:1 lolos; hairline kartu 1,27:1 (dekoratif, boleh) |
| Grid 4 px | Semua token spacing, padding, dan tinggi kelipatan 4 |
| Hitam/putih murni | Latar #f4f4f5 dan teks #18181b sudah lembut; putih hanya untuk kartu |
| Ukuran minimum 12 px | `label-caps` 11 px bertentangan dengan aturan DESIGN.md sendiri ("Never go below 12 px") → **diperbaiki: 12 px** |
| Tinggi baris kelipatan 4 | 9 dari 10 gaya huruf tidak jatuh di kelipatan 4 (mis. 14 × 1,5 = 21 px) → masih opsional, belum diubah |
| Baris tabel | `table-row` ditulis `height: 52px`; seharusnya tinggi minimum → **diperbaiki di prosa** ("rows at least 52 px tall"); nilai YAML tetap 52 px |
| State | `focus` dan `disabled` tidak disebut sama sekali → **ditambahkan** (token `focus-ring` + prosa States) |

## A. Selisih status dan kolom dengan DDL — ✅ diputuskan 17 Nov 2026

Diputuskan oleh Claude atas delegasi PM. Prinsip: **alur dan aturan dari proposal, nama dan nilai dari DDL**; alur proposal yang butuh nilai di luar DDL → skema ditambah (`91_USULAN_PERUBAHAN_SKEMA.md`); hal yang tidak ada di proposal maupun DDL → layar diubah. `STITCH_BRIEF.md` dan `SCREENS.md` sudah diperbarui.

| Di paket Stitch | Di DDL | Kata proposal | Keputusan |
|---|---|---|---|
| Stepper order 8 langkah (brief) | `purchase_orders.status` punya `INVOICED`, `DITERIMA` | Alur lengkap sampai Selesai | Stepper 10 langkah; brief diperbarui |
| RFQ tanpa "Negosiasi" (SCREENS 07, brief) | `rfq_contracts.status` punya `NEGOSIASI` | Penawaran bergantian dalam utas | "Negosiasi" (info) ditambahkan |
| "Tinjauan Ulang" (SCREENS 18) | `status_verifikasi`: PENDING/TERVERIFIKASI/DITOLAK | PBI-08: kembali ke Tinjauan Ulang bila dokumen wajib kedaluwarsa | Tanpa kolom baru: set `PENDING`, label "Tinjauan Ulang" diturunkan dari dokumen kedaluwarsa |
| Listing "Ditolak" (SCREENS 20) | `listings.status`: DRAF/PUBLISHED/DIARSIPKAN | PBI-09: draf → publikasi → arsip; Admin memoderasi | "Ditolak" dihapus; moderasi = arsip dengan alasan di `audit_logs` |
| Kebutuhan "Draf", "Aktif" (SCREENS 27, 29) | `demand_requests.status`: TERBUKA/TERPENUHI/KEDALUWARSA | PBI-14: "draf dapat dihapus" (operasi Delete-nya) | "Aktif" → "Terbuka"; draf dipertahankan; ⏳ `DRAF` diajukan ke ENUM (usulan 1) |
| Manifes "Dalam Pengiriman" (SCREENS 24) | `DALAM_PERJALANAN` | "Diperbarui sampai tiba" | Label "Dalam Perjalanan" |
| Moderasi ulasan: status, Setujui/Sembunyikan (SCREENS 31) | `reviews` tanpa status | PBI-15 dan CRUD: "Admin dapat menghapus" / "Hapus oleh moderator" | Moderasi = hapus dengan alasan di `audit_logs`; layar diubah |
| Tanggal ulasan, sunting 7 hari | `reviews` tanpa kolom waktu | PBI-15: sunting dalam 7 hari | ⏳ `dibuat_pada`, `diubah_pada` diajukan (usulan 2) |
| Tiga rating bintang (SCREENS 30) | `reviews.rating` satu kolom | PBI-15: mutu, ketepatan, komunikasi | ⏳ tiga kolom aspek diajukan (usulan 2); `rating` = rata-rata |
| "Wilayah Tugas" multi-pilih (SCREENS 03) | `wilayah_tugas` VARCHAR(100) | Tidak mensyaratkan banyak wilayah | Satu wilayah; SCREENS 03 diubah |
| Status tiket sengketa tidak disebut (SCREENS 26) | DIBUKA/DIPROSES/SELESAI/DITARIK | Pelapor dapat menarik tiket | Status ditambahkan ke SCREENS 26 |
| "Lolos"/"Tidak lolos" (SCREENS 36) | `hasil`: LULUS/GAGAL | "Lulus atau gagal" | Label tetap "Lolos"/"Tidak lolos"; nilai DB LULUS/GAGAL |

## B. Tambahan DESIGN.md — ✅ diterapkan 17 Nov 2026

Yang diterapkan: empat token warna di bawah, `label-caps` 12 px, dan prosa States, Icons, sidebar gelap, warning, serta tinggi minimum baris tabel. Yang **belum**: pembulatan tinggi baris (opsional, di akhir bagian ini).

```yaml
colors:
  warning: "#b45309"            # amber sebagai warna teks/garis: tahap "macet" di Dashboard Admin v2 (DIRECTION.md). 5,02:1 di atas putih
  on-secondary-muted: "#bbf7d0" # teks redup di sidebar hijau tua. 7,52:1 di atas #14532d (zinc-400 hanya 3,55:1)
  secondary-hover: "#166534"    # hover item di sidebar hijau tua; teks putih 7,13:1
  focus-ring: "#15803d"         # ring 2 px offset 2 px; di atas sidebar hijau tua pakai putih
typography:
  label-caps:
    fontSize: 12px              # atau tulis pengecualian eksplisit untuk label grup sidebar
```

Tambahan prosa:
- **States:** focus ring terlihat di setiap elemen interaktif (2 px `focus-ring`, offset 2 px); disabled = opacity 50% + kursor not-allowed, label tetap terbaca.
- **Ikon:** outline, stroke 1,5–2 px, 20 px (16 px di tabel padat), `currentColor`; ikon-only wajib tooltip (sudah ada).
- **Tabel:** "52 px rows" diganti "rows at least 52 px; rows grow when content wraps".

Opsional (pembulatan tinggi baris ke kelipatan 4, sekaligus cocok dengan skala bawaan Tailwind): headline-lg 26/32 · headline-md 18/24 · display-metric 28/32 · body-lg 16/24 · body-md 14/20 · body-sm 13/20 · label-md 14/20 · label-sm 12/16 · label-caps 12/16 · data-tabular 14/20.

Sudah dikerjakan setelah penerapan: blok `@theme` di `04_AGENT_LARAVEL_LIVEWIRE_BUILDER.md`, `02_ATURAN_UI_SYMBIONUSA.md`, dan `01_STITCH_AUDITOR.md` sudah disamakan. **Masih perlu dilakukan PM:** impor ulang DESIGN.md di proyek Stitch.

## C. Layar yang belum punya prompt di SCREENS.md

Dibandingkan dengan menu sidebar di `STITCH_BRIEF.md`:

- **Admin:** Moderasi Listing; detail tinjauan Verifikasi Mitra (SCREENS 18 hanya daftar).
- **Seller:** daftar RFQ Masuk; daftar Purchase Order versi Seller; daftar Pengiriman; Sengketa (mengajukan dan menanggapi).
- **Buyer:** Katalog Material + detail listing; daftar Invoice & Pembayaran; Sengketa versi Buyer.
- **Inspector:** Tugas Inspeksi; Profil Kompetensi.
- **Semua peran:** Login, Lupa Password, Reset Password; Dashboard Seller/Buyer/Inspector; halaman Notifikasi; halaman 403/404.
- **Mobile 390 px:** dashboard, daftar RFQ, detail PO, penerimaan barang (baru disebut sebagai saran).

Semua bisa dibuat dengan `01_STITCH_AUDITOR.md` mode LAYAR BARU.

## D. Kode repo belum mengikuti DESIGN.md

- `resources/css/app.css`: Instrument Sans; mencampur `@import "tailwindcss"` dengan `@tailwind base/components/utilities` (sintaks v3, sudah dihapus di Tailwind 4); belum ada token.
- `resources/views/layouts/app.blade.php`: navigasi atas dan palet slate/emerald. DESIGN.md meminta sidebar kiri 240 px dengan token zinc/hijau.
- `app/Livewire/DashboardOverview.php`: warna `purple`, `teal`, `rose` di luar palet tertutup; pesan error menampilkan `$e->getMessage()` ke pengguna.

Urutan perbaikan yang disarankan: (1) PM memutuskan bagian A dan B; (2) tugas fondasi token + komponen + app shell (Phase 1 di builder); (3) layar per PBI oleh pemilik masing-masing.

## E. Catatan kecil

- README paket menyebut "Paket ini belum diuji di Stitch", padahal prompt 01 v2 ditulis setelah hasil Stitch pertama. Perbarui kalimatnya.
- `AGENTS.md` §2 masih menyebut Laravel 11, Livewire 3, dan Pest; lock file berisi Laravel 13, Livewire 4, PHPUnit 12 tanpa Pest.
- `AGENTS.md` §15 merujuk `/tmp/opencode/figcraft-connection.md` dan perintah `collab.join`; path itu belum diverifikasi di komputer ini. FigCraft lokal tersambung lewat server `figcraft-app` dan skill figcraft-fast.

## Keputusan yang dibutuhkan dari PM

- [x] Setujui label dan varian di `03_STATUS_MAP.md` — diputuskan 17 Nov 2026 (delegasi PM)
- [x] Putuskan selisih DDL di bagian A — diputuskan 17 Nov 2026 (delegasi PM)
- [ ] Teruskan `91_USULAN_PERUBAHAN_SKEMA.md` ke Inaya (migrasi) dan Zacky (PBI-14, PBI-15)
- [ ] Beri tahu Haris (Tinjauan Ulang, moderasi listing) dan Dhafi (label `OrderStatus`, stepper 10 langkah)
- [x] Setujui tambahan DESIGN.md di bagian B (termasuk label-caps 12 px) — diterapkan 17 Nov 2026
- [ ] Impor ulang DESIGN.md di proyek Stitch
- [ ] (Opsional) Pembulatan tinggi baris ke kelipatan 4
- [ ] Jadwalkan tugas fondasi token + komponen + app shell, dan siapa pemiliknya
- [ ] Perbarui `AGENTS.md` §2 (versi stack) dan §15 (FigCraft) bila perlu
