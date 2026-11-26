# Database Design — SymbioNusa

> Dokumen ini merinci seluruh 29 tabel dalam skema database SymbioNusa berdasarkan sumber kebenaran DDL (`database/sql/clean_ddl.sql`), termasuk nama kolom, tipe data, primary key, foreign key, nullabilitas, default value, unique index, dan relasi kardinalitas.

---

## 1. Ringkasan 29 Tabel Relasional
1. `companies`
2. `users`
3. `company_documents`
4. `categories`
5. `inspector_profiles`
6. `impact_factors`
7. `listings`
8. `listing_attachments`
9. `supply_schedules`
10. `demand_requests`
11. `rfq_contracts`
12. `rfq_offers`
13. `purchase_orders`
14. `invoices`
15. `payments`
16. `shipments_manifests`
17. `goods_receipts`
18. `disputes`
19. `inspections`
20. `inspection_results`
21. `document_verifications`
22. `compliance_notes`
23. `reviews`
24. `impact_logs`
25. `watchlists`
26. `order_status_history`
27. `audit_logs`
28. `shipment_documents`
29. `notifications`

---

## 2. Rincian Skema Tabel (Schema-First DDL)

### 1. `companies`
- **Primary Key:** `id_company` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `nama`: VARCHAR(255), NOT NULL
  - `tipe`: ENUM('SELLER', 'BUYER', 'KEDUANYA'), NOT NULL
  - `nib`: VARCHAR(45), NULL
  - `alamat`: VARCHAR(255), NULL
  - `kontak`: VARCHAR(45), NULL
  - `lat`: DOUBLE, NULL
  - `lng`: DOUBLE, NULL
  - `tos_versi`: VARCHAR(45), NULL
  - `tos_disetujui_pada`: TIMESTAMP(6), NULL
  - `status_verifikasi`: ENUM('PENDING', 'TERVERIFIKASI', 'DITOLAK'), NOT NULL, DEFAULT 'PENDING'
- **Indexes:** UNIQUE INDEX `nib_UNIQUE` (`nib`)

### 2. `users`
- **Primary Key:** `id_user` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `nama`: VARCHAR(255), NOT NULL
  - `email`: VARCHAR(255), NOT NULL
  - `role`: ENUM('ADMIN', 'SELLER', 'BUYER', 'INSPECTOR'), NOT NULL
  - `password_hash`: VARCHAR(255), NULL
  - `status`: ENUM('AKTIF', 'NONAKTIF'), NOT NULL, DEFAULT 'AKTIF'
  - `id_company`: INT, NULL (FK ke `companies.id_company`)
- **Indexes:** UNIQUE INDEX `email_UNIQUE` (`email`), INDEX `fk_users_companies_idx` (`id_company`)
- **Foreign Keys:** `CONSTRAINT fk_users_companies FOREIGN KEY (id_company) REFERENCES companies(id_company)`

### 3. `company_documents`
- **Primary Key:** `id_company_document` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `jenis`: VARCHAR(45), NOT NULL
  - `file_url`: VARCHAR(512), NOT NULL
  - `berlaku_sampai`: DATE, NULL
  - `status`: ENUM('PENDING', 'VALID', 'KEDALUWARSA'), NOT NULL, DEFAULT 'PENDING'
  - `id_company`: INT, NOT NULL (FK ke `companies.id_company`)
- **Indexes:** INDEX `fk_company_documents_companies1_idx` (`id_company`)
- **Foreign Keys:** `CONSTRAINT fk_company_documents_companies1 FOREIGN KEY (id_company) REFERENCES companies(id_company)`

### 4. `categories`
- **Primary Key:** `id_categories` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `nama`: VARCHAR(100), NOT NULL
  - `spec_schema`: JSON, NULL
  - `is_b3_prohibited`: TINYINT, NOT NULL, DEFAULT 0
  - `toleransi_susut_persen`: DECIMAL(5,2), NOT NULL, DEFAULT 0
  - `kadar_air_maks_persen`: DECIMAL(5,2), NULL
  - `toleransi_kadar_air_poin`: DECIMAL(5,2), NULL
  - `tingkat_risiko`: ENUM('RENDAH', 'TINGGI'), NOT NULL, DEFAULT 'RENDAH'
  - `wajib_sertifikat_lab`: TINYINT, NOT NULL, DEFAULT 0
  - `categories_id_categories`: INT, NULL (Self-referencing FK ke `categories.id_categories`)
  - `status`: ENUM('AKTIF', 'ARSIP'), NOT NULL, DEFAULT 'AKTIF'
