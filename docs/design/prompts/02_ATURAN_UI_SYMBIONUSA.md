# 02 · ATURAN UI SYMBIONUSA — 10 aturan kamus yang sudah diputuskan

> **Untuk AI.** File ini menggantikan bagian "RULES & RATIONALE" di `UIUX_TECHNICAL_DICTIONARY.md` asli khusus untuk SymbioNusa. Kosakata umum (glassmorphism, bento, tabular figures, dll.) tetap boleh diambil dari kamus asli. Bedanya: di sini setiap aturan sudah diputuskan terhadap `DESIGN.md`, jadi AI **tidak boleh "membetulkan" DESIGN.md** memakai rumus umum.
>
> Diaudit terhadap `docs/design/DESIGN.md` pada 17 Nov 2026 dengan skrip kontras WCAG dan pemeriksaan grid, lalu diperbarui setelah token tambahan (usulan B di `90_TEMUAN_DAN_USULAN.md`) diterapkan ke DESIGN.md. Diselaraskan ulang dengan FND v10 Heavy-Clean pada 21 Nov 2026 (baris 1, 2, 3, 4, 5, 7, tabel kontras, dan aturan tambahan), lalu dengan FND v11 "Senja di Sawah" pada 22 Nov 2026 (baris 3, 4, 7, tabel kontras dihitung ulang dari hex Senja dengan skrip WCAG 2.x), lalu dengan FND v12 "Kaca" pada 25 Nov 2026 (baris 1, 4, 7, 8, tabel kontras Kaca diukur pada render nyata, aturan tambahan). Resep praktis Kaca: `docs/design/PANDUAN_KACA.md`.

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
| 1 | Spacing grid 4/8 | Base 4 px. Semua token kelipatan 4: margin halaman 16, jarak antarkartu kaca 16 (`gap-4`), padding kartu kaca 16 (mobile) / 20 (desktop), kontrol 40, baris tabel dan baris ledger 56, header tabel 40, topbar mobile 64, sidebar 276. Layar Senja yang belum dibangun ulang tetap gutter 24 dan padding kartu 24. Di Tailwind 4 skala bawaan sudah 4 px (`p-6` = 24, `gap-4` = 16, `px-8` = 32, `h-10` = 40). Nilai di luar kelipatan 4 = ❌. | ✅ |
| 2 | Kolom per breakpoint 12/8/4 | Tidak memakai grid 12/8/4. Perilaku responsif dari DESIGN.md: di bawah 1024 px sidebar jadi drawer; summary strip 4 → 2×2 di dalam satu permukaan putih; detail ⅔ + ⅓ jadi bertumpuk (kartu aksi di bawah); tabel scroll horizontal di dalam kartu dengan kolom pertama sticky; tidak ada overflow horizontal dari 360 sampai 1440 px. | 🎨 |
| 3 | Rasio skala huruf konsisten | Ukuran tetap dari DESIGN.md: 12, 13, 14, 15, 16, 18/19, 22, 28, 32, 40 (band), dan judul halaman clamp(25–34 px). Bobot Figtree 400–900 (FND v11): 400/500 isi, 600–700 label dan judul baris, 800 judul kartu/zona dan subjudul, 900 judul halaman dan angka metrik. Rasio antar tingkat tidak konstan; itu wajar untuk UI operasional. Jangan diganti skala geometris. | 🎨 |
| 4 | Radius bersarang `r_dalam = max(0, r_luar − padding)` | Tidak berlaku. Radius per jenis komponen (FND v12 Kaca): 22 px kartu kaca dan summary strip; 24 px laci, band dampak, sidebar (26 px lembar bawah mobile); 14 px kontrol kaca di bilah atas; 16 px baris antrean dan sub-panel; 11–12 px tombol di dalam kartu kaca; 9–11 px tombol segmen dan thumb-nya; 9 px komponen form `x-ui.*`; 6 px tag ID, pintasan, label foto, dan tag "Data demo"; 5 px lencana hitungan sidebar. Tag "Data demo" dan lencana hitungan satu-satunya bentuk tag di area konten — **tidak pernah pill untuk status atau data**. Tombol di dalam kartu tidak dijadikan 0. Layar Senja yang belum dibangun ulang: 14/9/8 px. | 🎨 |
| 5 | Elemen tetap, konten mengikuti isi | Kontrol (tombol, input, select, item nav) tinggi tetap 40 px. **Baris tabel 56 px adalah tinggi minimum**: baris boleh tumbuh bila teks membungkus. Kartu dan sel tidak diberi tinggi tetap. (DESIGN.md: table-row-height 56 px.) | ✅ |
| 6 | Token 3 tingkat + pasangan on-color | Sudah ada: token semantik + pasangan `on-*` + token komponen. Di kode hanya boleh kelas token (`bg-primary`, `text-on-surface-muted`, `border-outline`), bukan hex mentah dan bukan palet Tailwind (`emerald-700`, `slate-500`, `teal-*`, `purple-*`, `rose-*`). | ✅ |
| 7 | Hindari hitam/putih murni | Kanvas krem `#fdf8d8` di bawah foto buram, teks ink `#1c2a0a`, meta `#33330f` di kaca (`#4a4a1a` di layar Senja). Putih pekat `#ffffff` hanya untuk kontrol (tombol, input, select, textarea) dan alert; kartu adalah kaca krem tembus pandang. Palet terang dikunci; tidak ada mode gelap. | ✅ |
| 8 | Kontras WCAG | Di atas kaca latar berubah-ubah, jadi rasio dari dua hex tidak cukup: ukur pada render nyata (PANDUAN_KACA.md, Lampiran A). Pasangan yang terukur lolos AA ada di tabel Kaca di bawah; satu pasangan gagal (teks SVG `var(--danger)`) tercatat sebagai temuan. | ✅ / ❌ satu temuan |
| 9 | Line-height kelipatan 4 | Pakai nilai DESIGN.md apa adanya (unitless). Sebagian tinggi baris tidak jatuh di kelipatan 4 (mis. 14 × 1,5 = 21 px); ada usulan pembulatan di `90_TEMUAN_DAN_USULAN.md`. | 🎨 sementara |
| 10 | Ritme vertikal | DESIGN.md tidak mengatur angka. Default: judul → subjudul 4 px; blok header halaman → konten 24 px; antar-kartu 24 px; judul kartu → isi 16 px; label → field 6–8 px; helper/error di bawah field 4 px. Jarak di atas judul bagian selalu lebih besar dari jarak di bawahnya. | default |

