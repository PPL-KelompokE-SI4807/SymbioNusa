# 02 · ATURAN UI SYMBIONUSA — 10 aturan kamus yang sudah diputuskan

> **Untuk AI.** File ini menggantikan bagian "RULES & RATIONALE" di `UIUX_TECHNICAL_DICTIONARY.md` asli khusus untuk SymbioNusa. Kosakata umum (glassmorphism, bento, tabular figures, dll.) tetap boleh diambil dari kamus asli. Bedanya: di sini setiap aturan sudah diputuskan terhadap `DESIGN.md`, jadi AI **tidak boleh "membetulkan" DESIGN.md** memakai rumus umum.
>
> Diaudit terhadap `docs/design/DESIGN.md` pada 17 Nov 2026 dengan skrip kontras WCAG dan pemeriksaan grid, lalu diperbarui setelah token tambahan (usulan B di `90_TEMUAN_DAN_USULAN.md`) diterapkan ke DESIGN.md.

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
| 1 | Spacing grid 4/8 | Base 4 px. Semua token kelipatan 4: margin halaman 32, gutter 16, padding kartu 24, padding KPI 20, kontrol 40, baris tabel 52, header tabel 44, topbar 64, sidebar 240. Di Tailwind 4 skala bawaan sudah 4 px (`p-6` = 24, `gap-4` = 16, `px-8` = 32, `h-10` = 40). Nilai di luar kelipatan 4 = ❌. | ✅ |
| 2 | Kolom per breakpoint 12/8/4 | Tidak memakai grid 12/8/4. Perilaku responsif dari DESIGN.md: di bawah 1024 px sidebar jadi drawer; KPI 4 → 2 → 1 kolom; detail ⅔ + ⅓ jadi bertumpuk (kartu aksi di bawah); tabel scroll horizontal di dalam kartu dengan kolom pertama sticky; tidak ada overflow horizontal dari 360 sampai 1440 px. | 🎨 |
| 3 | Rasio skala huruf konsisten | Ukuran tetap dari DESIGN.md: 12, 13, 14, 16, 18, 26, 28. Rasio antar tingkat tidak konstan; itu wajar untuk UI operasional. Jangan diganti skala geometris. | 🎨 |
| 4 | Radius bersarang `r_dalam = max(0, r_luar − padding)` | Tidak berlaku. Radius per jenis komponen: 6 px tombol, input, item nav; 8 px kartu dan tabel; 12 px dialog; pill untuk badge dan penghitung. Tombol di dalam kartu tetap 6 px, jangan dijadikan 0. | 🎨 |
| 5 | Elemen tetap, konten mengikuti isi | Kontrol (tombol, input, select, item nav) tinggi tetap 40 px. **Baris tabel 52 px adalah tinggi minimum**: baris boleh tumbuh bila teks atau chip membungkus. Kartu dan sel tidak diberi tinggi tetap. (Prosa DESIGN.md: "rows at least 52 px tall".) | ✅ |
| 6 | Token 3 tingkat + pasangan on-color | Sudah ada: token semantik + pasangan `on-*` + token komponen. Di kode hanya boleh kelas token (`bg-primary`, `text-on-surface-muted`, `border-outline`), bukan hex mentah dan bukan palet Tailwind (`emerald-700`, `slate-500`, `teal-*`, `purple-*`, `rose-*`). | ✅ |
| 7 | Hindari hitam/putih murni | Latar `#f4f4f5`, teks `#18181b`. Putih `#ffffff` hanya untuk surface: kartu, topbar, input, sidebar terang. | ✅ |
| 8 | Kontras WCAG | Semua pasangan teks di DESIGN.md lolos AA. Lihat tabel kontras di bawah untuk yang perlu diwaspadai. | ✅ |
| 9 | Line-height kelipatan 4 | Pakai nilai DESIGN.md apa adanya (unitless). Sebagian tinggi baris tidak jatuh di kelipatan 4 (mis. 14 × 1,5 = 21 px); ada usulan pembulatan di `90_TEMUAN_DAN_USULAN.md`. | 🎨 sementara |
| 10 | Ritme vertikal | DESIGN.md tidak mengatur angka. Default: judul → subjudul 4 px; blok header halaman → konten 24 px; antar-kartu 24 px; judul kartu → isi 16 px; label → field 6–8 px; helper/error di bawah field 4 px. Jarak di atas judul bagian selalu lebih besar dari jarak di bawahnya. | default |