- **Indexes:** INDEX `fk_categories_categories1_idx` (`categories_id_categories`)
- **Foreign Keys:** `CONSTRAINT fk_categories_categories1 FOREIGN KEY (categories_id_categories) REFERENCES categories(id_categories)`

### 5. `inspector_profiles`
- **Primary Key:** `id_inspector` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `bidang_keahlian`: JSON, NULL
  - `wilayah_tugas`: VARCHAR(100), NULL
  - `no_sertifikat`: VARCHAR(100), NULL
  - `sertifikat_url`: VARCHAR(512), NULL
  - `berlaku_sampai`: DATE, NULL
  - `status_aktif`: TINYINT, NULL
  - `id_user`: INT, NOT NULL (FK ke `users.id_user`)
- **Indexes:** UNIQUE INDEX `users_id_user_UNIQUE` (`id_user`), INDEX `fk_inspector_profiles_users1_idx` (`id_user`)
- **Foreign Keys:** `CONSTRAINT fk_inspector_profiles_users1 FOREIGN KEY (id_user) REFERENCES users(id_user)`

### 6. `impact_factors`
- **Primary Key:** `id_impact_factor` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `ef_buang`: DECIMAL(8,4), NULL
  - `ef_virgin`: DECIMAL(8,4), NULL
  - `ef_proses`: DECIMAL(8,4), NULL
  - `ef_trans`: DECIMAL(8,4), NULL
  - `substitution_rate`: DECIMAL(4,2), NULL
  - `harga_virgin_ref`: DECIMAL(12,2), NULL
  - `biaya_buang_ref`: DECIMAL(12,2), NULL
  - `versi`: INT, NOT NULL
  - `berlaku_dari`: DATE, NULL
  - `aktif`: TINYINT, NOT NULL, DEFAULT 0
  - `categories_id_categories`: INT, NOT NULL (FK ke `categories.id_categories`)
- **Indexes:** UNIQUE INDEX `categories_id_categories, versi` (`categories_id_categories`, `versi`), INDEX `fk_impact_factors_categories1_idx` (`categories_id_categories`)
- **Foreign Keys:** `CONSTRAINT fk_impact_factors_categories1 FOREIGN KEY (categories_id_categories) REFERENCES categories(id_categories)`

