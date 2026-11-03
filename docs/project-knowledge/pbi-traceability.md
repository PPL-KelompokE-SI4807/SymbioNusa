# PBI Traceability — SymbioNusa

> Dokumen ini memetakan seluruh Fondasi (FND-01 s.d. FND-04) dan PBI (PBI-01 s.d. PBI-20) ke dalam kebutuhan aktor, database, komponen teknis, dan acceptance criteria.

| ID | Nama PBI / Fondasi | Aktor Utama | Tabel DB Utama | Komponen Teknis Utama (Model, Service, Policy) | Acceptance Criteria Utama |
|---|---|---|---|---|---|
| **FND-01** | Login | Semua Peran | `users`, `companies` | `Auth`, `LoginRequest`, `AuthenticatedSessionController` | Pengguna dapat masuk dengan email dan `password_hash`, diarahkan ke dashboard sesuai peran. |
| **FND-02** | Logout | Semua Peran | `users` | `AuthenticatedSessionController` | Sesi aktif diakhiri dan token CSRF diregenerasi. |
| **FND-03** | Reset Password | Semua Peran | `users`, `password_reset_tokens` | `PasswordResetLinkController`, `NewPasswordController` | Permintaan tautan reset via email dan form ubah sandi baru berfungsi. |
| **FND-04** | Main Dashboard Dasar | Semua Peran | Berbagai tabel | Dashboard Controllers / Livewire Layouts | Kerangka halaman utama per peran menampilkan navigasi dan kartu ringkasan modul. |
| **PBI-01** | Registri Inspector | Admin, Inspector | `inspector_profiles`, `users` | `InspectorService`, `InspectorPolicy` | Admin mendaftarkan inspector beserta wilayah dan sertifikasi; status otomatis nonaktif jika kedaluwarsa. |
| **PBI-02** | Master Kategori & Toleransi | Admin | `categories` | `CategoryService`, `CategoryPolicy` | Admin mengelola kategori hierarkis, templat spesifikasi JSON, toleransi susut, dan tanda `is_b3_prohibited`. |
| **PBI-03** | Parameter Dampak & Faktor Emisi | Admin | `impact_factors` | `ImpactFactorService`, `ImpactCalculator` | Admin mengelola faktor emisi berversi dengan tanggal berlaku; versi historis tetap terjaga untuk order lama. |
| **PBI-04** | RFQ & Negosiasi Penawaran | Buyer, Seller | `rfq_contracts`, `rfq_offers`, `listings` | `RfqService`, `RfqPolicy` | Buyer ajukan RFQ; Seller terima, tolak, atau *counter-offer* (maks 5 putaran). |
| **PBI-05** | Purchase Order (PO) | Buyer, Seller | `purchase_orders`, `rfq_contracts` | `OrderStateMachine`, `PoService`, `PoPolicy` | Buyer buat PO (snapshot toleransi); Seller konfirmasi dengan stok di-lock via `lockForUpdate()`. |
| **PBI-06** | Penerbitan Invoice | Seller, Buyer | `invoices`, `purchase_orders` | `InvoiceNumberGenerator`, `InvoiceService`, `InvoicePolicy` | Seller menerbitkan invoice resmi atas PO Lolos Inspeksi; unduh PDF via dompdf; fitur *void*. |
| **PBI-07** | Verifikasi Pembayaran | Buyer, Seller | `payments`, `invoices` | `NotificationService`, `PaymentService`, `PaymentPolicy` | Buyer upload bukti transfer; Seller verifikasi atau tolak; valid mengubah status order ke `DIBAYAR`. |
| **PBI-08** | Profil & Verifikasi Perusahaan | Perusahaan, Admin | `companies`, `company_documents` | `EnsureCompanyVerified`, `CompanyService`, `CompanyPolicy` | Perusahaan kelola profil & dokumen legalitas; Admin verifikasi; hanya perusahaan terverifikasi bisa transaksi. |
| **PBI-09** | Listing Sisa Produksi | Seller | `listings`, `listing_attachments`, `categories` | `ListingService`, `ListingPolicy` | Seller buat listing (spesifikasi, volume, harga, bukti non-B3); otomatis ditolak jika kategori B3. |
| **PBI-10** | Jadwal Pasokan Berkala | Seller | `supply_schedules`, `listings` | `SupplyScheduleService`, `SupplySchedulePolicy` | Seller menetapkan jadwal pasokan berulang (mingguan/bulanan) per listing. |
| **PBI-11** | Pengiriman & Manifes Angkut | Seller, Buyer | `shipments_manifests`, `shipment_documents` | `ManifestGenerator`, `ShipmentPolicy` | Seller catat logistik (syarat: dibayar & dokumen non-B3 OK); sistem terbitkan Manifes Angkut PDF. |
| **PBI-12** | Penerimaan Barang & Realisasi Tonase | Buyer, Seller | `goods_receipts`, `purchase_orders` | `ToleranceEvaluator`, `OrderStateMachine`, `GoodsReceiptPolicy` | Buyer catat berat/kadar air; `ToleranceEvaluator` cek toleransi (dalam $\rightarrow$ Selesai, luar $\rightarrow$ Sengketa). |
| **PBI-13** | Sengketa & Klaim Ketidaksesuaian | Buyer, Seller, Admin | `disputes`, `purchase_orders` | `DisputeService`, `OrderStateMachine`, `DisputePolicy` | Pengelolaan tiket sengketa dan putusan penyelesaian oleh Admin. |
| **PBI-14** | Kebutuhan Bahan Baku Sekunder | Buyer | `demand_requests`, `categories` | `DemandService`, `DemandPolicy` | Buyer publikasikan kebutuhan bahan baku; Seller melihat lewat papan kebutuhan. |
| **PBI-15** | Ulasan & Rating Mitra | Seller, Buyer | `reviews`, `purchase_orders` | `ReviewService`, `ReviewPolicy` | Saling beri ulasan pasca order Selesai; sunting maksimal 7 hari; hitung skor rata-rata. |
| **PBI-16** | Daftar Pantau & Peringatan | Semua Peran | `watchlists` | `MatchWatchlists`, `NotificationService`, `WatchlistPolicy` | Pengguna buat kriteria pantauan dan terima notifikasi in-app saat listing cocok dipublikasikan. |
| **PBI-17** | Verifikasi Dokumen Legalitas Non-B3 | Inspector | `document_verifications`, `purchase_orders` | `DocumentVerificationService`, `InspectionPolicy` | Inspector periksa dokumen per order via checklist dan setujui/tolak. |
| **PBI-18** | Penugasan Inspeksi | Admin | `inspections`, `purchase_orders` | `InspectorMatchingService`, `InspectionModeAdvisor`, `InspectionPolicy` | Admin tugaskan inspector berdasarkan keahlian & rekomendasi mode (Desk / Lapangan). |
| **PBI-19** | Laporan Hasil Verifikasi Mutu | Inspector | `inspection_results`, `inspections` | `InspectionEvaluator`, `OrderStateMachine`, `InspectionPolicy` | Inspector isi laporan mutu; status lulus/gagal ubah status order menjadi Lolos/Gagal Inspeksi. |
| **PBI-20** | Pengawasan & Tindak Lanjut DLH | Admin / DLH | `compliance_notes`, `companies` | `DlhdComplianceService`, `CompliancePolicy` | DLH catat temuan/peringatan perusahaan, perbarui status tindak lanjut, dan ekspor laporan Excel. |