## Kontras di atas kaca (FND v12, diukur pada render nyata)

Diukur 25 Nov 2026 pada dasbor Admin varian Kaca yang dirender Chrome: viewport 1440×900 di 5 posisi gulir dan 504×844 di 3 posisi; animasi latar dijeda; teks dibuat transparan untuk memotret latarnya; tiap kotak teks (566 kotak) diukur dengan rumus luminans relatif WCAG 2.x terhadap persentil 10 luminans piksel latarnya, termasuk alfa warna dan opasitas leluhur. AA teks normal ≥ 4,5:1; teks ≥ 24 px atau ≥ 18,66 px tebal ≥ 3:1. Cara mengulang: `PANDUAN_KACA.md` Lampiran A.

| Teks | Permukaan | Rasio terendah | Catatan |
|---|---|---|---|
| ink #1c2a0a | kartu kaca | 6,19:1 | Teks utama. |
| meta #33330f | kartu kaca | 5,18:1 | Nada kedua di kaca. Meta Senja #4a4a1a tidak dipakai di kaca. |
| ink #1c2a0a | tanah (kepala halaman, di luar kartu) | 5,21:1 (judul 26 px di 504 px: 4,64:1) | Di luar kartu hanya ink; meta #4a4a1a di tanah 3,0–3,2:1 (audit FND v12 awal) sehingga dilarang. |
| hijau #2f3d12 (`.text-primary` di kaca) | kartu kaca | 6,82:1 | Delta baik, status. Isi tombol tetap #3f6212. |
| jingga #7c2d12 (`text-accent-text` di kaca) | kartu kaca | 5,13:1 | Penanda tahap tertahan, "lewat n hari". |
| merah #7f1d1d (`text-danger` di kaca) | kartu kaca | 4,68:1 | Potongan pendek tebal saja. |
| warning #92400e | baris `bg-field` | 6,28:1 | Warning sebagai teks hanya di baris `bg-field`, `panel-strong`, atau kaca tebal. |
| merah #7f1d1d / meta #33330f / ink | baris `bg-field` | 9,03 / 11,38 / 13,34:1 | Baris antrean. |
| putih | lencana `bg-accent` #c2410c | 5,18:1 | Hitungan di judul kartu, tombol "Lihat n order". |
| putih | tombol `bg-primary` #3f6212 | 7,08:1 | Isi tombol; nilai tetap. |
| #e3d99a / #fef9c3 | band dampak | 7,47 / 12,24:1 | Keterangan / judul dan angka band. |
| **#b91c1c (`var(--danger)`) sebagai teks SVG** | kartu kaca | **4,06:1** | ❌ Label keluar Sungai Order ("Sengketa · n", "Ditolak · n"). Untuk teks pakai `var(--color-danger)` / `text-danger`. |
| Tombol nonaktif (opacity 50–60%) | kaca / baris | 1,6–3,9:1 | Dikecualikan WCAG 1.4.3 (komponen tidak aktif); wajib membawa alasan. |

