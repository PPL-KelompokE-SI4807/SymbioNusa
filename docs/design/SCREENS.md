# SymbioNusa — screen prompts for Google Stitch

Use one prompt per generation, in order. Each prompt assumes STITCH_BRIEF.md and DESIGN.md are already in the project. Attach the listed mockup PNG as an image reference when Stitch's image input is available (it keeps layout and content close to the approved mockup); the prompt alone also works.

Prompts are in English for reliable generation; all UI text in quotes must stay in Bahasa Indonesia.

---

## 01 · App shell & Dashboard Admin (FND-04)
Role: Admin · active menu "Dashboard" · mockup `mockups/01-admin-dashboard.png`
> Design the Admin dashboard of SymbioNusa using the app shell from the brief. Page title "Dashboard Admin", subtitle "Ringkasan aliran material, dampak, dan antrean tindakan platform.", period selector "Periode: Okt 2026" top-right. Row of 4 KPI cards: "Total tonase terdaur ulang 128,40 ton", "Total penghematan Rp 214.600.000 (Seller + Buyer)", "Emisi bersih ditekan 96.250,00 kg CO2e", "Inspector aktif 19 dari 24". Below: a bar chart card "Tonase terdaur ulang per bulan" with filters periode, kategori, wilayah (2/3 width) and an "Antrean Tindakan Admin" card (1/3) listing items with count badges: "Mitra menunggu verifikasi 4", "Order menunggu penugasan inspeksi 3", "Sengketa belum diputus 2" (red), "Sertifikat Inspector ≤ 30 hari 3". Bottom row: five module summary cards (Transaksi, Katalog Seller, Logistik, Kebutuhan Buyer, Inspeksi), each with one key number and a link.

