CREATE TABLE IF NOT EXISTS `companies` (
  `id_company` INT NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(255) NOT NULL,
  `tipe` ENUM('SELLER', 'BUYER', 'KEDUANYA') NOT NULL,
  `nib` VARCHAR(45) NULL,
  `alamat` VARCHAR(255) NULL,
  `kontak` VARCHAR(45) NULL,
  `lat` DOUBLE NULL,
  `lng` DOUBLE NULL,
  `tos_versi` VARCHAR(45) NULL,
  `tos_disetujui_pada` TIMESTAMP(6) NULL,
  `status_verifikasi` ENUM('PENDING', 'TERVERIFIKASI', 'DITOLAK') NOT NULL DEFAULT 'PENDING',
  PRIMARY KEY (`id_company`),
  UNIQUE INDEX `nib_UNIQUE` (`nib`))
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `role` ENUM('ADMIN', 'SELLER', 'BUYER', 'INSPECTOR') NOT NULL,
  `password_hash` VARCHAR(255) NULL,
  `status` ENUM('AKTIF', 'NONAKTIF') NOT NULL DEFAULT 'AKTIF',
  `id_company` INT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE INDEX `email_UNIQUE` (`email`),
  INDEX `fk_users_companies_idx` (`id_company`),
  CONSTRAINT `fk_users_companies`
    FOREIGN KEY (`id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `company_documents` (
  `id_company_document` INT NOT NULL AUTO_INCREMENT,
  `jenis` VARCHAR(45) NOT NULL,
  `file_url` VARCHAR(512) NOT NULL,
  `berlaku_sampai` DATE NULL,
  `status` ENUM('PENDING', 'VALID', 'KEDALUWARSA') NOT NULL DEFAULT 'PENDING',
  `id_company` INT NOT NULL,
  PRIMARY KEY (`id_company_document`),
  INDEX `fk_company_documents_companies1_idx` (`id_company`),
  CONSTRAINT `fk_company_documents_companies1`
    FOREIGN KEY (`id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `categories` (
  `id_categories` INT NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `spec_schema` JSON NULL,
  `is_b3_prohibited` TINYINT NOT NULL DEFAULT 0,
  `toleransi_susut_persen` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `kadar_air_maks_persen` DECIMAL(5,2) NULL,
  `toleransi_kadar_air_poin` DECIMAL(5,2) NULL,
  `tingkat_risiko` ENUM('RENDAH', 'TINGGI') NOT NULL DEFAULT 'RENDAH',
  `wajib_sertifikat_lab` TINYINT NOT NULL DEFAULT 0,
  `categories_id_categories` INT NULL,
  `status` ENUM('AKTIF', 'ARSIP') NOT NULL DEFAULT 'AKTIF',
  PRIMARY KEY (`id_categories`),
  INDEX `fk_categories_categories1_idx` (`categories_id_categories`),
  CONSTRAINT `fk_categories_categories1`
    FOREIGN KEY (`categories_id_categories`)
    REFERENCES `categories` (`id_categories`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `inspector_profiles` (
  `id_inspector` INT NOT NULL AUTO_INCREMENT,
  `bidang_keahlian` JSON NULL,
  `wilayah_tugas` VARCHAR(100) NULL,
  `no_sertifikat` VARCHAR(100) NULL,
  `sertifikat_url` VARCHAR(512) NULL,
  `berlaku_sampai` DATE NULL,
  `status_aktif` TINYINT NULL,
  `id_user` INT NOT NULL,
  PRIMARY KEY (`id_inspector`),
  INDEX `fk_inspector_profiles_users1_idx` (`id_user`),
  UNIQUE INDEX `users_id_user_UNIQUE` (`id_user`),
  CONSTRAINT `fk_inspector_profiles_users1`
    FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `impact_factors` (
  `id_impact_factor` INT NOT NULL AUTO_INCREMENT,
  `ef_buang` DECIMAL(8,4) NULL,
  `ef_virgin` DECIMAL(8,4) NULL,
  `ef_proses` DECIMAL(8,4) NULL,
  `ef_trans` DECIMAL(8,4) NULL,
  `substitution_rate` DECIMAL(4,2) NULL,
  `harga_virgin_ref` DECIMAL(12,2) NULL,
  `biaya_buang_ref` DECIMAL(12,2) NULL,
  `versi` INT NOT NULL,
  `berlaku_dari` DATE NULL,
  `aktif` TINYINT NOT NULL DEFAULT 0,
  `categories_id_categories` INT NOT NULL,
  PRIMARY KEY (`id_impact_factor`),
  INDEX `fk_impact_factors_categories1_idx` (`categories_id_categories`),
  UNIQUE INDEX `categories_id_categories, versicategories_id_categories, versi` (`categories_id_categories`, `versi`),
  CONSTRAINT `fk_impact_factors_categories1`
    FOREIGN KEY (`categories_id_categories`)
    REFERENCES `categories` (`id_categories`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `listings` (
  `id_listings` INT NOT NULL AUTO_INCREMENT,
  `judul` VARCHAR(255) NOT NULL,
  `spesifikasi` JSON NULL,
  `volume_kg` DECIMAL(12,2) NOT NULL,
  `stok_kg` DECIMAL(12,2) NOT NULL,
  `harga_per_kg` DECIMAL(12,2) NOT NULL,
  `biaya_buang_per_kg` DECIMAL(12,2) NULL,
  `metode_buang_sebelumnya` VARCHAR(100) NULL,
  `lokasi` VARCHAR(255) NULL,
  `status` ENUM('DRAF', 'PUBLISHED', 'DIARSIPKAN') NOT NULL DEFAULT 'DRAF',
  `companies_id_company` INT NOT NULL,
  `categories_id_categories` INT NOT NULL,
  PRIMARY KEY (`id_listings`),
  INDEX `fk_listings_companies1_idx` (`companies_id_company`),
  INDEX `fk_listings_categories1_idx` (`categories_id_categories`),
  CONSTRAINT `fk_listings_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_listings_categories1`
    FOREIGN KEY (`categories_id_categories`)
    REFERENCES `categories` (`id_categories`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `listing_attachments` (
  `id_listing_attachment` INT NOT NULL AUTO_INCREMENT,
  `jenis` ENUM('SURAT_NON_B3', 'FOTO_SAMPEL', 'SERTIFIKAT_LAB') NULL,
  `file_url` VARCHAR(512) NULL,
  `tanggal_pengambilan` DATE NULL,
  `listings_id_listings` INT NOT NULL,
  `status` ENUM('AKTIF', 'NONAKTIF') NOT NULL DEFAULT 'AKTIF',
  PRIMARY KEY (`id_listing_attachment`),
  INDEX `fk_listing_attachments_listings1_idx` (`listings_id_listings`),
  CONSTRAINT `fk_listing_attachments_listings1`
    FOREIGN KEY (`listings_id_listings`)
    REFERENCES `listings` (`id_listings`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `supply_schedules` (
  `id_supply_schedule` INT NOT NULL AUTO_INCREMENT,
  `frekuensi` ENUM('MINGGUAN', 'BULANAN') NULL,
  `volume_per_periode_kg` DECIMAL(12,2) NULL,
  `mulai` DATE NULL,
  `selesai` DATE NULL,
  `listings_id_listings` INT NOT NULL,
  `status` ENUM('AKTIF', 'DIHENTIKAN') NOT NULL DEFAULT 'AKTIF',
  PRIMARY KEY (`id_supply_schedule`),
  INDEX `fk_supply_schedules_listings1_idx` (`listings_id_listings`),
  CONSTRAINT `fk_supply_schedules_listings1`
    FOREIGN KEY (`listings_id_listings`)
    REFERENCES `listings` (`id_listings`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `demand_requests` (
  `id_demand_request` INT NOT NULL AUTO_INCREMENT,
  `spesifikasi_min` JSON NULL,
  `volume_kg` DECIMAL(12,2) NOT NULL,
  `harga_maks` DECIMAL(12,2) NULL,
  `berlaku_sampai` DATE NULL,
  `status` ENUM('TERBUKA', 'TERPENUHI', 'KEDALUWARSA') NOT NULL DEFAULT 'TERBUKA',
  `companies_id_company` INT NOT NULL,
  `categories_id_categories` INT NOT NULL,
  `frekuensi` ENUM('SEKALI', 'MINGGUAN', 'BULANAN') NOT NULL DEFAULT 'SEKALI',
  PRIMARY KEY (`id_demand_request`),
  INDEX `fk_demand_requests_companies1_idx` (`companies_id_company`),
  INDEX `fk_demand_requests_categories1_idx` (`categories_id_categories`),
  CONSTRAINT `fk_demand_requests_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_demand_requests_categories1`
    FOREIGN KEY (`categories_id_categories`)
    REFERENCES `categories` (`id_categories`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `rfq_contracts` (
  `id_rfq_contract` INT NOT NULL AUTO_INCREMENT,
  `volume_kg_disepakati` DECIMAL(12,2) NULL,
  `harga_disepakati` DECIMAL(12,2) NULL,
  `tenggat` TIMESTAMP(6) NULL,
  `status` ENUM('TERBUKA', 'NEGOSIASI', 'DISEPAKATI', 'DITOLAK', 'KEDALUWARSA', 'DITARIK') NOT NULL DEFAULT 'TERBUKA',
  `listings_id_listings` INT NOT NULL,
  `companies_id_company` INT NOT NULL,
  `demand_requests_id_demand_request` INT NULL,
  PRIMARY KEY (`id_rfq_contract`),
  INDEX `fk_rfq_contracts_listings1_idx` (`listings_id_listings`),
  INDEX `fk_rfq_contracts_companies1_idx` (`companies_id_company`),
  INDEX `fk_rfq_contracts_demand_requests1_idx` (`demand_requests_id_demand_request`),
  CONSTRAINT `fk_rfq_contracts_listings1`
    FOREIGN KEY (`listings_id_listings`)
    REFERENCES `listings` (`id_listings`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rfq_contracts_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rfq_contracts_demand_requests1`
    FOREIGN KEY (`demand_requests_id_demand_request`)
    REFERENCES `demand_requests` (`id_demand_request`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `rfq_offers` (
  `id_rfq_offer` INT NOT NULL AUTO_INCREMENT,
  `volume_kg` DECIMAL(12,2) NOT NULL,
  `harga_per_kg` DECIMAL(12,2) NOT NULL,
  `catatan` TEXT(255) NULL,
  `urutan` INT NOT NULL,
  `rfq_contracts_id_rfq_contract` INT NOT NULL,
  `companies_id_company` INT NOT NULL,
  PRIMARY KEY (`id_rfq_offer`),
  INDEX `fk_rfq_offers_rfq_contracts1_idx` (`rfq_contracts_id_rfq_contract`),
  INDEX `fk_rfq_offers_companies1_idx` (`companies_id_company`),
  CONSTRAINT `fk_rfq_offers_rfq_contracts1`
    FOREIGN KEY (`rfq_contracts_id_rfq_contract`)
    REFERENCES `rfq_contracts` (`id_rfq_contract`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rfq_offers_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id_purchase_order` INT NOT NULL AUTO_INCREMENT,
  `volume_kg` DECIMAL(12,2) NOT NULL,
  `harga_per_kg` DECIMAL(12,2) NOT NULL,
  `toleransi_persen` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `kadar_air_maks_persen` DECIMAL(5,2) NULL,
  `toleransi_kadar_air_poin` DECIMAL(5,2) NULL,
  `jarak_km` DECIMAL(8,2) NULL,
  `status` ENUM('DRAFT', 'MENUNGGU_KONFIRMASI', 'DIKONFIRMASI', 'INSPEKSI', 'LOLOS_INSPEKSI', 'GAGAL_INSPEKSI', 'INVOICED', 'DIBAYAR', 'DALAM_PENGIRIMAN', 'DITERIMA', 'SELESAI', 'SENGKETA', 'DIBATALKAN', 'DITOLAK') NOT NULL DEFAULT 'DRAFT',
  `rfq_contracts_id_rfq_contract` INT NOT NULL,
  `companies_id_seller` INT NOT NULL,
  `companies_id_buyer` INT NOT NULL,
  PRIMARY KEY (`id_purchase_order`),
  INDEX `fk_purchase_orders_rfq_contracts1_idx` (`rfq_contracts_id_rfq_contract`),
  INDEX `fk_purchase_orders_companies1_idx` (`companies_id_seller`),
  INDEX `fk_purchase_orders_companies2_idx` (`companies_id_buyer`),
  UNIQUE INDEX `rfq_contracts_id_rfq_contract_UNIQUE` (`rfq_contracts_id_rfq_contract`),
  CONSTRAINT `fk_purchase_orders_rfq_contracts1`
    FOREIGN KEY (`rfq_contracts_id_rfq_contract`)
    REFERENCES `rfq_contracts` (`id_rfq_contract`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_purchase_orders_companies1`
    FOREIGN KEY (`companies_id_seller`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_purchase_orders_companies2`
    FOREIGN KEY (`companies_id_buyer`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id_invoice` INT NOT NULL AUTO_INCREMENT,
  `nomor` VARCHAR(100) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  `jatuh_tempo` DATE NULL,
  `status` ENUM('TERBIT', 'DIBAYAR', 'VOID') NOT NULL DEFAULT 'TERBIT',
  `purchase_orders_id_purchase_order` INT NOT NULL,
  PRIMARY KEY (`id_invoice`),
  UNIQUE INDEX `nomor_UNIQUE` (`nomor`),
  INDEX `fk_invoices_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  UNIQUE INDEX `purchase_orders_id_purchase_order_UNIQUE` (`purchase_orders_id_purchase_order`),
  CONSTRAINT `fk_invoices_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `payments` (
  `id_payment` INT NOT NULL AUTO_INCREMENT,
  `bukti_url` VARCHAR(512) NULL,
  `tgl_transfer` DATE NULL,
  `nominal` DECIMAL(12,2) NOT NULL,
  `rekening_asal` VARCHAR(100) NULL,
  `status_verifikasi` ENUM('MENUNGGU', 'VALID', 'DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
  `alasan_tolak` TEXT(512) NULL,
  `invoices_id_invoice` INT NOT NULL,
  PRIMARY KEY (`id_payment`),
  INDEX `fk_payments_invoices1_idx` (`invoices_id_invoice`),
  CONSTRAINT `fk_payments_invoices1`
    FOREIGN KEY (`invoices_id_invoice`)
    REFERENCES `invoices` (`id_invoice`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `shipments_manifests` (
  `id_shipment_manifest` INT NOT NULL AUTO_INCREMENT,
  `armada` VARCHAR(100) NULL,
  `plat` VARCHAR(20) NULL,
  `sopir` VARCHAR(100) NULL,
  `manifes_url` VARCHAR(512) NULL,
  `status` ENUM('DRAF', 'DALAM_PERJALANAN', 'TIBA', 'DIBATALKAN') NOT NULL DEFAULT 'DRAF',
  `purchase_orders_id_purchase_order` INT NOT NULL,
  `berat_dikirim_kg` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `jadwal` TIMESTAMP(6) NULL,
  `rute` VARCHAR(255) NULL,
  `no_manifes` VARCHAR(50) NULL,
  PRIMARY KEY (`id_shipment_manifest`),
  INDEX `fk_shipments_manifests_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  UNIQUE INDEX `no_manifes_UNIQUE` (`no_manifes`),
  CONSTRAINT `fk_shipments_manifests_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `goods_receipts` (
  `id_good_receipt` INT NOT NULL AUTO_INCREMENT,
  `berat_diterima_kg` DECIMAL(12,2) NOT NULL,
  `berat_ditolak_kg` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `kadar_air_persen` DECIMAL(5,2) NULL,
  `selisih_persen` DECIMAL(5,2) NULL,
  `status_toleransi` ENUM('DALAM', 'DI_LUAR') NULL,
  `nilai_penyesuaian_rp` DECIMAL(12,2) NULL,
  `kondisi` VARCHAR(255) NULL,
  `catatan` TEXT(255) NULL,
  `waktu` TIMESTAMP(6) NULL,
  `shipments_manifests_id_shipment_manifest` INT NOT NULL,
  `status_penyesuaian` ENUM('TIDAK_ADA', 'MENUNGGU', 'SELESAI') NOT NULL DEFAULT 'TIDAK_ADA',
  PRIMARY KEY (`id_good_receipt`),
  INDEX `fk_goods_receipts_shipments_manifests1_idx` (`shipments_manifests_id_shipment_manifest`),
  UNIQUE INDEX `shipments_manifests_id_shipment_manifest_UNIQUE` (`shipments_manifests_id_shipment_manifest`),
  CONSTRAINT `fk_goods_receipts_shipments_manifests1`
    FOREIGN KEY (`shipments_manifests_id_shipment_manifest`)
    REFERENCES `shipments_manifests` (`id_shipment_manifest`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `disputes` (
  `id_dispute` INT NOT NULL AUTO_INCREMENT,
  `jenis` ENUM('SELISIH_TONASE', 'KADAR_AIR', 'MUTU', 'LAINNYA') NOT NULL,
  `deskripsi` TEXT(512) NULL,
  `bukti_url` VARCHAR(512) NULL,
  `status` ENUM('DIBUKA', 'DIPROSES', 'SELESAI', 'DITARIK') NOT NULL DEFAULT 'DIBUKA',
  `putusan` TEXT(512) NULL,
  `purchase_orders_id_purchase_order` INT NOT NULL,
  `companies_id_company` INT NOT NULL,
  PRIMARY KEY (`id_dispute`),
  INDEX `fk_disputes_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  INDEX `fk_disputes_companies1_idx` (`companies_id_company`),
  CONSTRAINT `fk_disputes_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_disputes_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `inspections` (
  `id_inspection` INT NOT NULL AUTO_INCREMENT,
  `mode_verifikasi` ENUM('DESK', 'LAPANGAN') NOT NULL,
  `alasan_mode` TEXT(255) NULL,
  `jadwal` TIMESTAMP(6) NULL,
  `status` ENUM('DITUGASKAN', 'DIPROSES', 'SELESAI', 'DIBATALKAN') NOT NULL DEFAULT 'DITUGASKAN',
  `purchase_orders_id_purchase_order` INT NOT NULL,
  `users_id_user` INT NOT NULL,
  PRIMARY KEY (`id_inspection`),
  INDEX `fk_inspections_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  INDEX `fk_inspections_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_inspections_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inspections_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `inspection_results` (
  `id_inspection_result` INT NOT NULL AUTO_INCREMENT,
  `parameter_uji` JSON NULL,
  `sumber_bukti` ENUM('FOTO', 'LAB', 'LAPANGAN') NULL,
  `hasil` ENUM('LULUS', 'GAGAL') NULL,
  `lampiran_url` VARCHAR(512) NULL,
  `status` ENUM('DRAF', 'FINAL') NOT NULL DEFAULT 'DRAF',
  `inspections_id_inspection` INT NOT NULL,
  PRIMARY KEY (`id_inspection_result`),
  INDEX `fk_inspection_results_inspections1_idx` (`inspections_id_inspection`),
  UNIQUE INDEX `inspections_id_inspection_UNIQUE` (`inspections_id_inspection`),
  CONSTRAINT `fk_inspection_results_inspections1`
    FOREIGN KEY (`inspections_id_inspection`)
    REFERENCES `inspections` (`id_inspection`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `document_verifications` (
  `id_document_verification` INT NOT NULL AUTO_INCREMENT,
  `verifiable_type` VARCHAR(255) NULL,
  `verifiable_id` INT NULL,
  `checklist` JSON NULL,
  `status` ENUM('MENUNGGU', 'SETUJU', 'DITOLAK', 'DIBATALKAN') NOT NULL DEFAULT 'MENUNGGU',
  `purchase_orders_id_purchase_order` INT NOT NULL,
  `users_id_user` INT NOT NULL,
  `alasan` TEXT(512) NULL,
  PRIMARY KEY (`id_document_verification`),
  INDEX `fk_document_verifications_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  INDEX `fk_document_verifications_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_document_verifications_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_document_verifications_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `compliance_notes` (
  `id_compliance_note` INT NOT NULL AUTO_INCREMENT,
  `jenis` ENUM('TEMUAN', 'PERINGATAN') NOT NULL,
  `deskripsi` TEXT(512) NULL,
  `tenggat` DATE NULL,
  `status_tindak_lanjut` ENUM('TEMUAN', 'PERINGATAN', 'DITINDAKLANJUTI', 'SELESAI') NOT NULL DEFAULT 'TEMUAN',
  `companies_id_company` INT NOT NULL,
  `users_id_user` INT NOT NULL,
  PRIMARY KEY (`id_compliance_note`),
  INDEX `fk_compliance_notes_companies1_idx` (`companies_id_company`),
  INDEX `fk_compliance_notes_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_compliance_notes_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_compliance_notes_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id_review` INT NOT NULL AUTO_INCREMENT,
  `rating` TINYINT NOT NULL,
  `komentar` TEXT(512) NULL,
  `purchase_orders_id_purchase_order` INT NOT NULL,
  `companies_id_company` INT NOT NULL,
  `companies_id_target_company` INT NOT NULL,
  PRIMARY KEY (`id_review`),
  INDEX `fk_reviews_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  INDEX `fk_reviews_companies1_idx` (`companies_id_company`),
  INDEX `fk_reviews_companies2_idx` (`companies_id_target_company`),
  UNIQUE INDEX `purchase_orders_id_purchase_order, companies_id_company` (`purchase_orders_id_purchase_order`, `companies_id_company`),
  CONSTRAINT `fk_reviews_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reviews_companies1`
    FOREIGN KEY (`companies_id_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reviews_companies2`
    FOREIGN KEY (`companies_id_target_company`)
    REFERENCES `companies` (`id_company`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `impact_logs` (
  `id_impact_log` INT NOT NULL AUTO_INCREMENT,
  `tonase_ton` DECIMAL(10,3) NULL,
  `hemat_seller_rp` DECIMAL(12,2) NULL,
  `hemat_buyer_rp` DECIMAL(12,2) NULL,
  `co2e_dihindari_kg` DECIMAL(12,2) NULL,
  `co2e_angkutan_kg` DECIMAL(12,2) NULL,
  `co2e_net_kg` DECIMAL(12,2) NULL,
  `versi_faktor` INT NULL,
  `purchase_orders_id_purchase_order` INT NOT NULL,
  PRIMARY KEY (`id_impact_log`),
  INDEX `fk_impact_logs_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  UNIQUE INDEX `purchase_orders_id_purchase_order_UNIQUE` (`purchase_orders_id_purchase_order`),
  CONSTRAINT `fk_impact_logs_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `watchlists` (
  `id_watchlist` INT NOT NULL AUTO_INCREMENT,
  `kriteria` JSON NULL,
  `aktif` TINYINT NULL,
  `users_id_user` INT NOT NULL,
  PRIMARY KEY (`id_watchlist`),
  INDEX `fk_watchlist_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_watchlist_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id_order_status_history` INT NOT NULL AUTO_INCREMENT,
  `dari` VARCHAR(50) NULL,
  `ke` VARCHAR(50) NULL,
  `alasan` TEXT(512) NULL,
  `waktu` TIMESTAMP(6) NULL,
  `purchase_orders_id_purchase_order` INT NOT NULL,
  `users_id_user` INT NULL,
  PRIMARY KEY (`id_order_status_history`),
  INDEX `fk_order_status_history_purchase_orders1_idx` (`purchase_orders_id_purchase_order`),
  INDEX `fk_order_status_history_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_order_status_history_purchase_orders1`
    FOREIGN KEY (`purchase_orders_id_purchase_order`)
    REFERENCES `purchase_orders` (`id_purchase_order`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_status_history_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id_audit_log` INT NOT NULL AUTO_INCREMENT,
  `waktu` TIMESTAMP(6) NULL,
  `users_id_user` INT NULL,
  `aksi` VARCHAR(100) NOT NULL,
  `auditable_type` VARCHAR(100) NULL,
  `auditable_id` INT NULL,
  `nilai_lama` JSON NULL,
  `nilai_baru` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  PRIMARY KEY (`id_audit_log`),
  INDEX `fk_audit_logs_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_audit_logs_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `shipment_documents` (
  `id_shipment_document` INT NOT NULL AUTO_INCREMENT,
  `jenis` VARCHAR(100) NULL,
  `file_url` VARCHAR(512) NULL,
  `shipments_manifests_id_shipment_manifest` INT NOT NULL,
  PRIMARY KEY (`id_shipment_document`),
  INDEX `fk_shipment_documents_shipments_manifests1_idx` (`shipments_manifests_id_shipment_manifest`),
  CONSTRAINT `fk_shipment_documents_shipments_manifests1`
    FOREIGN KEY (`shipments_manifests_id_shipment_manifest`)
    REFERENCES `shipments_manifests` (`id_shipment_manifest`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id_notification` INT NOT NULL AUTO_INCREMENT,
  `jenis` VARCHAR(255) NULL,
  `payload` JSON NULL,
  `read_at` TIMESTAMP(6) NULL,
  `created_at` TIMESTAMP(6) NULL,
  `users_id_user` INT NOT NULL,
  PRIMARY KEY (`id_notification`),
  INDEX `fk_notifications_users1_idx` (`users_id_user`),
  CONSTRAINT `fk_notifications_users1`
    FOREIGN KEY (`users_id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;