# SymbioNusa — product brief for Google Stitch

Paste this whole file as the first message of the Stitch project (together with DESIGN.md if it was not imported). It is the shared context for every screen in SCREENS.md. Source: team proposal "Proposal PPL Kelompok SI-48-07 B-A" (6 Oct 2026) and its mockups.

## Product
SymbioNusa is a **web-based B2B platform** that connects Indonesian factories and SMEs that produce **non-hazardous (non-B3) production residue** (kain perca, ampas tahu, ampas kopi, roti sisa, serbuk kayu, cacahan plastik) with industries that use it as **secondary raw material** (carpet/filler plants, animal feed, biomass pellets). It is a marketplace, a transaction management system and a supervision system in one: RFQ → purchase order → inspection → invoice → payment → shipment → goods receipt → automatic environmental impact (recycled tonnage, cost saved in Rupiah, CO2e avoided). The local environment agency (DLH) supervises compliance. Payments are plain bank transfers with uploaded proof; no e-wallet, no IoT, no consumer features.

Design an **operational desktop-first web app** (responsive down to 360 px), not a landing page.

## Roles and their sidebar navigation (menu names are fixed; use them exactly)
Every page has the same app shell: left sidebar (logo "S" green square + "SymbioNusa" + role subtitle, grouped menu, user card with "Keluar" at the bottom) and a top bar (breadcrumbs left; "Notifikasi" with red count badge and avatar initials right).

**Admin Platform / DLH** (subtitle "Admin Platform / DLH") — the regulator; clears verification queues, master data, disputes, compliance.
- UTAMA: Dashboard
- MASTER DATA: Registri Inspector · Kategori Material · Parameter Dampak
- VERIFIKASI & TRANSAKSI: Verifikasi Mitra · Moderasi Listing · Penugasan Inspeksi · Sengketa & Klaim
- PENGAWASAN: Pengawasan DLH · Moderasi Ulasan

**Seller** (subtitle "Seller / Perusahaan Penghasil") — residue producer.
- UTAMA: Dashboard
- KATALOG: Listing Saya · Jadwal Pasokan · Papan Kebutuhan
- TRANSAKSI: RFQ Masuk · Purchase Order · Invoice · Pengiriman · Sengketa
- AKUN: Profil Perusahaan · Ulasan

**Buyer** (subtitle "Buyer / Perusahaan Pengolah") — processor buying secondary raw material.
- UTAMA: Dashboard
- PASAR: Katalog Material · Papan Kebutuhan · Daftar Pantau
- TRANSAKSI: RFQ & Negosiasi · Purchase Order · Invoice & Pembayaran · Penerimaan Barang · Sengketa
- AKUN: Profil Perusahaan · Ulasan

**Inspector** (subtitle "Inspector / Verifikator") — neutral verifier of legal documents and material quality. *The proposal has no Inspector sidebar mockup; this menu is derived from its use cases UC-30–33:*
- UTAMA: Dashboard
- INSPEKSI: Tugas Inspeksi · Verifikasi Dokumen · Laporan Mutu
- AKUN: Profil Kompetensi

## Order lifecycle (show as a stepper on order/PO detail pages)
Draf → Menunggu Konfirmasi → Dikonfirmasi → Inspeksi → Lolos Inspeksi → Menunggu Pembayaran (invoice issued) → Dibayar → Dalam Pengiriman → Diterima → Selesai.
Side branches: Ditolak, Dibatalkan (end), Gagal Inspeksi, Sengketa (dispute after receipt, ends in Selesai or Dibatalkan). A screen may show only the steps in its scope (e.g. the PO detail shows Draf → Menunggu Konfirmasi → Dikonfirmasi), but always with these labels.

## Status badge vocabulary
Source of truth: `prompts/03_STATUS_MAP.md` (DDL value → label → badge). Use these labels exactly; never invent a status.
- Warning (amber): Draf, Terbuka, Menunggu Konfirmasi, Menunggu Verifikasi, Menunggu Pembayaran, Menunggu Penyesuaian, Tinjauan Ulang, Segera Berakhir, Ditugaskan, Belum diisi, Sebagian lolos, Risiko Tinggi
- Success (green): Disepakati, Dikonfirmasi, Lolos / Lolos Inspeksi, Aktif, Terverifikasi, Valid, Disetujui, Dipublikasikan, Terpenuhi, Dibayar, Tiba, Dalam toleransi, Penyesuaian Selesai, Final, Selesai
- Danger (red): Ditolak, Gagal Inspeksi, Tidak lolos, Sengketa, Dibuka (dispute ticket), Di luar toleransi, Temuan, Peringatan, Dilarang (B3)
- Info (blue): Negosiasi, Inspeksi, Diproses, Dalam Pengiriman (order), Dalam Perjalanan (shipment), Diterima, Ditindaklanjuti
- Neutral (grey): Kedaluwarsa, Ditarik, Nonaktif, Dihentikan, Dibatalkan, Void, Diarsipkan, Tanpa penyesuaian, Risiko Rendah

## Realistic demo data (use instead of lorem ipsum)
Companies: PT Karpet Prima (Buyer), UD Tahu Sari (Seller), CV Tekstil Jaya, PT Plastindo, CV Mebel Raya, PT Kopi Nusa, Bakery Mandiri.
Materials: Kain perca katun · Ampas tahu · Cacahan plastik PP · Serbuk kayu · Ampas kopi · Roti sisa.
IDs: RFQ-2026-0041, PO-2026-0021, INV-2026-0012, SHP-2026-0007, DSP-2026-0003.
Example trade: Ampas tahu, 5.000 kg, Rp 600/kg, total Rp 3.000.000, toleransi tonase 5% (4.750–5.250 kg), batas kadar air 70% (toleransi 3 poin).
Dashboard impact: 128,40 ton terdaur ulang · Rp 214.600.000 penghematan · 96.250,00 kg CO2e ditekan · 19 dari 24 Inspector aktif.

## Global UI rules
- All copy in Bahasa Indonesia. Rupiah "Rp 3.000.000", weights "5.000 kg" / "128,40 ton", dates "27 Okt 2026, 14:00" (WIB).
- List pages: title + one-line subtitle + primary action top-right; optional 4 KPI cards; filter bar (search, status, kategori, date, "Reset"); table in a white card; pagination "Menampilkan 1–20 dari N" (max 20 rows per page).
- Detail pages: main card left (≈2/3) + actions card right (≈1/3) with the decision buttons; status history ("Riwayat Status") with timestamp and actor.
- Forms: visible labels, helper text, inline validation errors, auto-filled values shown read-only, actions bottom-right "Simpan Draf" + main action. File uploads: PDF/JPG/PNG max 5 MB with progress.
- Destructive actions open a confirmation dialog naming the object and requiring a reason where specified.
- Every table has empty, loading (skeleton) and error states. Main actions reachable within 3 clicks of the dashboard.
- Trust cues matter: B3 categories are blocked, verification badges, audit trail. Credible and calm; no marketing hero, gradients or illustrations inside the app.
