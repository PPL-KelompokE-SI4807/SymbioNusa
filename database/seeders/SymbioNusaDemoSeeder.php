<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\DemandRequest;
use App\Models\Listing;
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

        // 3. Users for 4 roles
        User::firstOrCreate(
            ['email' => 'admin@symbionusa.test'],
            [
                'nama' => 'Admin DLH Pusat',
                'role' => 'ADMIN',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        User::firstOrCreate(
            ['email' => 'seller@symbionusa.test'],
            [
                'nama' => 'Budi Seller',
                'role' => 'SELLER',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
                'id_company' => $sellerCompany->id_company,
            ]
        );

        User::firstOrCreate(
            ['email' => 'buyer@symbionusa.test'],
            [
                'nama' => 'Siti Buyer',
                'role' => 'BUYER',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
                'id_company' => $buyerCompany->id_company,
            ]
        );

        User::firstOrCreate(
            ['email' => 'inspector@symbionusa.test'],
            [
                'nama' => 'Ir. Joko Inspector',
                'role' => 'INSPECTOR',
                'password_hash' => Hash::make('password'),
                'status' => 'AKTIF',
            ]
        );

        // 4. Sample Listings & Demand Requests & POs
        $listing = Listing::firstOrCreate(
            ['judul' => 'Sisa Potongan Plastik PP Murni'],
            [
                'spesifikasi' => json_encode(['warna' => 'bening', 'bentuk' => 'flakes']),
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
                'spesifikasi_min' => json_encode(['kadar_air' => '<5%']),
                'volume_kg' => 3000.00,
                'harga_maks' => 13000.00,
                'berlaku_sampai' => now()->addDays(30),
                'status' => 'TERBUKA',
                'frekuensi' => 'MINGGUAN',
            ]
        );
    }
}