### 7. `listings`
- **Primary Key:** `id_listings` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `judul`: VARCHAR(255), NOT NULL
  - `spesifikasi`: JSON, NULL
  - `volume_kg`: DECIMAL(12,2), NOT NULL
  - `stok_kg`: DECIMAL(12,2), NOT NULL
  - `harga_per_kg`: DECIMAL(12,2), NOT NULL
  - `biaya_buang_per_kg`: DECIMAL(12,2), NULL
  - `metode_buang_sebelumnya`: VARCHAR(100), NULL
  - `lokasi`: VARCHAR(255), NULL
  - `status`: ENUM('DRAF', 'PUBLISHED', 'DIARSIPKAN'), NOT NULL, DEFAULT 'DRAF'
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company`)
  - `categories_id_categories`: INT, NOT NULL (FK ke `categories.id_categories`)
- **Indexes:** INDEX `fk_listings_companies1_idx` (`companies_id_company`), INDEX `fk_listings_categories1_idx` (`categories_id_categories`)
- **Foreign Keys:** 
  - `CONSTRAINT fk_listings_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`
  - `CONSTRAINT fk_listings_categories1 FOREIGN KEY (categories_id_categories) REFERENCES categories(id_categories)`

### 8. `listing_attachments`
- **Primary Key:** `id_listing_attachment` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `jenis`: ENUM('SURAT_NON_B3', 'FOTO_SAMPEL', 'SERTIFIKAT_LAB'), NULL
  - `file_url`: VARCHAR(512), NULL
  - `tanggal_pengambilan`: DATE, NULL
  - `listings_id_listings`: INT, NOT NULL (FK ke `listings.id_listings`)
  - `status`: ENUM('AKTIF', 'NONAKTIF'), NOT NULL, DEFAULT 'AKTIF'
- **Indexes:** INDEX `fk_listing_attachments_listings1_idx` (`listings_id_listings`)
- **Foreign Keys:** `CONSTRAINT fk_listing_attachments_listings1 FOREIGN KEY (listings_id_listings) REFERENCES listings(id_listings)`

### 9. `supply_schedules`
- **Primary Key:** `id_supply_schedule` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `frekuensi`: ENUM('MINGGUAN', 'BULANAN'), NULL
  - `volume_per_periode_kg`: DECIMAL(12,2), NULL
  - `mulai`: DATE, NULL
  - `selesai`: DATE, NULL
  - `listings_id_listings`: INT, NOT NULL (FK ke `listings.id_listings`)
  - `status`: ENUM('AKTIF', 'DIHENTIKAN'), NOT NULL, DEFAULT 'AKTIF'
- **Indexes:** INDEX `fk_supply_schedules_listings1_idx` (`listings_id_listings`)
- **Foreign Keys:** `CONSTRAINT fk_supply_schedules_listings1 FOREIGN KEY (listings_id_listings) REFERENCES listings(id_listings)`

### 10. `demand_requests`
- **Primary Key:** `id_demand_request` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `spesifikasi_min`: JSON, NULL
  - `volume_kg`: DECIMAL(12,2), NOT NULL
  - `harga_maks`: DECIMAL(12,2), NULL
  - `berlaku_sampai`: DATE, NULL
  - `status`: ENUM('TERBUKA', 'TERPENUHI', 'KEDALUWARSA'), NOT NULL, DEFAULT 'TERBUKA'
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company`)
  - `categories_id_categories`: INT, NOT NULL (FK ke `categories.id_categories`)
  - `frekuensi`: ENUM('SEKALI', 'MINGGUAN', 'BULANAN'), NOT NULL, DEFAULT 'SEKALI'
- **Indexes:** INDEX `fk_demand_requests_companies1_idx` (`companies_id_company`), INDEX `fk_demand_requests_categories1_idx` (`categories_id_categories`)
- **Foreign Keys:**
  - `CONSTRAINT fk_demand_requests_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`
  - `CONSTRAINT fk_demand_requests_categories1 FOREIGN KEY (categories_id_categories) REFERENCES categories(id_categories)`

### 11. `rfq_contracts`
- **Primary Key:** `id_rfq_contract` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `volume_kg_disepakati`: DECIMAL(12,2), NULL
  - `harga_disepakati`: DECIMAL(12,2), NULL
  - `tenggat`: TIMESTAMP(6), NULL
  - `status`: ENUM('TERBUKA', 'NEGOSIASI', 'DISEPAKATI', 'DITOLAK', 'KEDALUWARSA', 'DITARIK'), NOT NULL, DEFAULT 'TERBUKA'
  - `listings_id_listings`: INT, NOT NULL (FK ke `listings.id_listings`)
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company`)
  - `demand_requests_id_demand_request`: INT, NULL (FK ke `demand_requests.id_demand_request`)
- **Indexes:** INDEX `fk_rfq_contracts_listings1_idx`, `fk_rfq_contracts_companies1_idx`, `fk_rfq_contracts_demand_requests1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_rfq_contracts_listings1 FOREIGN KEY (listings_id_listings) REFERENCES listings(id_listings)`
  - `CONSTRAINT fk_rfq_contracts_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`
  - `CONSTRAINT fk_rfq_contracts_demand_requests1 FOREIGN KEY (demand_requests_id_demand_request) REFERENCES demand_requests(id_demand_request)`

### 12. `rfq_offers`
- **Primary Key:** `id_rfq_offer` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `volume_kg`: DECIMAL(12,2), NOT NULL
  - `harga_per_kg`: DECIMAL(12,2), NOT NULL
  - `catatan`: TEXT(255), NULL
  - `urutan`: INT, NOT NULL
  - `rfq_contracts_id_rfq_contract`: INT, NOT NULL (FK ke `rfq_contracts.id_rfq_contract`)
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company`)
- **Indexes:** INDEX `fk_rfq_offers_rfq_contracts1_idx`, `fk_rfq_offers_companies1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_rfq_offers_rfq_contracts1 FOREIGN KEY (rfq_contracts_id_rfq_contract) REFERENCES rfq_contracts(id_rfq_contract)`
  - `CONSTRAINT fk_rfq_offers_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`

