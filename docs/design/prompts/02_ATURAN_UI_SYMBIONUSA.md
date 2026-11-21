# 02 · ATURAN UI SYMBIONUSA — 10 aturan kamus yang sudah diputuskan

> **Untuk AI.** File ini menggantikan bagian "RULES & RATIONALE" di `UIUX_TECHNICAL_DICTIONARY.md` asli khusus untuk SymbioNusa. Kosakata umum (glassmorphism, bento, tabular figures, dll.) tetap boleh diambil dari kamus asli. Bedanya: di sini setiap aturan sudah diputuskan terhadap `DESIGN.md`, jadi AI **tidak boleh "membetulkan" DESIGN.md** memakai rumus umum.
>
> Diaudit terhadap `docs/design/DESIGN.md` pada 17 Nov 2026 dengan skrip kontras WCAG dan pemeriksaan grid, lalu diperbarui setelah token tambahan (usulan B di `90_TEMUAN_DAN_USULAN.md`) diterapkan ke DESIGN.md. Diselaraskan ulang dengan FND v10 Heavy-Clean pada 21 Nov 2026 (baris 1, 2, 3, 4, 5, 7, tabel kontras, dan aturan tambahan).

## Urutan prioritas (kalau sumber bertentangan)

1. DDL `docs/DDL ERD SymbioNusa.sql` dan AGENTS.md §5–6: data, status, aturan bisnis.
2. `DESIGN.md`: semua nilai visual.
3. `03_STATUS_MAP.md`: label dan warna status.
4. `STITCH_BRIEF.md`, `SCREENS.md`, prototipe `docs/design/prototype/` (DIRECTION.md): isi dan komposisi layar.
5. File ini: default untuk hal yang belum diatur di atas.
6. `UIUX_TECHNICAL_DICTIONARY.md` asli: kosakata saja.

## Label hasil audit

- ✅ **Sesuai**: cocok dengan DESIGN.md atau keputusan di file ini.
- ❌ **Menyimpang**: berbeda dari DESIGN.md atau STATUS_MAP. Ini bug, wajib diperbaiki.
- ⚠️ **Celah**: DESIGN.md tidak mengatur, dan hasilnya melanggar default di file ini. Perbaiki ke default.
- 🎨 **Pilihan sengaja**: berbeda dari kamus umum, tetapi memang ditetapkan DESIGN.md. Jangan diubah.
- 💡 **Usulan**: perbaikan untuk DESIGN.md sendiri. Tulis sebagai usulan, jangan diterapkan diam-diam.

## Keputusan per aturan

