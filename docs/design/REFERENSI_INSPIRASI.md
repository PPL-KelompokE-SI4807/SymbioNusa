# Referensi & inspirasi UI — SymbioNusa

Disusun 17 Nov 2026. Semua tautan dicek hari itu (HTTP 200), kecuali yang diberi catatan. Prinsipnya: ambil **pola** dari produk dan design system nyata, ambil **gaya** dari galeri inspirasi.

## 1. Pola yang paling relevan (gratis, dari design system nyata)
| Kebutuhan SymbioNusa | Rujukan | Yang dicari di sana |
|---|---|---|
| Dashboard admin/regulator | [Carbon — Dashboards](https://carbondesignsystem.com/data-visualization/dashboards/) | Susunan KPI → grafik → detail, cara memilih grafik per pertanyaan |
| Memilih jenis grafik (tonase, tren, target vs realisasi) | [Carbon — Chart types](https://carbondesignsystem.com/data-visualization/chart-types/) | Kapan bar, line, atau funnel |
| Tabel antrean dengan baris yang bisa dibuka | [Carbon — Data table](https://carbondesignsystem.com/components/data-table/usage/) | Expandable rows, batch actions, toolbar filter |
| Tabel admin dengan aksi massal | [Polaris — Index table](https://polaris-react.shopify.com/components/tables/index-table) | Seleksi baris, bulk action, filter tab (Semua / Menunggu …) |
| Badge status | [Carbon — Status indicators](https://carbondesignsystem.com/patterns/status-indicator-pattern/), [Atlassian — Lozenge](https://atlassian.design/components/lozenge) | Warna + ikon + teks, tingkat keparahan |
| Antrean tugas bertahap (verifikasi mitra, kelengkapan dokumen) | [GOV.UK — Task list](https://design-system.service.gov.uk/components/task-list/) | Daftar tugas dengan status per langkah |
| Layar kosong (belum ada data / hasil filter kosong) | [Carbon — Empty states](https://carbondesignsystem.com/patterns/empty-states-pattern/) | Teks + aksi berikutnya |
| Sidebar/drawer navigasi | [Material 3 — Navigation drawer](https://m3.material.io/components/navigation-drawer/overview) | Pengelompokan menu, state aktif |

## 2. Contoh dashboard yang bisa langsung dijadikan kode
| Rujukan | Catatan |
|---|---|
| [shadcn/ui — Dashboard example](https://ui.shadcn.com/examples/dashboard) dan [Blocks](https://ui.shadcn.com/blocks) | Sidebar + KPI + grafik + tabel; gratis; berbasis React. Polanya tetap bisa ditiru di Blade/Livewire. |
| [Tremor](https://www.tremor.so/) dan [Tremor Blocks](https://blocks.tremor.so/) | Komponen grafik dan KPI untuk Tailwind; ada contoh KPI strip dengan delta dan tracker status |

## 3. Galeri inspirasi (gaya dan layar aplikasi nyata)
| Tempat | Isi | Biaya |
|---|---|---|
| [Refero](https://refero.design/) | Layar web app nyata, bisa dicari per pola ("approval", "dashboard") | Gratis terbatas, penuh berbayar |
| [Nicelydone](https://nicelydone.club/) | Layar SaaS: dashboard, tabel, settings, empty state | Sebagian gratis |
| [Page Flows](https://pageflows.com/) | Rekaman alur lengkap (onboarding, approval, checkout) | Berbayar |
| SaaSFrame (saasframe.io) | Layar SaaS per kategori | Situs menolak cek otomatis (429); buka manual |
| Mobbin (mobbin.com) | Layar dan alur dari aplikasi nyata, paling lengkap | **Berbayar.** Konektor Mobbin di Claude Anda tersambung, tetapi pencarian lewat Claude butuh paket berbayar. |
| [Figma Community](https://www.figma.com/community/search?resource_type=mixed&query=admin%20dashboard) | UI kit dashboard yang bisa dibuka di Figma | Gratis |
| Dribbble / Behance | Moodboard gaya | Gratis; banyak berupa konsep (data tidak realistis, kontras rendah). Ambil gayanya, bukan polanya. |

Kata kunci pencarian yang berguna: `admin dashboard approval queue`, `compliance dashboard`, `order pipeline funnel`, `expandable table row`, `document verification review`, `logistics shipment tracking`, `B2B marketplace RFQ`.

## 4. Dua inspirasi yang Anda kirim: ambil apa
**Elevio (sidebar gelap, funnel, pipeline)**
- Ambil: funnel per tahap untuk alur order (RFQ → PO → Inspeksi → Dibayar → Pengiriman → Selesai) beserta tahap yang macet; "priority tasks" dengan progress bar SLA; bingkai sidebar gelap dalam **hijau tua brand** (`secondary #14532d` di DESIGN.md).
- Jangan ambil: heading sangat besar dan kartu kanban ber-avatar. Terlalu longgar untuk layar kerja regulator.

**Marketing Intelligence (strip KPI, tabel yang bisa dibuka)**
- Ambil: strip KPI satu baris dengan delta; tab di atas tabel (Semua / Mendesak / SLA hampir habis); baris tabel yang bisa dibuka beserta tombol aksinya, cocok untuk Verifikasi Mitra, Moderasi Listing, dan Penugasan Inspeksi.
- Jangan ambil: teks sangat kecil dan terlalu banyak warna. Teks tetap minimal 12 px, dan palet tetap tertutup (hijau, zinc, biru, ditambah amber/merah khusus status).

## 5. Prompt iterasi Dashboard Admin (v2) untuk Stitch
Tersedia juga sebagai layar **01 v2** di `SCREENS.md`. Impor ulang `DESIGN.md` yang sudah diperbarui sebelum menjalankannya.