## 01 v2 · Dashboard Admin — iteration after first Stitch result
Role: Admin · "Dashboard" · references: your inspirations (pipeline funnel, compact KPI strip, expandable queue rows)
> Redesign "Dashboard Admin" keeping the same sidebar menu and green brand. Make the sidebar deep green (secondary #14532d) with white text and a mint active item. Replace the 4 large KPI cards with one compact KPI strip (Tonase terdaur ulang "128,4 ton", Penghematan "Rp 214,6 jt", Emisi ditekan "96,3 t CO2e", Inspector aktif "19/24"), each with a delta vs last month; no text below 12 px. Add an "Alur Order" funnel showing counts per stage: RFQ Terbuka → PO Dikonfirmasi → Inspeksi → Dibayar → Dalam Pengiriman → Selesai, with conversion % between stages and the stage where orders are stuck highlighted. Turn "Antrean Tindakan" into a table with tabs Semua / Mendesak / SLA hampir habis, where each row expands inline to show details and "Setujui" / "Tolak" / "Tugaskan" buttons; use one consistent priority style with an SLA progress bar. Keep the monthly tonnage chart but render the target series in tertiary blue with at least 3:1 contrast. Header: title, subtitle, period selector and "Unduh Rekap Laporan" only; remove the "DLH Terintegrasi" chip. Nothing may overflow horizontally at 1280 px; card rows wrap instead.

## 02 · Registri Inspector — daftar (PBI-01)
Role: Admin · "Registri Inspector" · `mockups/02-admin-registri-inspector-daftar.png`
> Inspector registry list page. Title "Registri Inspector", primary button "+ Daftarkan Inspector". KPI cards: Total Inspector, Aktif, Segera Berakhir (certificate ≤ 30 days), Nonaktif. Filter bar: search name/certificate no., bidang keahlian (material category), wilayah tugas, status. Table columns: Nama, Bidang Keahlian (chips), Wilayah Tugas, No. Sertifikasi, Berlaku Sampai, Status badge (Aktif / Segera Berakhir / Nonaktif), Aksi (Lihat, Perpanjang, Keluarkan). Pagination.

## 03 · Registri Inspector — form daftarkan (PBI-01)
Role: Admin · "Registri Inspector" · `mockups/03-admin-registri-inspector-form.png`
> Full-page form "Daftarkan Inspector". Sections: Akun Pengguna (select existing user account), Bidang Keahlian (multi-select material categories as chips), Wilayah Tugas (single select: one region per Inspector), Sertifikasi Kompetensi (nomor sertifikat, tanggal terbit, berlaku sampai, file upload PDF max 5 MB with progress). Helper text: inspectors become Nonaktif automatically when certification expires; only active inspectors can be assigned. Actions bottom-right: "Batal", "Simpan Inspector".

## 04 · Kategori Material & Templat Spesifikasi (PBI-02)
Role: Admin · "Kategori Material" · `mockups/04-admin-kategori-material.png`
> Split view. Left panel: hierarchical category tree (e.g. Tekstil › Kain perca katun; Pangan › Ampas tahu, Ampas kopi, Roti sisa; Kayu › Serbuk kayu; Plastik › Cacahan PP) with search and "+ Kategori"; B3 categories shown with a red "Dilarang (B3)" badge. Right panel: editor for the selected category: name, parent, toggle "Tandai sebagai B3 (dilarang)", Toleransi tonase (%), Batas kadar air maksimum (%) + toleransi (poin), Tingkat risiko (Rendah/Tinggi), toggle "Wajib sertifikat uji lab", and a dynamic "Templat Spesifikasi" table (parameter, satuan, wajib?, ambang min–maks) with add/remove rows. Note: categories used by active listings cannot be archived; tolerance changes don't affect running POs. Actions "Arsipkan", "Simpan Perubahan".

## 05 · Parameter Dampak — daftar versi faktor emisi (PBI-03)
Role: Admin · "Parameter Dampak" · `mockups/05-admin-faktor-emisi-daftar.png`
> Table page "Parameter Dampak & Faktor Emisi". Filter by kategori material and status. Table: Kategori, Versi (v1, v2…), Faktor pembuangan, Faktor bahan primer, Faktor pemrosesan, Faktor angkutan (kg CO2e per ton-km), Substitution rate, Harga referensi (Rp/kg), Berlaku sejak, Status (Aktif / Nonaktif). Only one active version per category. Primary button "+ Versi Baru". Explain in a muted note that past impact results keep the version used at completion.

## 06 · Parameter Dampak — form versi baru & simulasi (PBI-03)
Role: Admin · "Parameter Dampak" · `mockups/06-admin-faktor-emisi-form-simulasi.png`
> Two-column page "Versi Faktor Baru". Left: form fields for kategori, the four emission factors, substitution rate (0–1), harga bahan primer pembanding (Rp/kg), biaya buang (Rp/kg), tanggal berlaku. Right: sticky "Simulasi Pratinjau" card with inputs volume (kg) and jarak (km) and live results: Penghematan biaya (Rp), Emisi ditekan (kg CO2e), Tonase. Actions "Simpan Draf", "Aktifkan Versi".

## 07 · RFQ & Negosiasi — daftar (PBI-04)
Role: Buyer · "RFQ & Negosiasi" · `mockups/07-buyer-rfq-daftar.png`
> Title "RFQ & Negosiasi", subtitle "Pantau pengajuan RFQ dan utas negosiasi harga dengan Seller.", primary "+ Ajukan RFQ". KPI cards: Total RFQ 18, Terbuka / Negosiasi 6, Disepakati 7, Ditolak / Kedaluwarsa 5. Filters: search "Cari no. RFQ / material…", Status, Kategori material, Tenggat, "Reset". Table: No. RFQ, Material, Seller, Volume (kg), Harga Diajukan, Putaran (e.g. 3/5), Tenggat, Status badge (Terbuka, Negosiasi, Disepakati, Ditolak, Kedaluwarsa, Ditarik), Aksi (Lihat · Tarik for unanswered · Buat PO for Disepakati). Rows: RFQ-2026-0041 Kain perca katun CV Tekstil Jaya 2.000 Rp 3.500/kg 3/5 28 Okt 2026 Negosiasi; RFQ-2026-0040 Ampas tahu UD Tahu Sari 5.000 Rp 600/kg Disepakati; etc. Pagination "Menampilkan 1–6 dari 18 RFQ".

## 08 · Form Ajukan RFQ (PBI-04)
Role: Buyer · "RFQ & Negosiasi" · `mockups/08-buyer-rfq-form.png`
> Form "Ajukan RFQ". Step 1: choose a listing from the catalog (searchable select); once chosen, show a read-only summary card: Seller, material, stok tersedia, harga listing, lokasi, spesifikasi. Step 2 "Rincian Penawaran": Volume (kg, cannot exceed stock), Harga yang diajukan (Rp/kg), Tenggat negosiasi (default 3 hari, max 7), Catatan (optional). Validation messages for volume > stock and own-company listing. Actions "Batal", "Kirim RFQ".

## 09 · Detail RFQ & utas negosiasi (PBI-04)
Role: Seller · "RFQ Masuk" · `mockups/09-seller-rfq-negosiasi.png`
> Detail page "RFQ-2026-0041" with status badge and countdown to tenggat. Left: RFQ summary (Buyer, material, volume, harga diajukan, putaran 3/5) and a chat-like negotiation thread of alternating offers (who, volume, harga, catatan, timestamp). Right action card "Tanggapi Penawaran": radio Terima / Ajukan Penawaran Balik / Tolak; fields volume and harga for counter-offer; alasan required for Tolak; button changes label accordingly. Show agreed state: price and volume locked "Kontrak RFQ disepakati".

## 10 · Purchase Order — daftar (PBI-05)
Role: Buyer · "Purchase Order" · `mockups/10-buyer-po-daftar.png`
> Title "Purchase Order". KPI cards per status: Draf, Menunggu Konfirmasi, Dikonfirmasi, Ditolak/Dibatalkan. Filters and table: No. PO, Material, Seller, Volume, Harga, Total, Batas Konfirmasi, Status, Aksi (Lihat, Batalkan before inspection, Hapus draf).

## 11 · Form Buat Purchase Order (PBI-05)
Role: Buyer · "Purchase Order" · `mockups/11-buyer-po-form.png`
> Form "Buat Purchase Order" from an agreed RFQ contract (select "RFQ-2026-0040 · Ampas tahu · UD Tahu Sari"). Auto-filled read-only block: Seller, Material, Volume nominal 5.000 kg, Harga Rp 600/kg, Total Rp 3.000.000, and snapshot of category tolerances "Toleransi tonase 5% (4.750–5.250 kg)", "Batas kadar air 70% (toleransi 3 poin)". Fields: alamat pengiriman, catatan. Info note: tolerances are copied at creation and won't change. Actions "Simpan Draf", "Kirim ke Seller".

## 12 · Detail PO & Konfirmasi (PBI-05)
Role: Seller · "Purchase Order" · `mockups/12-seller-po-detail-konfirmasi.png`
> Title "PO-2026-0021" with badge "Menunggu Konfirmasi", subtitle "Dibuat dari kontrak RFQ-2026-0040 oleh PT Karpet Prima.", button "← Kembali". Card "Status Order" with stepper Draf (done) → Menunggu Konfirmasi (current) → Dikonfirmasi, and muted note about terminal branches. Card "Rincian PO" in a 3-column definition grid: Buyer PT Karpet Prima, Seller UD Tahu Sari, Material Ampas tahu, Volume nominal 5.000 kg, Harga Rp 600/kg, Total Rp 3.000.000, Toleransi tonase 5% (4.750–5.250 kg), Batas kadar air 70% (toleransi 3 poin), Batas konfirmasi 27 Okt 2026, 14:00; then "Riwayat Status" list with timestamps and actor. Right card "Tindakan Seller": note "Konfirmasi akan mereservasi 5.000 kg dari stok listing (stok saat ini 6.200 kg).", textarea "Alasan penolakan" (required to reject), buttons "Konfirmasi PO" (primary, full width) and "Tolak PO" (danger outline).

## 13 · Invoice — daftar & rekap umur tagihan (PBI-06)
Role: Seller · "Invoice" · `mockups/13-seller-invoice-daftar.png`
> Title "Invoice", primary "+ Terbitkan Invoice". Aging summary cards: Belum jatuh tempo, 1–3 hari, > 3 hari, Lunas (amounts in Rp). Table: No. Invoice, No. PO, Buyer, Total, Jatuh Tempo, Status (Menunggu Pembayaran, Dibayar, Void), Aksi (Unduh PDF, Void for unpaid).

## 14 · Form Terbitkan Invoice (PBI-06)
Role: Seller · "Invoice" · `mockups/14-seller-invoice-form.png`
> Form "Terbitkan Invoice": select a PO with status Lolos Inspeksi; auto-filled read-only Buyer, Material, Volume, Harga, Total tagihan (large tabular number); auto invoice number preview; fields Tanggal jatuh tempo (default 3 hari) and Catatan. Note that weight differences at receipt are handled by an adjustment note, not by changing the invoice. Button "Terbitkan Invoice" (sends notification to Buyer).

## 15 · Dialog konfirmasi Void Invoice (PBI-06)
Role: Seller · "Invoice" · `mockups/15-seller-invoice-void-dialog.png`
> Modal dialog over the invoice list: title "Void invoice INV-2026-0012?", warning box explaining that void keeps the invoice for audit and cannot be undone, required textarea "Alasan void", buttons "Batal" and "Void Invoice" (danger). Dim scrim behind.

## 16 · Unggah Bukti Pembayaran (PBI-07)
Role: Buyer · "Invoice & Pembayaran" · `mockups/16-buyer-unggah-bukti-bayar.png`
> Two-column page. Left form "Unggah Bukti Pembayaran": tanggal transfer, nominal (must equal invoice total; inline error if different), rekening asal (bank, nomor, nama pemilik), file upload of transfer proof (JPG/PNG/PDF ≤ 5 MB) with preview. Right card "Ringkasan Invoice": invoice no., Seller, total, jatuh tempo, bank tujuan. Button "Kirim untuk Verifikasi". Note: proof can only be replaced before it is verified.

## 17 · Verifikasi Pembayaran (PBI-07)
Role: Seller · "Invoice" · `mockups/17-seller-verifikasi-pembayaran.png`
> Review page: left large preview of the transfer proof image; middle details (tanggal, nominal, rekening asal) with a comparison row "Nominal invoice vs nominal transfer" and a match indicator; right action card with "Verifikasi Pembayaran" (primary) and "Tolak" (danger) plus required reason when rejecting. On verify the order becomes "Dibayar".

## 18 · Profil Perusahaan — daftar (PBI-08)
Role: Admin · "Verifikasi Mitra" · `mockups/18-profil-perusahaan-daftar.png`
> Company list for verification: search by name/NIB, filters tipe mitra (Seller, Buyer, Keduanya) and status (Menunggu Verifikasi, Tinjauan Ulang, Terverifikasi, Ditolak); "Tinjauan Ulang" marks a previously verified company whose required document has expired. Table: Nama Perusahaan, NIB, Tipe Mitra, Kota, Dokumen (e.g. 3/4 lengkap), Status, Aksi (Tinjau).

## 19 · Form Profil & Legalitas (PBI-08)
Role: Seller or Buyer · "Profil Perusahaan" · `mockups/19-profil-perusahaan-form.png`
> Company profile form with sections: Identitas (nama, NIB, tipe mitra Seller/Buyer/Keduanya), Alamat & Koordinat (address + small map pin), Kontak, Dokumen Wajib (list of required documents per partner type with upload, masa berlaku, status per document), and a required checkbox "Saya menyetujui Syarat & Ketentuan kepatuhan non-B3 (versi 1.2)". Show verification status banner at top (e.g. "Menunggu verifikasi Admin"). Actions "Simpan", "Ajukan Verifikasi".

## 20 · Listing Saya (PBI-09)
Role: Seller · "Listing Saya" · `mockups/20-seller-listing-daftar.png`
> Title "Listing Saya", primary "+ Buat Listing". Filters: search, kategori, status (Draf, Dipublikasikan, Diarsipkan). Table or card list: material name + category, stok tersedia (kg) with reserved amount, harga per kg, lokasi, bukti (icons for surat non-B3, foto, sertifikat lab), status badge, Aksi (Lihat, Ubah, Arsipkan).

## 21 · Form Listing Material (PBI-09)
Role: Seller · "Listing Saya" · `mockups/21-seller-listing-form.png`
> Form "Buat Listing": kategori (select; B3 categories disabled with explanation), dynamic specification fields generated from the category template (e.g. kadar air %, ukuran, tingkat kebersihan), volume tersedia (kg), harga per kg, lokasi, metode pembuangan sebelumnya, biaya buang (Rp/kg). Section "Bukti Material": uploads for surat pernyataan non-B3 (required), foto sampel + tanggal pengambilan, sertifikat uji laboratorium (required if category demands). Actions "Simpan Draf", "Publikasikan".

## 22 · Jadwal Pasokan Berkala (PBI-10)
Role: Seller · "Jadwal Pasokan" · `mockups/22-seller-jadwal-pasokan.png`
> Page with a month calendar showing supply periods per listing (colored chips) and a side list of schedules: listing, frekuensi (Mingguan/Bulanan), volume per periode, kapasitas, status (Aktif/Dihentikan). Small "Proyeksi volume" bar chart for the next 3 months. Primary "+ Buat Jadwal".

## 23 · Form Jadwal Pasokan (PBI-10)
Role: Seller · "Jadwal Pasokan" · `mockups/23-seller-jadwal-pasokan-form.png`
> Form: pilih listing, frekuensi (Mingguan/Bulanan), hari/tanggal pasokan, tanggal mulai, volume per periode (validated against announced capacity), catatan. Preview of the next 4 periods. Note: changes don't affect periods already ordered. Actions "Batal", "Simpan Jadwal".

## 24 · Pengiriman & Manifes (PBI-11)
Role: Seller · "Pengiriman" · `mockups/24-seller-pengiriman-manifes.png`
> Shipment form for a paid order: pre-check panel with three ticks (Order sudah Dibayar, Verifikasi dokumen non-B3 disetujui, Berat dikirim ≤ batas atas toleransi 5.250 kg). Fields: armada, nomor pelat, nama sopir, jadwal berangkat, rute, berat dikirim (kg; warning if below lower bound, requires reason), upload surat jalan internal. Right: status tracker (Draf → Dalam Perjalanan → Tiba) and recent shipment summary. Button "Terbitkan Manifes Angkut (PDF)".

## 25 · Penerimaan Barang (PBI-12)
Role: Buyer · "Penerimaan Barang" · `mockups/25-buyer-penerimaan-barang.png`
> Receipt form for SHP-2026-0007: shows berat dikirim 5.000 kg and tolerance window 4.750–5.250 kg. Inputs: berat diterima (kg), berat ditolak (kg), kadar air terukur (%) if required, kondisi material, alasan penolakan, foto bukti. Live evaluation panel: "Dalam toleransi" (green) or "Di luar toleransi — tiket sengketa akan dibuka otomatis" (red), with calculated adjustment note (selisih nilai in Rp). Note: corrections allowed within 24 jam. Button "Konfirmasi Penerimaan".

## 26 · Sengketa & Klaim (PBI-13)
Role: Admin · "Sengketa & Klaim" · `mockups/26-sengketa-klaim.png`
> Three-part page: left list of dispute tickets (no., order, jenis: Selisih tonase / Kadar air / Mutu / Lainnya, pelapor, status badge Dibuka / Diproses / Selesai / Ditarik, age); center selected ticket detail: report description, evidence attachments, the counter-party's response; right "Putusan Admin" panel with options Penyesuaian harga / Penggantian barang / Pembatalan transaksi, amount field, reasoning textarea, button "Tetapkan Putusan".

## 27 · Kebutuhan Saya (PBI-14)
Role: Buyer · "Papan Kebutuhan" · `mockups/27-buyer-kebutuhan-saya.png`
> Title "Kebutuhan Saya", primary "+ Publikasikan Kebutuhan". Filters and table: Komoditas/kategori, Volume dibutuhkan, Frekuensi, Harga maksimal, Berlaku sampai, Status (Draf, Terbuka, Terpenuhi, Kedaluwarsa), Aksi (Lihat, Ubah, Tutup, Hapus draf).

## 28 · Papan Kebutuhan Buyer — tampilan Seller (PBI-14)
Role: Seller · "Papan Kebutuhan" · `mockups/28-seller-papan-kebutuhan.png`
> Board of open buyer demands as cards or table: Buyer name with rating, kategori, spesifikasi minimal, volume & frekuensi, harga maksimal, wilayah, berlaku sampai; filters kategori, wilayah, rentang volume. Each item has "Lihat Detail" and "Tawarkan Listing".

## 29 · Form Publikasikan Kebutuhan (PBI-14)
Role: Buyer · "Papan Kebutuhan" · `mockups/29-buyer-kebutuhan-form.png`
> Form: kategori material, spesifikasi minimal from template (kadar air, kebersihan, ukuran), volume, frekuensi (Sekali/Mingguan/Bulanan), harga maksimal (Rp/kg), berlaku sampai, catatan. Actions "Simpan Draf", "Publikasikan".

## 30 · Ulasan & Rating Mitra (PBI-15)
Role: Buyer · "Ulasan" · `mockups/30-buyer-ulasan-rating.png`
> Review form for a completed order: three star ratings (Mutu, Ketepatan, Komunikasi) with the overall score shown as their average, comment, note "dapat disunting dalam 7 hari". Side card with the partner's average score and a history list of previous reviews.

## 31 · Moderasi Ulasan (PBI-15)
Role: Admin · "Moderasi Ulasan" · `mockups/31-admin-moderasi-ulasan.png`
> Table of published reviews: Pemberi, Penerima, Order, Rating (stars, overall), Komentar (truncated with expand), Tanggal ulasan; one row action "Hapus" (danger text link). "Hapus" opens a confirmation dialog naming the review ("Hapus ulasan PT Karpet Prima untuk PO-2026-0021?") with a required textarea "Alasan penghapusan" and a note that the deletion and reason are recorded in the audit log. There is no approve/hide status: reviews are published directly and only removed by moderation. Filters by rating and order period.

## 32 · Daftar Pantau & Peringatan (PBI-16)
Role: Buyer · "Daftar Pantau" · `mockups/32-daftar-pantau.png`
> List of saved watchlists: name, kriteria summary chips (kategori, wilayah, volume min–maks, rentang harga), match count since last visit, toggle Aktif, actions Ubah / Hapus. Recent alerts panel: new matching listings/demands with "Lihat".

## 33 · Form Tambah Pantauan (PBI-16)
Role: Buyer · "Daftar Pantau" · `mockups/33-daftar-pantau-form.png`
> Form: kategori material, wilayah, volume minimum & maksimum (kg), rentang harga (Rp/kg), toggle "Aktifkan pantauan". Explanation that in-app notifications are sent when a matching listing or demand is published. Button "Simpan Pantauan".

## 34 · Verifikasi Dokumen (PBI-17)
Role: Inspector · "Verifikasi Dokumen" · `mockups/34-inspector-verifikasi-dokumen.png`
> Title "Verifikasi Dokumen", subtitle "Periksa dokumen perusahaan dan bukti listing.". Context bar "PT [Nama Perusahaan] · Inspeksi [ID]" with badge "Menunggu verifikasi". Left large card: tabs "Dokumen legalitas", "Izin usaha", "Bukti listing" and a document preview area (PDF/image viewer with zoom). Right card "Checklist verifikasi": checkboxes Nama perusahaan sesuai dokumen, Nomor dokumen valid, Masa berlaku belum habis, Bukti listing sesuai produk, Dokumen terbaca jelas; textarea "Catatan" (required when rejecting); buttons "Tolak" (danger outline) and "Setujui" (primary); muted note "Hanya Inspector yang ditugaskan dapat memutuskan." Use the Inspector sidebar, not the Admin one.

## 35 · Penugasan Inspeksi (PBI-18)
Role: Admin · "Penugasan Inspeksi" · `mockups/35-admin-penugasan-inspeksi.png`
> Top card: order data (PO, Seller, material, volume, kategori risk) and a recommendation box "Mode verifikasi: Desk" with reason (risiko rendah, volume kecil, riwayat Seller baik) and option to override with a reason. Middle: table of eligible active Inspectors: nama, keahlian, wilayah, beban kerja, tingkat kecocokan (%), radio select. Bottom: workload calendar per Inspector colored green/yellow/red to avoid clashes. Fields jadwal inspeksi; button "Tugaskan Inspector".

## 36 · Laporan Hasil Verifikasi Mutu (PBI-19)
Role: Inspector · "Laporan Mutu" · `mockups/36-inspector-laporan-mutu.png`
> Title "Laporan Hasil Verifikasi Mutu", subtitle "Isi parameter, lampiran, dan sumber bukti.". Context bar "PT … · Inspeksi … · Mode: Desk" with badge "Draf". Card "Parameter pemeriksaan" table: Parameter, Ambang batas (min–maks), Nilai (input), Sumber bukti (Dokumen / foto / alat), Lampiran ("Unggah"), Penilaian badge (Lolos / Tidak lolos / Belum diisi) computed automatically. Bottom: "Ringkasan hasil" textarea and "Penilaian otomatis" card with badge "Sebagian lolos" and buttons "Simpan draf" and "Finalisasi". Use the Inspector sidebar.

## 37 · Pengawasan DLH (PBI-20)
Role: Admin/DLH · "Pengawasan DLH" · `mockups/37-admin-pengawasan-dlh.png`
> Split page. Left: table of findings per company (Perusahaan, Jenis temuan, Deskripsi, Tenggat, Status: Temuan / Peringatan / Ditindaklanjuti / Selesai) with search and status filter. Right: form "Catat Temuan Baru" (perusahaan, jenis, deskripsi, tenggat). Top-right button "Ekspor Excel".

---

### Extra screens worth generating (not in the proposal mockups)
- Login, Lupa Password, Reset Password (FND-01–03), Indonesian copy, SymbioNusa logo, demo-account quick access only in local environment.
- Dashboard per role: Seller (RFQ masuk, PO menunggu konfirmasi, invoice belum dibayar, stok listing), Buyer (RFQ terbuka, pengiriman dalam perjalanan, penerimaan menunggu), Inspector (tugas hari ini, verifikasi menunggu, laporan draf).
- Katalog Material for Buyer (search + filters + listing cards with verification badges).
- Mobile (390 px) versions of: dashboard, RFQ list, PO detail, penerimaan barang.
