<?php

namespace Database\Seeders;

use App\Enums\RfqStatus;
use App\Models\Listing;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use App\Models\User;
use Illuminate\Database\Seeder;

class RfqSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('email', 'seller@symbionusa.test')->first();
        $buyer = User::where('email', 'buyer@symbionusa.test')->first();

        if (! $seller || ! $buyer || ! $seller->id_company || ! $buyer->id_company) {
            return;
        }

        $listing = Listing::where('companies_id_company', $seller->id_company)->first();
        if (! $listing) {
            return;
        }

        // 1. RFQ Negosiasi (Putaran 2)
        $rfq1 = RfqContract::create([
            'volume_kg_disepakati' => null,
            'harga_disepakati' => null,
            'tenggat' => now()->addDays(3),
            'status' => RfqStatus::NEGOSIASI,
            'listings_id_listings' => $listing->id_listings,
            'companies_id_company' => $buyer->id_company,
        ]);

        RfqOffer::create([
            'volume_kg' => 2000.00,
            'harga_per_kg' => 11000.00,
            'catatan' => 'Penawaran awal untuk kebutuhan daur ulang rutin.',
            'urutan' => 1,
            'rfq_contracts_id_rfq_contract' => $rfq1->id_rfq_contract,
            'companies_id_company' => $buyer->id_company,
        ]);

        RfqOffer::create([
            'volume_kg' => 2000.00,
            'harga_per_kg' => 12000.00,
            'catatan' => 'Bahan berkualitas tinggi, kami tawarkan penyesuaian di Rp 12.000/kg.',
            'urutan' => 2,
            'rfq_contracts_id_rfq_contract' => $rfq1->id_rfq_contract,
            'companies_id_company' => $seller->id_company,
        ]);

        // 2. RFQ Disepakati (Siap ke PO)
        $rfq2 = RfqContract::create([
            'volume_kg_disepakati' => 1500.00,
            'harga_disepakati' => 11500.00,
            'tenggat' => now()->addDays(2),
            'status' => RfqStatus::DISEPAKATI,
            'listings_id_listings' => $listing->id_listings,
            'companies_id_company' => $buyer->id_company,
        ]);

        RfqOffer::create([
            'volume_kg' => 1500.00,
            'harga_per_kg' => 11500.00,
            'catatan' => 'Disetujui bersama sesuai spesifikasi.',
            'urutan' => 1,
            'rfq_contracts_id_rfq_contract' => $rfq2->id_rfq_contract,
            'companies_id_company' => $buyer->id_company,
        ]);
    }
}
