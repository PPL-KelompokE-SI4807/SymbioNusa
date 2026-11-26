<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\RfqStatus;
use App\Models\Listing;
use App\Models\OrderStatusHistory;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('email', 'seller@symbionusa.test')->first();
        $buyer = User::where('email', 'buyer@symbionusa.test')->first();

        if (! $seller || ! $buyer || ! $seller->id_company || ! $buyer->id_company) {
            return;
        }

        $listing = Listing::with('category')->where('companies_id_company', $seller->id_company)->first();
        if (! $listing) {
            return;
        }

        $cat = $listing->category;
        $tolPersen = $cat?->toleransi_susut_persen ?? 5.0;
        $kadarAir = $cat?->kadar_air_maks_persen;
        $tolKadarAir = $cat?->toleransi_kadar_air_poin;

        // 1. RFQ yang sudah dibuatkan PO Menunggu Konfirmasi
        $rfqPo1 = RfqContract::create([
            'volume_kg_disepakati' => 1000.00,
            'harga_disepakati' => 12000.00,
            'tenggat' => now()->addDays(5),
            'status' => RfqStatus::DISEPAKATI,
            'listings_id_listings' => $listing->id_listings,
            'companies_id_company' => $buyer->id_company,
        ]);

        RfqOffer::create([
            'volume_kg' => 1000.00,
            'harga_per_kg' => 12000.00,
            'catatan' => 'Sepakat volume 1.000 kg',
            'urutan' => 1,
            'rfq_contracts_id_rfq_contract' => $rfqPo1->id_rfq_contract,
            'companies_id_company' => $seller->id_company,
        ]);

        $po1 = PurchaseOrder::create([
            'volume_kg' => 1000.00,
            'harga_per_kg' => 12000.00,
            'toleransi_persen' => $tolPersen,
            'kadar_air_maks_persen' => $kadarAir,
            'toleransi_kadar_air_poin' => $tolKadarAir,
            'jarak_km' => 35.50,
            'status' => OrderStatus::MENUNGGU_KONFIRMASI,
            'rfq_contracts_id_rfq_contract' => $rfqPo1->id_rfq_contract,
            'companies_id_seller' => $seller->id_company,
            'companies_id_buyer' => $buyer->id_company,
        ]);

        OrderStatusHistory::create([
            'dari' => null,
            'ke' => OrderStatus::DRAFT->value,
            'alasan' => 'PO dibuat oleh Buyer',
            'waktu' => now()->subHours(4),
            'purchase_orders_id_purchase_order' => $po1->id_purchase_order,
            'users_id_user' => $buyer->id_user,
        ]);

        OrderStatusHistory::create([
            'dari' => OrderStatus::DRAFT->value,
            'ke' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'alasan' => 'PO dikirim ke Seller untuk konfirmasi',
            'waktu' => now()->subHours(2),
            'purchase_orders_id_purchase_order' => $po1->id_purchase_order,
            'users_id_user' => $buyer->id_user,
        ]);

        // 2. RFQ yang sudah dibuatkan PO Dikonfirmasi
        $rfqPo2 = RfqContract::create([
            'volume_kg_disepakati' => 800.00,
            'harga_disepakati' => 11800.00,
            'tenggat' => now()->addDays(4),
            'status' => RfqStatus::DISEPAKATI,
            'listings_id_listings' => $listing->id_listings,
            'companies_id_company' => $buyer->id_company,
        ]);

        $po2 = PurchaseOrder::create([
            'volume_kg' => 800.00,
            'harga_per_kg' => 11800.00,
            'toleransi_persen' => $tolPersen,
            'kadar_air_maks_persen' => $kadarAir,
            'toleransi_kadar_air_poin' => $tolKadarAir,
            'jarak_km' => 20.00,
            'status' => OrderStatus::DIKONFIRMASI,
            'rfq_contracts_id_rfq_contract' => $rfqPo2->id_rfq_contract,
            'companies_id_seller' => $seller->id_company,
            'companies_id_buyer' => $buyer->id_company,
        ]);

        OrderStatusHistory::create([
            'dari' => null,
            'ke' => OrderStatus::DRAFT->value,
            'alasan' => 'PO dibuat oleh Buyer',
            'waktu' => now()->subDays(1),
            'purchase_orders_id_purchase_order' => $po2->id_purchase_order,
            'users_id_user' => $buyer->id_user,
        ]);

        OrderStatusHistory::create([
            'dari' => OrderStatus::DRAFT->value,
            'ke' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'alasan' => 'PO dikirim ke Seller untuk konfirmasi',
            'waktu' => now()->subHours(12),
            'purchase_orders_id_purchase_order' => $po2->id_purchase_order,
            'users_id_user' => $buyer->id_user,
        ]);

        OrderStatusHistory::create([
            'dari' => OrderStatus::MENUNGGU_KONFIRMASI->value,
            'ke' => OrderStatus::DIKONFIRMASI->value,
            'alasan' => 'PO dikonfirmasi oleh Seller. Stok 800 kg berhasil direservasi.',
            'waktu' => now()->subHours(6),
            'purchase_orders_id_purchase_order' => $po2->id_purchase_order,
            'users_id_user' => $seller->id_user,
        ]);
    }
}