| # | Aturan kamus | Keputusan SymbioNusa | Label |
|---|---|---|---|
| 1 | Spacing grid 4/8 | Base 4 px. Semua token kelipatan 4: margin halaman 16, gutter 24, padding kartu 24, padding summary strip 20–24, kontrol 40, baris tabel dan baris ledger 56, header tabel 40, topbar mobile 64, sidebar 276. Di Tailwind 4 skala bawaan sudah 4 px (`p-6` = 24, `gap-4` = 16, `px-8` = 32, `h-10` = 40). Nilai di luar kelipatan 4 = ❌. | ✅ |
| 2 | Kolom per breakpoint 12/8/4 | Tidak memakai grid 12/8/4. Perilaku responsif dari DESIGN.md: di bawah 1024 px sidebar jadi drawer; summary strip 4 → 2×2 di dalam satu permukaan putih; detail ⅔ + ⅓ jadi bertumpuk (kartu aksi di bawah); tabel scroll horizontal di dalam kartu dengan kolom pertama sticky; tidak ada overflow horizontal dari 360 sampai 1440 px. | 🎨 |
| 3 | Rasio skala huruf konsisten | Ukuran tetap dari DESIGN.md: 12, 13, 14, 15, 16, 18, 22, 28, 32, dan 40 (band). Bobot 400/500/600; 700 hanya untuk angka metrik, angka tahap, dan angka hari di blok tanggal. Rasio antar tingkat tidak konstan; itu wajar untuk UI operasional. Jangan diganti skala geometris. | 🎨 |
| 4 | Radius bersarang `r_dalam = max(0, r_luar − padding)` | Tidak berlaku. Radius per jenis komponen: 10 px tombol, input, item nav, segmented control, thumbnail; 14 px panel dalam, segmen alur, alert; 20 px kartu, summary strip, sidebar, dialog; 6 px label foto. Pill hanya untuk badge hitungan sidebar, tag, dan "Data demo" — **tidak pernah untuk status atau data di area konten**. Tombol di dalam kartu tetap 10 px, jangan dijadikan 0. | 🎨 |
| 5 | Elemen tetap, konten mengikuti isi | Kontrol (tombol, input, select, item nav) tinggi tetap 40 px. **Baris tabel 56 px adalah tinggi minimum**: baris boleh tumbuh bila teks membungkus. Kartu dan sel tidak diberi tinggi tetap. (DESIGN.md: table-row-height 56 px.) | ✅ |
| 6 | Token 3 tingkat + pasangan on-color | Sudah ada: token semantik + pasangan `on-*` + token komponen. Di kode hanya boleh kelas token (`bg-primary`, `text-on-surface-muted`, `border-outline`), bukan hex mentah dan bukan palet Tailwind (`emerald-700`, `slate-500`, `teal-*`, `purple-*`, `rose-*`). | ✅ |
| 7 | Hindari hitam/putih murni | Latar sage `#eef3ec`, teks `#16241b`. Putih `#ffffff` hanya untuk surface: kartu, summary strip, input. Palet terang dikunci; tidak ada mode gelap. | ✅ |
| 8 | Kontras WCAG | Semua pasangan teks di DESIGN.md lolos AA. Lihat tabel kontras di bawah untuk yang perlu diwaspadai. | ✅ |
| 9 | Line-height kelipatan 4 | Pakai nilai DESIGN.md apa adanya (unitless). Sebagian tinggi baris tidak jatuh di kelipatan 4 (mis. 14 × 1,5 = 21 px); ada usulan pembulatan di `90_TEMUAN_DAN_USULAN.md`. | 🎨 sementara |
| 10 | Ritme vertikal | DESIGN.md tidak mengatur angka. Default: judul → subjudul 4 px; blok header halaman → konten 24 px; antar-kartu 24 px; judul kartu → isi 16 px; label → field 6–8 px; helper/error di bawah field 4 px. Jarak di atas judul bagian selalu lebih besar dari jarak di bawahnya. | default |

## Kontras yang perlu diwaspadai (dihitung, bukan ditebak)

| Pasangan | Rasio | Catatan |
|---|---|---|
| meta `on-surface-subtle` #5f6d64 di atas `background` #eef3ec | 4,84:1 | Lolos AA. Ini nada teks kedua; jangan menambah abu-abu ketiga. |
| `primary` #15803d sebagai teks di atas `background` #eef3ec | ±4,5:1 | Batas. Tautan dan teks status hijau sebaiknya di atas kartu putih (5,02:1). Jangan memakai #1a8f4a (4,14:1, gagal). |
| `on-primary` putih di atas `primary` | 5,02:1 | Lolos AA. |
| `outline-input` #7a8a7f di atas putih | ≥ 3:1 | Lolos 3:1 untuk border kontrol. |
| `outline` #e2e8df di atas putih | ±1,3:1 | Hanya dekoratif. Batas kartu juga ditandai beda warna kartu vs latar, jadi boleh. Jangan pakai `outline` sebagai satu-satunya penanda batas input. |
| `secondary-label` #a7e0b8 / `on-secondary-muted` #cfeedd di atas sidebar `secondary` #14532d | 6,1:1 / > 7:1 | Label grup dan teks redup di sidebar hijau tua. Jangan memakai zinc-400 #a1a1aa (3,55:1). |
| `on-secondary` putih di atas `secondary-hover` #166534 | 7,13:1 | Item sidebar gelap saat hover. |
| `warning` #b45309 di atas putih | 5,02:1 | Amber hanya sebagai warna depan: penanda insight, teks status, ikon peringatan. Sebagai isi hanya di batang umur dan tanda kategori. Bukan isi tombol atau latar luas. |
| Kategori material di atas putih (non-teks) | Plastik 8,7 · Tekstil 5,0 · Kayu 7,1 · Kertas 10,4 · Pangan 3,1 | Semua ≥ 3:1. Tekstil dan Kayu mirip (1,41:1): jangan bersebelahan tanpa label. |
| Label foto putih di atas #14532d | 9,1:1 | Selalu di atas scrim; uji di foto paling terang. |
| `focus-ring` #15803d di atas putih / #eef3ec | 5,02:1 / ±4,5:1 | Lolos 3:1 untuk penanda fokus. Di atas sidebar hijau tua pakai ring putih (9,11:1). |