## Kontras yang perlu diwaspadai (dihitung, bukan ditebak)

| Pasangan | Rasio | Catatan |
|---|---|---|
| `neutral` #71717a di atas `background` #f4f4f5 | 4,40:1 | ❌ untuk teks. DESIGN.md sudah melarang; pakai `on-surface-muted` #52525b (7,03:1). |
| `primary` #15803d sebagai teks di atas `background` #f4f4f5 | 4,56:1 | Lolos tipis. Tautan hijau sebaiknya di atas kartu putih (5,02:1). |
| `on-primary` putih di atas `primary` | 5,02:1 | Lolos AA. |
| `outline-input` #71717a di atas putih | 4,83:1 | Lolos 3:1 untuk border kontrol. |
| `outline` #e4e4e7 di atas putih | 1,27:1 | Hanya dekoratif. Batas kartu juga ditandai beda warna kartu vs latar, jadi boleh. Jangan pakai `outline` sebagai satu-satunya penanda batas input. |
| `on-secondary-muted` #bbf7d0 di atas sidebar `secondary` #14532d | 7,52:1 | Token untuk label grup dan teks redup di sidebar hijau tua (5,88:1 di atas `secondary-hover`). Jangan memakai zinc-400 #a1a1aa (3,55:1). |
| `on-secondary` putih di atas `secondary-hover` #166534 | 7,13:1 | Item sidebar gelap saat hover. |
| `warning` #b45309 di atas putih | 5,02:1 | Amber hanya sebagai warna depan: tahap macet, ikon peringatan, bar SLA. Di atas latar #f4f4f5 4,57:1. Bukan isi tombol atau latar luas. |
| `focus-ring` #15803d di atas putih / #f4f4f5 | 5,02:1 / 4,56:1 | Lolos 3:1 untuk penanda fokus. Di atas sidebar hijau tua pakai ring putih (9,11:1). |

## Aturan tambahan (ringkasan DESIGN.md dan AGENTS.md, plus cara menerapkannya)

- **Focus** (DESIGN.md, Components › States): setiap elemen interaktif punya focus ring terlihat: 2 px `focus-ring`, offset 2 px (`focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring`). Di atas sidebar hijau tua pakai ring putih.
- **Disabled** (DESIGN.md): opacity 50%, kursor `not-allowed`, label tetap terbaca; jelaskan kenapa aksi tidak tersedia bila tidak jelas.
- **Hover** (DESIGN.md): isi tombol menggelap (primary → primary-hover); tombol bergaris diberi tint (secondary → latar halaman, danger → error-container).
- **Ikon** (DESIGN.md, Components › Icons): outline, stroke 1,5–2 px, 20 px (16 px di tabel padat dan badge), `currentColor`, satu keluarga ikon. Di kode: komponen Blade SVG inline; catat sumber ikon dan lisensinya; jangan menambah paket ikon tanpa persetujuan PM.
- **Sidebar hijau tua** (DESIGN.md, Colors): teks dan ikon `on-secondary`, label grup `on-secondary-muted`, hover `secondary-hover`, item aktif mint `primary-container` + `on-primary-container`.
- **Teks minimum 12 px** tanpa pengecualian, termasuk sumbu grafik, legenda, dan label grup sidebar (`label-caps` kini 12 px).
- **Angka:** `tabular-nums` di tabel, KPI, dan uang. Format: `Rp 3.000.000`, `5.000 kg`, `128,40 ton`, `27 Okt 2026, 14:00` WIB. KPI memakai bentuk ringkas ("Rp 214,6 jt") dengan nilai lengkap di tooltip atau baris bawah.
- **Satu aksi utama hijau per wilayah tampilan.** Aksi lain secondary, danger, atau tautan teks.
- **Setiap tabel** punya empty state (penjelasan + aksi utama), loading (skeleton baris), dan error (pesan umum berbahasa Indonesia, bukan pesan exception).
- **Data demo** diberi label "data demo" bila tampil sebagai angka dampak.
- **Bahasa:** semua teks UI Bahasa Indonesia; label tombol berupa kata kerja ("Konfirmasi PO", "Terbitkan Invoice").