Batas teoretis (simulasi tanpa renderer, titik foto tergelap tepat di bawah stop kaca tertipis .36) jauh lebih pesimis — meta 4,54:1, teks berwarna 2,5–4,1:1 — sehingga aturan Kaca dibuat aman untuk kasus itu: teks berwarna di kaca polos hanya versi Kaca dalam potongan pendek tebal (≥ 13 px / 700), warning/info/warna isi sebagai teks hanya di permukaan yang lebih pekat, dan alfa kaca (.66/.36/.50) serta kerudung (.52/.22/.42) tidak boleh diturunkan.

Non-teks di kaca: ring fokus accent #c2410c 2 px dengan offset 2 px, ditambah batas jingga dan cincin `accent-soft` pada baris yang fokus. Batas input `outline-input` #8a8350 di atas input putih 3,86:1 — itulah sebabnya input tetap putih pekat.

## Kontras layar Senja (FND v11, untuk layar yang belum dibangun ulang)

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

- **Focus** (DESIGN.md, Components › States): setiap elemen interaktif punya focus ring terlihat: 2 px `focus-ring`, offset 2 px (`focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring`). Di atas sidebar hijau tua pakai ring putih. Di dalam `.dash-kaca` ring otomatis jingga accent 2 px (`.dash-kaca :focus-visible`).
- **Disabled** (DESIGN.md): opacity 50%, kursor `not-allowed`, label tetap terbaca; jelaskan kenapa aksi tidak tersedia bila tidak jelas.
- **Hover** (DESIGN.md): isi tombol menggelap (primary → primary-hover); tombol bergaris diberi tint (secondary → latar halaman, danger → error-container).
- **Ikon** (DESIGN.md, Components › Icons): outline, stroke 1,75, 20 px (16 px di baris padat dan glyph status), tidak pernah di dalam tile atau lingkaran berwarna, `currentColor`, satu keluarga ikon. Di kode: komponen Blade SVG inline; catat sumber ikon dan lisensinya; jangan menambah paket ikon tanpa persetujuan PM.
- **Sidebar forest** (DESIGN.md, FND v11): basis forestDeep #1e2a08 dengan overlay 168°, teks onForest #fef9c3, label grup eyebrow 12 px / 800, hover putih 9%, item aktif bidang onForest + teks forest, lencana hitungan persegi 5 px emas. Didefinisikan di `resources/css/sidebar-senja.css`. Di halaman yang memuat `.dash-kaca` sidebar otomatis menjadi kaca (gradien rgb(71 88 28 / .84) → rgb(30 42 8 / .92), blur 24 px; `body:has(.dash-kaca) [data-sidebar]`); onForest di ujung atas tetap 5,08:1 di atas titik latar paling terang (simulasi kontras, bukan render).
- **Teks minimum 12 px** tanpa pengecualian, termasuk sumbu grafik dan legenda. Huruf besar ber-tracking (12 px / 800, tracking 0,13em) hanya untuk eyebrow zona dashboard (`.dash-eyebrow`) dan label grup sidebar (FND v11); label isi lainnya huruf kalimat.
- **Angka:** `tabular-nums` di tabel, KPI, dan uang. Format: `Rp 3.000.000`, `5.000 kg`, `128,4 ton`, `27 Okt 2026, 14.00 WIB` (jam memakai titik). Metrik memakai bentuk ringkas ("Rp 214,6 jt", tanpa ",0"). Format tetap benar di setiap frame animasi.
- **Satu aksi utama hijau per wilayah tampilan.** Aksi lain secondary, danger, atau tautan teks.
- **Setiap tabel** punya empty state (penjelasan + aksi utama), loading (skeleton baris), dan error (pesan umum berbahasa Indonesia, bukan pesan exception).
- **Data demo** diberi label "Data demo" di bidang visual yang sama dengan angkanya, di mana pun angka itu tampil. Di kaca: tag bergaris putus-putus radius 6 px, 12 px meta, di dalam kartu yang sama. Angka yang tidak bisa dihitung dari DDL hanya boleh tampil sebagai contoh bertanda, dan service-nya (pola `AdminKaca`) menulis kolom apa yang hilang.
- **Tanpa kapsul di area konten** (Ledger): status = teks berwarna + glyph, daftar = baris ledger di satu permukaan (kartu kaca), warna = tinta data.
- **Kaca** (FND v12, PANDUAN_KACA.md): isi setiap layar baru atau yang dibangun ulang dibungkus `.dash-kaca` dengan `.kaca-backdrop` (empat foto, ukuran dan posisi tetap); kartu = `x-ui.card` atau `.glass`; laci, dialog, palet, tooltip = `.glass-strong`; kontrol tetap putih pekat atau hijau terisi; jangan menurunkan alfa kaca atau mengganti foto latar tanpa ukur ulang; `prefers-reduced-transparency` sengaja tidak diikuti; `forced-colors` dan browser tanpa `backdrop-filter` jatuh ke kartu putih.
- **Teks di kaca:** di kaca polos hanya ink, meta #33330f, dan teks berwarna versi Kaca (`text-primary`, `text-accent-text`, `text-danger`) dalam potongan pendek tebal; `text-warning`, `text-info`, dan warna isi (`var(--danger)`, `var(--accent)`) sebagai teks hanya di `bg-field`, `panel-strong`, atau `.glass-strong`; di luar kartu hanya ink.
- **Motion** (FND v12, Motion): satu kurva spring Kaca (Lab 420/38/0,9) lewat `--kaca-spring`, `.kaca-spring`, `flip()`, `exit()`, `x-kaca-seg`; tidak ada easing CSS baru untuk gerak posisi; angka tidak dihitung naik saat dimuat; gerak tereduksi mengikuti Lab: posisi, skala, dan loop berhenti, fade/warna/lebar bilah tetap jalan (jangan `transition: none` untuk semuanya). Preset solver Gentle/Snappy/Soft-bounce tetap untuk umpan balik tekan, centang sukses, dan layar Senja yang belum dibangun ulang.
- **Aksi yang menyelesaikan pekerjaan** menjalankan feedback loop: centang tergambar → baris terlipat → hitungan berubah bersama → empty state "Semua selesai".
- **Bahasa:** semua teks UI Bahasa Indonesia; label tombol berupa kata kerja ("Konfirmasi PO", "Terbitkan Invoice").
