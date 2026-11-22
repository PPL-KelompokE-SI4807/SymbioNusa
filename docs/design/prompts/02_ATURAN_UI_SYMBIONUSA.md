# 02 · ATURAN UI SYMBIONUSA — 10 aturan kamus yang sudah diputuskan

> **Untuk AI.** File ini menggantikan bagian "RULES & RATIONALE" di `UIUX_TECHNICAL_DICTIONARY.md` asli khusus untuk SymbioNusa. Kosakata umum (glassmorphism, bento, tabular figures, dll.) tetap boleh diambil dari kamus asli. Bedanya: di sini setiap aturan sudah diputuskan terhadap `DESIGN.md`, jadi AI **tidak boleh "membetulkan" DESIGN.md** memakai rumus umum.
>
> Diaudit terhadap `docs/design/DESIGN.md` pada 17 Nov 2026 dengan skrip kontras WCAG dan pemeriksaan grid, lalu diperbarui setelah token tambahan (usulan B di `90_TEMUAN_DAN_USULAN.md`) diterapkan ke DESIGN.md. Diselaraskan ulang dengan FND v10 Heavy-Clean pada 21 Nov 2026 (baris 1, 2, 3, 4, 5, 7, tabel kontras, dan aturan tambahan), lalu dengan FND v11 "Senja di Sawah" pada 22 Nov 2026 (baris 3, 4, 7, tabel kontras dihitung ulang dari hex Senja dengan skrip WCAG 2.x).

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
| 3 | Rasio skala huruf konsisten | Ukuran tetap dari DESIGN.md: 12, 13, 14, 15, 16, 18/19, 22, 28, 32, 40 (band), dan judul halaman clamp(25–34 px). Bobot Figtree 400–900 (FND v11): 400/500 isi, 600–700 label dan judul baris, 800 judul kartu/zona dan subjudul, 900 judul halaman dan angka metrik. Rasio antar tingkat tidak konstan; itu wajar untuk UI operasional. Jangan diganti skala geometris. | 🎨 |
| 4 | Radius bersarang `r_dalam = max(0, r_luar − padding)` | Tidak berlaku. Radius per jenis komponen (FND v11): 9 px tombol, input, select, segmented control, thumbnail; 8 px segmen alur, baris interaktif kecil, alert; 14 px kartu, summary strip, dialog; 24 px sidebar; 6 px label foto; 5 px lencana hitungan sidebar. Pill hanya untuk tag "Data demo" dan tag di permukaan publik — **tidak pernah untuk status atau data di area konten**. Tombol di dalam kartu tetap 9 px, jangan dijadikan 0. | 🎨 |
| 5 | Elemen tetap, konten mengikuti isi | Kontrol (tombol, input, select, item nav) tinggi tetap 40 px. **Baris tabel 56 px adalah tinggi minimum**: baris boleh tumbuh bila teks membungkus. Kartu dan sel tidak diberi tinggi tetap. (DESIGN.md: table-row-height 56 px.) | ✅ |
| 6 | Token 3 tingkat + pasangan on-color | Sudah ada: token semantik + pasangan `on-*` + token komponen. Di kode hanya boleh kelas token (`bg-primary`, `text-on-surface-muted`, `border-outline`), bukan hex mentah dan bukan palet Tailwind (`emerald-700`, `slate-500`, `teal-*`, `purple-*`, `rose-*`). | ✅ |
| 7 | Hindari hitam/putih murni | Latar krem keemasan `#fdf8d8`, teks ink `#1c2a0a`, meta `#4a4a1a`. Putih `#ffffff` hanya untuk surface: kartu, summary strip, input. Palet terang dikunci; tidak ada mode gelap. | ✅ |
| 8 | Kontras WCAG | Semua pasangan teks di DESIGN.md lolos AA. Lihat tabel kontras di bawah untuk yang perlu diwaspadai. | ✅ |
| 9 | Line-height kelipatan 4 | Pakai nilai DESIGN.md apa adanya (unitless). Sebagian tinggi baris tidak jatuh di kelipatan 4 (mis. 14 × 1,5 = 21 px); ada usulan pembulatan di `90_TEMUAN_DAN_USULAN.md`. | 🎨 sementara |
| 10 | Ritme vertikal | DESIGN.md tidak mengatur angka. Default: judul → subjudul 4 px; blok header halaman → konten 24 px; antar-kartu 24 px; judul kartu → isi 16 px; label → field 6–8 px; helper/error di bawah field 4 px. Jarak di atas judul bagian selalu lebih besar dari jarak di bawahnya. | default |

