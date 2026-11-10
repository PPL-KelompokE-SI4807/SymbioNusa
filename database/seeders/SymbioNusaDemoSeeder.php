<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Dispute;
use App\Models\ImpactLog;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SymbioNusaDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $cat1 = Category::firstOrCreate(
            ['nama' => 'Plastik PP/PE Non-B3'],
            [
                'is_b3_prohibited' => 0,
                'toleransi_susut_persen' => 2.50,
                'kadar_air_maks_persen' => 5.00,
                'toleransi_kadar_air_poin' => 1.00,
                'tingkat_risiko' => 'RENDAH',
                'wajib_sertifikat_lab' => 0,
                'status' => 'AKTIF',
            ]
        );

        $cat2 = Category::firstOrCreate(
            ['nama' => 'Logam Aluminium Scrap'],
            [
                'is_b3_prohibited' => 0,
                'toleransi_susut_persen' => 1.00,
                'kadar_air_maks_persen' => 2.00,
                'toleransi_kadar_air_poin' => 0.50,
                'tingkat_risiko' => 'RENDAH',
                'wajib_sertifikat_lab' => 0,
                'status' => 'AKTIF',
            ]
        );

        // 2. Companies
        $sellerCompany = Company::firstOrCreate(
            ['nib' => '1234567890123'],
            [
                'nama' => 'PT Plastik Makmur Sejahtera',
                'tipe' => 'SELLER',
                'alamat' => 'Jl. Industri No. 10, Cikarang',
                'kontak' => '0215551234',
                'status_verifikasi' => 'TERVERIFIKASI',
            ]
        );

        $buyerCompany = Company::firstOrCreate(
            ['nib' => '9876543210123'],
            [
                'nama' => 'PT Daur Ulang Nusantara',
                'tipe' => 'BUYER',
                'alamat' => 'Jl. Daendels No. 45, Karawang',
                'kontak' => '0267555888',
                'status_verifikasi' => 'TERVERIFIKASI',
            ]
        );

        $pendingCompany = Company::firstOrCreate(
            ['nib' => '5555666677778'],
            [
                'nama' => 'CV Sumber Kertas Lestari',
                'tipe' => 'SELLER',
                'alamat' => 'Kawasan Industri Rungkut, Surabaya',
                'kontak' => '0318889999',
                'status_verifikasi' => 'PENDING',
            ]
        );

        // 3. Users for 4 roles
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@symbionusa.test'],
            [
                'nama' => 'Admin DLH Pusat',
                'role' => 'ADMIN',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@symbionusa.test'],
            [
                'nama' => 'Budi Seller',
                'role' => 'SELLER',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
                'id_company' => $sellerCompany->id_company,
            ]
        );

        $buyerUser = User::firstOrCreate(
            ['email' => 'buyer@symbionusa.test'],
            [
                'nama' => 'Siti Buyer',
                'role' => 'BUYER',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
                'id_company' => $buyerCompany->id_company,
            ]
        );

        $inspectorUser = User::firstOrCreate(
            ['email' => 'inspector@symbionusa.test'],
            [
                'nama' => 'Ir. Joko Inspector',
                'role' => 'INSPECTOR',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        // 4. Sample Listings & Demand Requests
        $listing = Listing::firstOrCreate(
            ['judul' => 'Sisa Potongan Plastik PP Murni'],
            [
                'spesifikasi' => ['warna' => 'bening', 'bentuk' => 'flakes'],
                'volume_kg' => 5000.00,
                'stok_kg' => 5000.00,
                'harga_per_kg' => 12500.00,
                'biaya_buang_per_kg' => 500.00,
                'metode_buang_sebelumnya' => 'Landfill',
                'lokasi' => 'Cikarang',
                'status' => 'PUBLISHED',
                'companies_id_company' => $sellerCompany->id_company,
                'categories_id_categories' => $cat1->id_categories,
            ]
        );

        DemandRequest::firstOrCreate(
            ['companies_id_company' => $buyerCompany->id_company, 'categories_id_categories' => $cat1->id_categories],
            [
                'spesifikasi_min' => ['kadar_air' => '<5%'],
                'volume_kg' => 3000.00,
                'harga_maks' => 13000.00,
                'berlaku_sampai' => now()->addDays(30),
                'status' => 'TERBUKA',
                'frekuensi' => 'MINGGUAN',
            ]
        );

        // 5. Sample Contract & Purchase Order
        $contract = RfqContract::firstOrCreate(
            ['listings_id_listings' => $listing->id_listings, 'companies_id_company' => $buyerCompany->id_company],
            [
                'volume_kg_disepakati' => 2000.00,
                'harga_disepakati' => 12000.00,
                'tenggat' => now()->addDays(14),
                'status' => 'DISEPAKATI',
            ]
        );

        $po = PurchaseOrder::firstOrCreate(
            ['rfq_contracts_id_rfq_contract' => $contract->id_rfq_contract],
            [
                'volume_kg' => 2000.00,
                'harga_per_kg' => 12000.00,
                'toleransi_persen' => 2.50,
                'kadar_air_maks_persen' => 5.00,
                'toleransi_kadar_air_poin' => 1.00,
                'jarak_km' => 35.50,
                'status' => 'DIKONFIRMASI',
                'companies_id_seller' => $sellerCompany->id_company,
                'companies_id_buyer' => $buyerCompany->id_company,
            ]
        );

        // 6. Sample Invoice
        Invoice::firstOrCreate(
            ['purchase_orders_id_purchase_order' => $po->id_purchase_order],
            [
                'nomor' => 'INV-2026-0001',
                'total' => 24000000.00,
                'jatuh_tempo' => now()->addDays(7),
                'status' => 'TERBIT',
            ]
        );

        // 7. Sample Inspection
        Inspection::firstOrCreate(
            ['purchase_orders_id_purchase_order' => $po->id_purchase_order],
            [
                'mode_verifikasi' => 'LAPANGAN',
                'alasan_mode' => 'Pemeriksaan rutin volume awal di atas 1 ton',
                'jadwal' => now()->addDays(2),
                'status' => 'DITUGASKAN',
                'users_id_user' => $inspectorUser->id_user,
            ]
        );

        // 8. Sample Dispute
        Dispute::firstOrCreate(
            ['purchase_orders_id_purchase_order' => $po->id_purchase_order],
            [
                'jenis' => 'SELISIH_TONASE',
                'deskripsi' => 'Perbedaan berat timbang awal saat pengambilan sampel',
                'status' => 'DIBUKA',
                'companies_id_company' => $buyerCompany->id_company,
            ]
        );

        // 9. Sample Impact Log
        ImpactLog::firstOrCreate(
            ['purchase_orders_id_purchase_order' => $po->id_purchase_order],
            [
                'tonase_ton' => 2.0000,
                'hemat_seller_rp' => 1000000.00,
                'hemat_buyer_rp' => 2000000.00,
                'co2e_dihindari_kg' => 450.5000,
                'co2e_angkutan_kg' => 30.5000,
                'co2e_net_kg' => 420.0000,
                'versi_faktor' => 1,
            ]
        );
    }
}
