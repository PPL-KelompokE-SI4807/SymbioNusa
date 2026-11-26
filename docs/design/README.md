# Paket Google Stitch — SymbioNusa

Sumber: `Proposal PPL_Kelompok SI-48-07 B-A.docx` (6 Okt 2026). Dikonversi pada 17 Nov 2026. Disimpan di repo pada `docs/design/` sejak 17 Nov 2026 (sebelumnya hanya di laptop PM).

**Mulai dari sini:** untuk tugas UI, baca `prompts/00_BACA_INI.md`. Label dan warna status: `prompts/03_STATUS_MAP.md`.

## Asal-usul dan pemakaian AI

Isi folder ini disusun PM dari proposal dan 37 mockup-nya dengan bantuan AI, dan dicatat di `docs/ai-log/` sesuai AGENTS.md §10:
- **Google Stitch**: membuat layar dari `STITCH_BRIEF.md` + prompt `SCREENS.md` (antara lain iterasi Dashboard Admin 01 v2).
- **Claude (Claude Code)**: pada 17 Nov 2026 dipakai untuk audit paket ini, tambahan token `DESIGN.md`, peta status, keputusan selisih DDL, dan paket `prompts/`. Penyusunan paket Stitch dan prototipe sebelumnya juga dibantu AI; rinciannya dicatat di log AI masing-masing sesi.

Keputusan di `prompts/03_STATUS_MAP.md` dan `prompts/91_USULAN_PERUBAHAN_SKEMA.md` diambil atas delegasi PM dan bisa direvisi lewat PR.

## Isi folder
| File | Untuk apa | Diberikan ke Stitch? |
|---|---|---|
| `DESIGN.md` | Sistem visual dalam **format resmi DESIGN.md dari Google Stitch** (token warna, tipografi, spasi, radius, komponen + aturan). Diturunkan dari warna dan layout mockup proposal. | **Ya.** Impor sebagai design system, atau tempel isinya di prompt pertama. |
| `STITCH_BRIEF.md` | Konteks produk ringkas: apa SymbioNusa, 4 peran beserta menu sidebar, alur status order, kosakata badge, data demo, aturan UI global. | **Ya.** Tempel sebagai prompt pertama. |
| `SCREENS.md` | 37 prompt siap tempel, satu per layar mockup (+ saran layar tambahan). | **Ya, satu per satu.** |
| `mockups/` | 37 gambar mockup dari proposal, diberi nama sesuai nomor di SCREENS.md. | Lampirkan sebagai referensi gambar per layar bila tersedia. |
| `Proposal_SymbioNusa_lengkap.md` | Konversi lengkap proposal ke Markdown (43 tabel, rumus, 44 gambar tertaut). | **Tidak.** Ini arsip/rujukan untuk manusia dan agen lain (Claude, dll.). |
| `diagrams/` | BPMN, use case, arsitektur, ERD, class diagram. | Tidak perlu. |
| `REFERENSI_INSPIRASI.md` | Tempat mencari inspirasi (pola gratis dari Carbon, Polaris, GOV.UK, shadcn, Tremor; galeri seperti Refero dan Nicelydone) dan apa yang diambil dari inspirasi Anda. | Tidak; untuk Anda dan tim. |
| `prompts/` | Paket prompt kerja SymbioNusa: auditor Stitch, aturan UI, peta status DDL, builder Laravel/Livewire, temuan audit. Mulai dari `prompts/00_BACA_INI.md`. | Tidak; untuk chat AI dan coding agent. |
| `prototype/` | Prototipe HTML Dashboard Admin (v1, v2) dan `DIRECTION.md`, komposisi yang dikunci PM pada 17 Nov 2026. | Tidak. |

**Pembaruan 17 Nov 2026:** `DESIGN.md` kini mendefinisikan `secondary` (hijau tua), `tertiary` (biru), dan `neutral` (zinc murni), sehingga Stitch tidak lagi mengarang warna merah muda/abu kehijauan. Aturan baru juga mencakup KPI ringkas, teks minimal 12 px, header sederhana, dan larangan overflow. **Impor ulang DESIGN.md di Stitch**, lalu jalankan prompt **01 v2**.

**Pembaruan 17 Nov 2026 (2):** `DESIGN.md` menambah token `warning` (#b45309, amber sebagai warna depan), `focus-ring` (#15803d), `secondary-hover` (#166534), dan `on-secondary-muted` (#bbf7d0, teks redup di sidebar hijau tua); `label-caps` naik dari 11 ke 12 px; prosa baru untuk States (focus, disabled, hover), Icons, sidebar gelap, dan baris tabel minimal 52 px. **Impor ulang DESIGN.md di Stitch lagi.**

## Cara pakai di Stitch
1. Buka stitch.withgoogle.com, buat proyek baru, pilih **Web**.
2. Impor `DESIGN.md` sebagai design system (Google menyebut DESIGN.md bisa diimpor/ekspor antarproyek). Kalau menunya tidak ketemu, tempel isi `DESIGN.md` di awal prompt pertama.
3. Prompt pertama: tempel isi `STITCH_BRIEF.md`, lalu prompt **01** dari `SCREENS.md`.
4. Periksa hasil layar 01 (sidebar, warna, angka). Kalau sudah pas, lanjut layar 02, 03, dst. Satu prompt per layar.
5. Lampirkan PNG dari `mockups/` sebagai referensi gambar kalau fitur input gambar tersedia di mode yang Anda pakai.
6. Ekspor hasil ke Figma atau HTML/kode.

## Batasan yang perlu diketahui
- **Paket ini belum diuji di Stitch.** Stitch butuh login Google Anda; saya tidak menjalankannya.
- Spesifikasi DESIGN.md masih **alpha**. Linter resmi `@google/design.md` tidak sempat dijalankan; file ini diperiksa dengan pemeriksa buatan sendiri berdasarkan spesifikasi resmi: 0 error, semua pasangan warna lolos WCAG AA.
- Batas panjang prompt Stitch tidak dipublikasikan. Kuota generasi bulanan berlaku, jadi hemat dengan satu prompt per layar.
- Menu sidebar **Inspector** tidak ada di mockup proposal (mockup Inspector memakai sidebar Admin). Menu di brief diturunkan dari use case UC-30–33 dan perlu disetujui tim.
- Warna diambil dari mockup (hijau `#15803d` + abu zinc), dengan satu koreksi: teks abu memakai `#52525b`, karena `#71717a` di atas latar `#f4f4f5` sedikit di bawah standar kontras.