### 13. `purchase_orders`
- **Primary Key:** `id_purchase_order` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `volume_kg`: DECIMAL(12,2), NOT NULL
  - `harga_per_kg`: DECIMAL(12,2), NOT NULL
  - `toleransi_persen`: DECIMAL(5,2), NOT NULL, DEFAULT 0
  - `kadar_air_maks_persen`: DECIMAL(5,2), NULL
  - `toleransi_kadar_air_poin`: DECIMAL(5,2), NULL
  - `jarak_km`: DECIMAL(8,2), NULL
  - `status`: ENUM('DRAFT', 'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI', 'GAGAL_INSPEKSI', 'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA', 'SELESAI', 'SENGKETA', 'DIBATALKAN', 'DITOLAK'), NOT NULL, DEFAULT 'DRAFT'
  - `rfq_contracts_id_rfq_contract`: INT, NOT NULL (FK ke `rfq_contracts.id_rfq_contract`)
  - `companies_id_seller`: INT, NOT NULL (FK ke `companies.id_company`)
  - `companies_id_buyer`: INT, NOT NULL (FK ke `companies.id_company`)
- **Indexes:** UNIQUE INDEX `rfq_contracts_id_rfq_contract_UNIQUE` (`rfq_contracts_id_rfq_contract`), INDEX `fk_purchase_orders_rfq_contracts1_idx`, `fk_purchase_orders_companies1_idx`, `fk_purchase_orders_companies2_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_purchase_orders_rfq_contracts1 FOREIGN KEY (rfq_contracts_id_rfq_contract) REFERENCES rfq_contracts(id_rfq_contract)`
  - `CONSTRAINT fk_purchase_orders_companies1 FOREIGN KEY (companies_id_seller) REFERENCES companies(id_company)`
  - `CONSTRAINT fk_purchase_orders_companies2 FOREIGN KEY (companies_id_buyer) REFERENCES companies(id_company)`

### 14. `invoices`
- **Primary Key:** `id_invoice` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `nomor`: VARCHAR(100), NOT NULL
  - `total`: DECIMAL(12,2), NOT NULL
  - `jatuh_tempo`: DATE, NULL
  - `status`: ENUM('TERBIT', 'DIBAYAR', 'VOID'), NOT NULL, DEFAULT 'TERBIT'
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
- **Indexes:** UNIQUE INDEX `nomor_UNIQUE` (`nomor`), UNIQUE INDEX `purchase_orders_id_purchase_order_UNIQUE` (`purchase_orders_id_purchase_order`)
- **Foreign Keys:** `CONSTRAINT fk_invoices_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`

### 15. `payments`
- **Primary Key:** `id_payment` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `bukti_url`: VARCHAR(512), NULL
  - `tgl_transfer`: DATE, NULL
  - `nominal`: DECIMAL(12,2), NOT NULL
  - `rekening_asal`: VARCHAR(100), NULL
  - `status_verifikasi`: ENUM('MENUNGGU', 'VALID', 'DITOLAK'), NOT NULL, DEFAULT 'MENUNGGU'
  - `alasan_tolak`: TEXT(512), NULL
  - `invoices_id_invoice`: INT, NOT NULL (FK ke `invoices.id_invoice`)
- **Indexes:** INDEX `fk_payments_invoices1_idx` (`invoices_id_invoice`)
- **Foreign Keys:** `CONSTRAINT fk_payments_invoices1 FOREIGN KEY (invoices_id_invoice) REFERENCES invoices(id_invoice)`