## Kontras yang perlu diwaspadai (dihitung, bukan ditebak)

Dihitung ulang 22 Nov 2026 dari hex FND v11 "Senja di Sawah" dengan rumus luminans relatif WCAG 2.x (skrip Python, bukan perkiraan). AA teks normal ≥ 4,5:1; teks besar dan non-teks ≥ 3:1.

| Pasangan | Rasio | Catatan |
|---|---|---|
| ink `on-surface` #1c2a0a di atas `background` #fdf8d8 / surface #ffffff | 14,15:1 / 15,16:1 | Teks utama. |
| meta `on-surface-muted` = `on-surface-subtle` #4a4a1a di atas background / surface / surface2 #fef3c7 | 8,57:1 / 9,19:1 / 8,25:1 | Satu-satunya nada teks kedua; jangan menambah abu-abu ketiga. |
| `primary` #3f6212 sebagai teks di atas background / surface | 6,60:1 / 7,08:1 | Tautan dan teks status sukses lolos AA di kedua latar. |
| `on-primary` putih di atas `primary` / `primary-hover` #2f3d12 | 7,08:1 / 11,69:1 | Tombol terisi dan hover-nya. |
| `warning` #92400e di atas surface / background | 7,09:1 / 6,62:1 | Amber hanya sebagai warna depan (teks status, penanda insight). |
| `error` #b91c1c di atas surface / `error-container` #fdf2f2 | 6,47:1 / 5,90:1 | Teks galat dan alert bahaya. |
| `info` #1e40af di atas surface | 8,72:1 | Status "berjalan" dari STATUS_MAP; bukan warna variasi. |
| `accent` #c2410c sebagai teks di atas surface; putih di atas accent | 5,18:1 / 5,18:1 | Aksen kecil saja: segmen terpilih, penanda SEKARANG, tanda logo. Bukan tombol kedua. |
| `outline` #e3d99a di atas surface (non-teks) | 1,43:1 | Hanya garis dekoratif dan batas kartu/tombol berlabel. **Jangan dipakai sebagai satu-satunya batas input.** |
| `outline-input` #8a8350 di atas surface (non-teks) | 3,86:1 | Lolos 3:1 untuk batas input, select, textarea. |
| `focus-ring` #3f6212 di atas background (non-teks) | 6,60:1 | Lolos 3:1. Di atas sidebar forest pakai ring terang. |
| onForest #fef9c3 di atas forestDeep #1e2a08 / forest2 #47581c | 14,07:1 / 7,29:1 | Teks sidebar di kedua ujung overlay gradien. |
| Label grup sidebar (onForest 72%) di atas forestDeep | 7,96:1 | Eyebrow sidebar 12 px / 800. |
| Item nav aktif: forest #2f3d12 di atas onForest #fef9c3 | 10,88:1 | Satu-satunya keadaan terbalik di sidebar. |
| Lencana hitungan: forestDeep #1e2a08 di atas gold #d97706 | 4,74:1 | Lolos AA untuk angka 12 px / 800. |
| Label foto putih di atas `photo-label` #1e2a08 | 15,11:1 | Selalu di atas scrim; uji di foto paling terang. |
| Kategori di atas putih (non-teks): Plastik #d97706 · Pangan #65a30d · Tekstil #ea580c · Kayu #a16207 · Kertas #57534e | 3,19 · 3,09 · 3,56 · 4,92 · 7,63 | Semua ≥ 3:1. Warna kategori tidak pernah dipakai sebagai warna teks. |
| Antarkategori: Plastik–Tekstil · Tekstil–Kayu · Plastik–Kayu | 1,12 · 1,38 · 1,55 | Ketiganya satu keluarga jingga: jangan bersebelahan tanpa label nama kategori. |

