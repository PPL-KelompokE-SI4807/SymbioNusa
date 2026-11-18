<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\RfqContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'volume_kg' => 1000.00,
            'harga_per_kg' => 15000.00,
            'toleransi_persen' => 5.00,
            'kadar_air_maks_persen' => 12.00,
            'toleransi_kadar_air_poin' => 2.00,
            'jarak_km' => 25.50,
            'status' => 'INVOICED',
            'rfq_contracts_id_rfq_contract' => RfqContract::factory(),
            'companies_id_seller' => Company::factory()->seller(),
            'companies_id_buyer' => Company::factory()->buyer(),
        ];
    }

    public function invoiced(): static
    {
        return $this->state(fn () => ['status' => 'INVOICED']);
    }

    public function dibayar(): static
    {
        return $this->state(fn () => ['status' => 'DIBAYAR']);
    }
}