### 16. `shipments_manifests`
- **Primary Key:** `id_shipment_manifest` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `armada`: VARCHAR(100), NULL
  - `plat`: VARCHAR(20), NULL
  - `sopir`: VARCHAR(100), NULL
  - `manifes_url`: VARCHAR(512), NULL
  - `status`: ENUM('DRAF', 'DALAM_PERJALANAN', 'TIBA', 'DIBATALKAN'), NOT NULL, DEFAULT 'DRAF'
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
  - `berat_dikirim_kg`: DECIMAL(12,2), NOT NULL, DEFAULT 0
  - `jadwal`: TIMESTAMP(6), NULL
  - `rute`: VARCHAR(255), NULL
  - `no_manifes`: VARCHAR(50), NULL
- **Indexes:** UNIQUE INDEX `no_manifes_UNIQUE` (`no_manifes`), INDEX `fk_shipments_manifests_purchase_orders1_idx` (`purchase_orders_id_purchase_order`)
- **Foreign Keys:** `CONSTRAINT fk_shipments_manifests_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`

### 17. `goods_receipts`
- **Primary Key:** `id_good_receipt` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `berat_diterima_kg`: DECIMAL(12,2), NOT NULL
  - `berat_ditolak_kg`: DECIMAL(12,2), NOT NULL, DEFAULT 0
  - `kadar_air_persen`: DECIMAL(5,2), NULL
  - `selisih_persen`: DECIMAL(5,2), NULL
  - `status_toleransi`: ENUM('DALAM', 'DI_LUAR'), NULL
  - `nilai_penyesuaian_rp`: DECIMAL(12,2), NULL
  - `kondisi`: VARCHAR(255), NULL
  - `catatan`: TEXT(255), NULL
  - `waktu`: TIMESTAMP(6), NULL
  - `shipments_manifests_id_shipment_manifest`: INT, NOT NULL (FK ke `shipments_manifests.id_shipment_manifest`)
  - `status_penyesuaian`: ENUM('TIDAK_ADA', 'MENUNGGU', 'SELESAI'), NOT NULL, DEFAULT 'TIDAK_ADA'
- **Indexes:** UNIQUE INDEX `shipments_manifests_id_shipment_manifest_UNIQUE` (`shipments_manifests_id_shipment_manifest`)
- **Foreign Keys:** `CONSTRAINT fk_goods_receipts_shipments_manifests1 FOREIGN KEY (shipments_manifests_id_shipment_manifest) REFERENCES shipments_manifests(id_shipment_manifest)`

### 18. `disputes`
- **Primary Key:** `id_dispute` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `jenis`: ENUM('SELISIH_TONASE', 'KADAR_AIR', 'MUTU', 'LAINNYA'), NOT NULL
  - `deskripsi`: TEXT(512), NULL
  - `bukti_url`: VARCHAR(512), NULL
  - `status`: ENUM('DIBUKA', 'DIPROSES', 'SELESAI', 'DITARIK'), NOT NULL, DEFAULT 'DIBUKA'
  - `putusan`: TEXT(512), NULL
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company`)
- **Indexes:** INDEX `fk_disputes_purchase_orders1_idx`, `fk_disputes_companies1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_disputes_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`
  - `CONSTRAINT fk_disputes_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`

### 19. `inspections`
- **Primary Key:** `id_inspection` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `mode_verifikasi`: ENUM('DESK', 'LAPANGAN'), NOT NULL
  - `alasan_mode`: TEXT(255), NULL
  - `jadwal`: TIMESTAMP(6), NULL
  - `status`: ENUM('DITUGASKAN', 'DIPROSES', 'SELESAI', 'DIBATALKAN'), NOT NULL, DEFAULT 'DITUGASKAN'
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
  - `users_id_user`: INT, NOT NULL (FK ke `users.id_user`)
- **Indexes:** INDEX `fk_inspections_purchase_orders1_idx`, `fk_inspections_users1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_inspections_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`
  - `CONSTRAINT fk_inspections_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