Nilai Sage FND v10 (latar #eef3ec, primary #15803d, dll.) dan tabel kontras lamanya tersimpan di riwayat git berkas ini.

## Aturan tambahan (ringkasan DESIGN.md dan AGENTS.md, plus cara menerapkannya)

- **Focus** (DESIGN.md, Components › States): setiap elemen interaktif punya focus ring terlihat: 2 px `focus-ring`, offset 2 px (`focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring`). Di atas sidebar hijau tua pakai ring putih.
- **Disabled** (DESIGN.md): opacity 50%, kursor `not-allowed`, label tetap terbaca; jelaskan kenapa aksi tidak tersedia bila tidak jelas.
- **Hover** (DESIGN.md): isi tombol menggelap (primary → primary-hover); tombol bergaris diberi tint (secondary → latar halaman, danger → error-container).
- **Ikon** (DESIGN.md, Components › Icons): outline, stroke 1,75, 20 px (16 px di baris padat dan glyph status), tidak pernah di dalam tile atau lingkaran berwarna, `currentColor`, satu keluarga ikon. Di kode: komponen Blade SVG inline; catat sumber ikon dan lisensinya; jangan menambah paket ikon tanpa persetujuan PM.
- **Sidebar forest** (DESIGN.md, FND v11): basis forestDeep #1e2a08 dengan overlay 168°, teks onForest #fef9c3, label grup eyebrow 12 px / 800, hover putih 9%, item aktif bidang onForest + teks forest, lencana hitungan persegi 5 px emas. Didefinisikan di `resources/css/sidebar-senja.css`.
- **Teks minimum 12 px** tanpa pengecualian, termasuk sumbu grafik dan legenda. Huruf besar ber-tracking (12 px / 800, tracking 0,13em) hanya untuk eyebrow zona dashboard (`.dash-eyebrow`) dan label grup sidebar (FND v11); label isi lainnya huruf kalimat.
- **Angka:** `tabular-nums` di tabel, KPI, dan uang. Format: `Rp 3.000.000`, `5.000 kg`, `128,4 ton`, `27 Okt 2026, 14.00 WIB` (jam memakai titik). Metrik memakai bentuk ringkas ("Rp 214,6 jt", tanpa ",0"). Format tetap benar di setiap frame animasi.
- **Satu aksi utama hijau per wilayah tampilan.** Aksi lain secondary, danger, atau tautan teks.
- **Setiap tabel** punya empty state (penjelasan + aksi utama), loading (skeleton baris), dan error (pesan umum berbahasa Indonesia, bukan pesan exception).
- **Data demo** diberi label "Data demo" di bidang visual yang sama dengan angkanya, di mana pun angka itu tampil.
- **Tanpa kapsul di area konten** (FND v10, Ledger): status = teks berwarna + glyph, daftar = baris ledger di satu permukaan putih, warna = tinta data.
- **Motion** (FND v10, Motion): satu solver spring dengan tiga preset (Gentle, Snappy, Soft-bounce); angka tidak dihitung naik saat halaman dimuat; easing CSS hanya untuk hover; `prefers-reduced-motion` membuat semuanya instan.
- **Aksi yang menyelesaikan pekerjaan** menjalankan feedback loop: centang tergambar → baris terlipat → hitungan berubah bersama → empty state "Semua selesai".
- **Bahasa:** semua teks UI Bahasa Indonesia; label tombol berupa kata kerja ("Konfirmasi PO", "Terbitkan Invoice").
