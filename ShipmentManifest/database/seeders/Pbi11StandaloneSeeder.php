<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Company;
use App\Models\DocumentVerification;
use App\Models\PurchaseOrder;
use App\Models\ShipmentManifest;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Data contoh untuk demonstrasi PBI-11 secara mandiri. Semua akun berkata sandi: password */
class Pbi11StandaloneSeeder extends Seeder
{
    public function run(): void
    {
        $tahu = Company::create([
            'nama' => 'CV Tahu Makmur', 'tipe' => 'SELLER', 'nib' => '1234567890001',
            'alamat' => 'Jl. Raya Cibiru No. 12, Bandung', 'latitude' => -6.9175, 'longitude' => 107.7200, 'kontak' => '022-7000001',
        ]);
        $pakan = Company::create([
            'nama' => 'PT Pakan Nusantara', 'tipe' => 'BUYER', 'nib' => '1234567890002',
            'alamat' => 'Kawasan Industri Jababeka, Cikarang', 'latitude' => -6.2950, 'longitude' => 107.1400, 'kontak' => '021-8900002',
        ]);
        $konveksi = Company::create([
            'nama' => 'Konveksi Sinar Jaya', 'tipe' => 'SELLER', 'nib' => '1234567890003',
            'alamat' => 'Jl. Soekarno-Hatta No. 88, Bandung', 'latitude' => -6.9400, 'longitude' => 107.6500, 'kontak' => '022-7000003',
        ]);

        $seller1 = $this->user('Sari (Seller - Tahu Makmur)', 'seller1@symbionusa.test', User::ROLE_SELLER, $tahu->id);
        $this->user('Budi (Seller - Sinar Jaya)', 'seller2@symbionusa.test', User::ROLE_SELLER, $konveksi->id);
        $this->user('Rina (Buyer - Pakan Nusantara)', 'buyer@symbionusa.test', User::ROLE_BUYER, $pakan->id);
        $inspector = $this->user('Dedi (Inspector)', 'inspector@symbionusa.test', User::ROLE_INSPECTOR, null);
        $this->user('Admin DLH', 'admin@symbionusa.test', User::ROLE_ADMIN, null);

        // 1) Siap kirim (dibayar + dokumen terverifikasi), tahu 5.000 kg, toleransi 5%
        $po1 = $this->order('PO-2026-0001', $tahu, $pakan, 'Ampas Tahu', 5000, 600, 5, OrderStatus::Dibayar);
        $this->verification($po1, $inspector, DocumentVerification::DISETUJUI);

        // 2) Dibayar tetapi verifikasi dokumen belum selesai -> belum boleh dikirim (contoh guard)
        $po2 = $this->order('PO-2026-0002', $tahu, $pakan, 'Serbuk Kayu', 3000, 450, 3, OrderStatus::Dibayar);
        $this->verification($po2, $inspector, DocumentVerification::MENUNGGU);

        // 3) Siap kirim, Seller lain
        $po3 = $this->order('PO-2026-0003', $konveksi, $pakan, 'Kain Perca', 2000, 1200, 2, OrderStatus::Dibayar);
        $this->verification($po3, $inspector, DocumentVerification::DISETUJUI);

        // 4) Sudah punya draf pengiriman
        $po4 = $this->order('PO-2026-0004', $tahu, $pakan, 'Roti Sisa', 1500, 700, 5, OrderStatus::Dibayar);
        $this->verification($po4, $inspector, DocumentVerification::DISETUJUI);
        $this->shipment($po4, $seller1, ShipmentStatus::Draf, 1480, 'Hino Dutro', 'D 8123 ABC', 'Ujang', null);

        // 5) Dalam pengiriman
        $po5 = $this->order('PO-2026-0005', $tahu, $pakan, 'Ampas Kopi', 1000, 500, 5, OrderStatus::DalamPengiriman);
        $this->verification($po5, $inspector, DocumentVerification::DISETUJUI);
        $this->shipment($po5, $seller1, ShipmentStatus::DalamPengiriman, 990, 'Mitsubishi L300', 'D 1456 XY', 'Asep', 'MNF-202611-0001');

        // 6) Tiba (menunggu konfirmasi penerimaan Buyer - PBI-12)
        $po6 = $this->order('PO-2026-0006', $konveksi, $pakan, 'Cacahan Plastik', 4000, 2500, 2, OrderStatus::DalamPengiriman);
        $this->verification($po6, $inspector, DocumentVerification::DISETUJUI);
        $this->shipment($po6, null, ShipmentStatus::Tiba, 4020, 'Fuso Fighter', 'B 9021 KQ', 'Hendra', 'MNF-202611-0002', $konveksi);
    }

    private function user(string $name, string $email, string $role, ?int $companyId): User
    {
        return User::create([
            'name' => $name, 'email' => $email, 'password' => 'password',
            'role' => $role, 'company_id' => $companyId,
        ]);
    }

    private function order(string $no, Company $seller, Company $buyer, string $material, float $kg, float $price, float $tol, OrderStatus $status): PurchaseOrder
    {
        return PurchaseOrder::create([
            'nomor_po' => $no, 'seller_company_id' => $seller->id, 'buyer_company_id' => $buyer->id,
            'material' => $material, 'volume_kg' => $kg, 'harga_per_kg' => $price,
            'toleransi_tonase_persen' => $tol, 'status' => $status,
        ]);
    }

    private function verification(PurchaseOrder $po, User $inspector, string $status): void
    {
        DocumentVerification::create([
            'purchase_order_id' => $po->id, 'user_id' => $inspector->id, 'status' => $status,
            'verified_at' => $status === DocumentVerification::DISETUJUI ? now()->subDays(2) : null,
        ]);
    }

    private function shipment(PurchaseOrder $po, ?User $creator, ShipmentStatus $status, float $kg, string $armada, string $plat, string $sopir, ?string $noManifes, ?Company $seller = null): void
    {
        $origin = ($seller ?? $po->seller)->alamat;

        ShipmentManifest::create([
            'purchase_order_id' => $po->id,
            'no_manifes' => $noManifes,
            'armada' => $armada, 'plat' => $plat, 'sopir' => $sopir, 'kontak_sopir' => '081234567890',
            'jadwal_berangkat' => now()->subDay(), 'estimasi_tiba' => now()->addDay(),
            'rute_asal' => $origin ?? '-', 'rute_tujuan' => $po->buyer->alamat ?? '-',
            'jarak_km' => 95.5, 'berat_dikirim_kg' => $kg,
            'status' => $status,
            'berangkat_at' => $status === ShipmentStatus::Draf ? null : now()->subDay(),
            'tiba_at' => $status === ShipmentStatus::Tiba ? now()->subHours(3) : null,
            'created_by' => $creator?->id,
        ]);
    }
}