### 20. `inspection_results`
- **Primary Key:** `id_inspection_result` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `parameter_uji`: JSON, NULL
  - `sumber_bukti`: ENUM('FOTO', 'LAB', 'LAPANGAN'), NULL
  - `hasil`: ENUM('LULUS', 'GAGAL'), NULL
  - `lampiran_url`: VARCHAR(512), NULL
  - `status`: ENUM('DRAF', 'FINAL'), NOT NULL, DEFAULT 'DRAF'
  - `inspections_id_inspection`: INT, NOT NULL (FK ke `inspections.id_inspection`)
- **Indexes:** UNIQUE INDEX `inspections_id_inspection_UNIQUE` (`inspections_id_inspection`)
- **Foreign Keys:** `CONSTRAINT fk_inspection_results_inspections1 FOREIGN KEY (inspections_id_inspection) REFERENCES inspections(id_inspection)`

### 21. `document_verifications`
- **Primary Key:** `id_document_verification` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `verifiable_type`: VARCHAR(255), NULL
  - `verifiable_id`: INT, NULL
  - `checklist`: JSON, NULL
  - `status`: ENUM('MENUNGGU', 'SETUJU', 'DITOLAK', 'DIBATALKAN'), NOT NULL, DEFAULT 'MENUNGGU'
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
  - `users_id_user`: INT, NOT NULL (FK ke `users.id_user`)
  - `alasan`: TEXT(512), NULL
- **Indexes:** INDEX `fk_document_verifications_purchase_orders1_idx`, `fk_document_verifications_users1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_document_verifications_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`
  - `CONSTRAINT fk_document_verifications_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

### 22. `compliance_notes`
- **Primary Key:** `id_compliance_note` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `jenis`: ENUM('TEMUAN', 'PERINGATAN'), NOT NULL
  - `deskripsi`: TEXT(512), NULL
  - `tenggat`: DATE, NULL
  - `status_tindak_lanjut`: ENUM('TEMUAN', 'PERINGATAN', 'DITINDAKLANJUTI', 'SELESAI'), NOT NULL, DEFAULT 'TEMUAN'
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company`)
  - `users_id_user`: INT, NOT NULL (FK ke `users.id_user`)
- **Indexes:** INDEX `fk_compliance_notes_companies1_idx`, `fk_compliance_notes_users1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_compliance_notes_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`
  - `CONSTRAINT fk_compliance_notes_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

### 23. `reviews`
- **Primary Key:** `id_review` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `rating`: TINYINT, NOT NULL
  - `komentar`: TEXT(512), NULL
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
  - `companies_id_company`: INT, NOT NULL (FK ke `companies.id_company` - pemberi ulasan)
  - `companies_id_target_company`: INT, NOT NULL (FK ke `companies.id_company` - target ulasan)
- **Indexes:** UNIQUE INDEX `purchase_orders_id_purchase_order, companies_id_company` (`purchase_orders_id_purchase_order`, `companies_id_company`)
- **Foreign Keys:**
  - `CONSTRAINT fk_reviews_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`
  - `CONSTRAINT fk_reviews_companies1 FOREIGN KEY (companies_id_company) REFERENCES companies(id_company)`
  - `CONSTRAINT fk_reviews_companies2 FOREIGN KEY (companies_id_target_company) REFERENCES companies(id_company)`

### 24. `impact_logs`
- **Primary Key:** `id_impact_log` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `tonase_ton`: DECIMAL(10,3), NULL
  - `hemat_seller_rp`: DECIMAL(12,2), NULL
  - `hemat_buyer_rp`: DECIMAL(12,2), NULL
  - `co2e_dihindari_kg`: DECIMAL(12,2), NULL
  - `co2e_angkutan_kg`: DECIMAL(12,2), NULL
  - `co2e_net_kg`: DECIMAL(12,2), NULL
  - `versi_faktor`: INT, NULL
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
- **Indexes:** UNIQUE INDEX `purchase_orders_id_purchase_order_UNIQUE` (`purchase_orders_id_purchase_order`)
- **Foreign Keys:** `CONSTRAINT fk_impact_logs_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`

### 25. `watchlists`
- **Primary Key:** `id_watchlist` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `kriteria`: JSON, NULL
  - `aktif`: TINYINT, NULL
  - `users_id_user`: INT, NOT NULL (FK ke `users.id_user`)
- **Indexes:** INDEX `fk_watchlist_users1_idx` (`users_id_user`)
- **Foreign Keys:** `CONSTRAINT fk_watchlist_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

