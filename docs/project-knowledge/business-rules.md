# Business Rules — SymbioNusa

> Dokumen ini merinci seluruh aturan bisnis kritis yang wajib dipatuhi oleh kode aplikasi SymbioNusa beserta sumber referensinya.

## 1. Larangan Material B3 (`is_b3_prohibited`)
- **Aturan:** Setiap kali listing dibuat atau diperbarui, sistem **wajib** menolak jika kategori terkait memiliki `categories.is_b3_prohibited = 1`.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/PRD.md` (Bagian 4.4).

## 2. Order State Machine
- **Aturan:** Semua perubahan status order (`purchase_orders.status`) **hanya boleh** melalui service `OrderStateMachine`. Dilarang melakukan pembaruan langsung (`update(['status' => ...])`). Setiap transisi wajib dicatat ke tabel `order_status_history`.
- **Alur Status:** `DRAFT → MENUNGGU_KONFIRMASI → DIKONFIRMASI → INSPEKSI → LOLOS_INSPEKSI → INVOICED → DIBAYAR → DALAM_PENGIRIMAN → DITERIMA → SELESAI`.
- **Cabang Sengketa/Pembatalan:** Kapan saja dapat beralih ke `SENGKETA`, `DIBATALKAN`, atau `DITOLAK`.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/PRD.md` (Bagian 4.1).

## 3. Toleransi Tonase dan Kadar Air
- **Aturan:** Saat Purchase Order (PO) dibuat, parameter `toleransi_persen`, `kadar_air_maks_persen`, dan `toleransi_kadar_air_poin` **disalin langsung** dari tabel `categories` ke dalam rekam PO (bukan berupa relasi dinamis). (`AGENTS.md` Bagian 6, `docs/PRD.md` Bagian 4.2).
- **Batas Tonase:** Batas bawah $B_{min} = V \times (1 - t)$ dan batas atas $B_{max} = V \times (1 + t)$, di mana $V$ adalah volume PO dan $t$ adalah persentase toleransi. Berat kirim tidak boleh melebihi $B_{max}$. (`docs/PRD.md` Bagian 4.2).
- **Evaluasi (`ToleranceEvaluator`):**
  - **Dalam toleransi:** `qty_received >= qty_min` dan (`kadar_air_terukur` <= `moisture_max`) $\rightarrow$ Status order menjadi `SELESAI`, menghitung `nilai_penyesuaian_rp`. (`docs/PRD.md` Bagian 3 - Fase F).
  - **Di luar toleransi:** $\rightarrow$ Status order menjadi `SENGKETA` dan tiket otomatis terbuka pada tabel `disputes`. (`docs/PRD.md` Bagian 3 - Fase F).
- **Status Konfirmasi:** Parameter evaluasi spesifik dan formula penyesuaian harga mendetail `PENDING CONFIRMATION` dari Project Manager karena PRD dan DDL hanya memberikan parameter dasar tanpa rumus numerik lengkap penyesuaian rupiah.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/PRD.md` (Bagian 4.2).

## 4. Stock Locking (Reservasi Stok)
- **Aturan:** Operasi reservasi atau pengurangan stok listing saat konfirmasi PO wajib menggunakan `DB::transaction()` dipadu dengan `lockForUpdate()` pada baris listing terkait untuk menghindari *race condition*.
- **Sumber:** `AGENTS.md` (Bagian 5.9), `docs/PRD.md` (Bagian 4.3).

## 5. Prasyarat Pengiriman Barang
- **Aturan:** Pengiriman hanya diizinkan apabila order berstatus `DIBAYAR` dan verifikasi dokumen non-B3 telah disetujui (`document_verifications.status = 'SETUJU'`). Sistem otomatis menerbitkan Manifes Angkut PDF.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/PRD.md` (Bagian 3 - Fase F).

## 6. Isolasi Data & Peran Mitra
- **Aturan:** Seller dan Buyer tidak boleh merupakan entitas perusahaan yang sama (`id_company` berbeda) dalam satu RFQ/PO. Data transaksi terisolasi per `id_company` melalui global scope atau Policy.
- **Sumber:** `AGENTS.md` (Bagian 6).

## 7. Versi Faktor Dampak (`ImpactFactors`)
- **Aturan:** Hanya satu versi faktor dampak yang aktif per kategori pada satu waktu (`impact_factors.aktif = 1`), dikelola oleh `ImpactFactorService` dalam transaksi database. Versi historis tetap terjaga untuk order lama.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/PRD.md` (Bagian 4.5).

## 8. Ulasan dan Rating Mitra
- **Aturan:** Satu ulasan per pihak per order. Penyuntingan ulasan maksimal 7 hari setelah order selesai. Ulasan hanya dapat diberikan setelah order berstatus `SELESAI`.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/SPRINT_1_BACKLOG.md` (PBI-15).

## 9. Pembayaran Manual
- **Aturan:** Platform tidak memproses uang secara elektronik (tanpa e-wallet/gateway otomatis). Pembayaran murni berbasis unggah dan verifikasi manual bukti transfer bank oleh Seller.
- **Sumber:** `AGENTS.md` (Bagian 6), `docs/PRD.md` (Bagian 1 & 3 - Fase E).

## 10. Butuh Konfirmasi (`PENDING CONFIRMATION`)
- Apakah batas waktu 48 jam untuk konfirmasi PO (`po_confirmation_limit_hours` di `config/symbionusa.php`) harus memicu pembatalan otomatis via Laravel Scheduler? *(Belum diatur secara eksplisit dalam DDL atau PRD selain config).*
- Formula eksak perhitungan `nilai_penyesuaian_rp` pada `ToleranceEvaluator` saat tonase aktual berbeda dari volume PO. *(PENDING CONFIRMATION)*.