## Aturan tambahan (ringkasan DESIGN.md dan AGENTS.md, plus cara menerapkannya)

- **Focus** (DESIGN.md, Components › States): setiap elemen interaktif punya focus ring terlihat: 2 px `focus-ring`, offset 2 px (`focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring`). Di atas sidebar hijau tua pakai ring putih.
- **Disabled** (DESIGN.md): opacity 50%, kursor `not-allowed`, label tetap terbaca; jelaskan kenapa aksi tidak tersedia bila tidak jelas.
- **Hover** (DESIGN.md): isi tombol menggelap (primary → primary-hover); tombol bergaris diberi tint (secondary → latar halaman, danger → error-container).
- **Ikon** (DESIGN.md, Components › Icons): outline, stroke 1,75, 20 px (16 px di baris padat dan glyph status), tidak pernah di dalam tile atau lingkaran berwarna, `currentColor`, satu keluarga ikon. Di kode: komponen Blade SVG inline; catat sumber ikon dan lisensinya; jangan menambah paket ikon tanpa persetujuan PM.
- **Sidebar hijau tua** (DESIGN.md, Colors): teks dan ikon `on-secondary`, label grup `on-secondary-muted`, hover `secondary-hover`, item aktif mint `primary-container` + `on-primary-container`.
- **Teks minimum 12 px** tanpa pengecualian, termasuk sumbu grafik dan legenda. Tidak ada label uppercase ber-tracking; label grup sidebar huruf kalimat 13 px.
- **Angka:** `tabular-nums` di tabel, KPI, dan uang. Format: `Rp 3.000.000`, `5.000 kg`, `128,4 ton`, `27 Okt 2026, 14.00 WIB` (jam memakai titik). Metrik memakai bentuk ringkas ("Rp 214,6 jt", tanpa ",0"). Format tetap benar di setiap frame animasi.
- **Satu aksi utama hijau per wilayah tampilan.** Aksi lain secondary, danger, atau tautan teks.
- **Setiap tabel** punya empty state (penjelasan + aksi utama), loading (skeleton baris), dan error (pesan umum berbahasa Indonesia, bukan pesan exception).
- **Data demo** diberi label "Data demo" di bidang visual yang sama dengan angkanya, di mana pun angka itu tampil.
- **Tanpa kapsul di area konten** (FND v10, Ledger): status = teks berwarna + glyph, daftar = baris ledger di satu permukaan putih, warna = tinta data.
- **Motion** (FND v10, Motion): satu solver spring dengan tiga preset (Gentle, Snappy, Soft-bounce); angka tidak dihitung naik saat halaman dimuat; easing CSS hanya untuk hover; `prefers-reduced-motion` membuat semuanya instan.
- **Aksi yang menyelesaikan pekerjaan** menjalankan feedback loop: centang tergambar → baris terlipat → hitungan berubah bersama → empty state "Semua selesai".
- **Bahasa:** semua teks UI Bahasa Indonesia; label tombol berupa kata kerja ("Konfirmasi PO", "Terbitkan Invoice").