### 26. `order_status_history`
- **Primary Key:** `id_order_status_history` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `dari`: VARCHAR(50), NULL
  - `ke`: VARCHAR(50), NULL
  - `alasan`: TEXT(512), NULL
  - `waktu`: TIMESTAMP(6), NULL
  - `purchase_orders_id_purchase_order`: INT, NOT NULL (FK ke `purchase_orders.id_purchase_order`)
  - `users_id_user`: INT, NULL (FK ke `users.id_user`)
- **Indexes:** INDEX `fk_order_status_history_purchase_orders1_idx`, `fk_order_status_history_users1_idx`
- **Foreign Keys:**
  - `CONSTRAINT fk_order_status_history_purchase_orders1 FOREIGN KEY (purchase_orders_id_purchase_order) REFERENCES purchase_orders(id_purchase_order)`
  - `CONSTRAINT fk_order_status_history_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

### 27. `audit_logs`
- **Primary Key:** `id_audit_log` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `waktu`: TIMESTAMP(6), NULL
  - `users_id_user`: INT, NULL (FK ke `users.id_user`)
  - `aksi`: VARCHAR(100), NOT NULL
  - `auditable_type`: VARCHAR(100), NULL
  - `auditable_id`: INT, NULL
  - `nilai_lama`: JSON, NULL
  - `nilai_baru`: JSON, NULL
  - `ip_address`: VARCHAR(45), NULL
- **Indexes:** INDEX `fk_audit_logs_users1_idx` (`users_id_user`)
- **Foreign Keys:** `CONSTRAINT fk_audit_logs_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

### 28. `shipment_documents`
- **Primary Key:** `id_shipment_document` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `jenis`: VARCHAR(100), NULL
  - `file_url`: VARCHAR(512), NULL
  - `shipments_manifests_id_shipment_manifest`: INT, NOT NULL (FK ke `shipments_manifests.id_shipment_manifest`)
- **Indexes:** INDEX `fk_shipment_documents_shipments_manifests1_idx` (`shipments_manifests_id_shipment_manifest`)
- **Foreign Keys:** `CONSTRAINT fk_shipment_documents_shipments_manifests1 FOREIGN KEY (shipments_manifests_id_shipment_manifest) REFERENCES shipments_manifests(id_shipment_manifest)`

### 29. `notifications`
- **Primary Key:** `id_notification` (INT, NOT NULL, AUTO_INCREMENT)
- **Kolom:**
  - `jenis`: VARCHAR(255), NULL
  - `payload`: JSON, NULL
  - `read_at`: TIMESTAMP(6), NULL
  - `created_at`: TIMESTAMP(6), NULL
  - `users_id_user`: INT, NOT NULL (FK ke `users.id_user`)
- **Indexes:** INDEX `fk_notifications_users1_idx` (`users_id_user`)
- **Foreign Keys:** `CONSTRAINT fk_notifications_users1 FOREIGN KEY (users_id_user) REFERENCES users(id_user)`

---

## 3. Konvensi & Aturan Eloquent (`AGENTS.md` Bagian 5)
1. **Primary Key:** Mengikuti pola `id_<entitas>` (misal: `id_company`, `id_user`, `id_purchase_order`, `id_categories`, `id_listings`). Setiap model wajib mendeklarasikan `$table`, `$primaryKey`, dan `$fillable`.
2. **Foreign Key:** Mengikuti pola `<tabel>_id_<entitas>` (misal: `companies_id_seller`, `rfq_contracts_id_rfq_contract`, kecuali `users.id_company`). Relasi didefinisikan dengan kunci eksplisit.
3. **Timestamps:** DDL tidak menggunakan `created_at` dan `updated_at`, sehingga model Eloquent **wajib** mendeklarasikan `public $timestamps = false;` (kecuali pada tabel yang secara eksplisit memilikinya seperti `notifications`).
4. **Autentikasi Password:** Kolom `users.password_hash` menggantikan `password`. Model `User` (`app/Models/User.php`) telah mengoverride `getAuthPassword()`.
