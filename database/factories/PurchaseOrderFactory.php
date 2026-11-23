<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
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
            'volume_kg' => '2000.00',
            'harga_per_kg' => '3500.00',
            'toleransi_persen' => '5.00',
            'kadar_air_maks_persen' => '14.00',
            'toleransi_kadar_air_poin' => '2.00',
            'jarak_km' => '45.00',
            'status' => OrderStatus::DRAFT,
            'rfq_contracts_id_rfq_contract' => RfqContract::factory()->disepakati(),
            'companies_id_seller' => Company::factory()->seller(),
            'companies_id_buyer' => Company::factory()->buyer(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::DRAFT,
        ]);
    }

    public function menungguKonfirmasi(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::MENUNGGU_KONFIRMASI,
        ]);
    }

    public function dikonfirmasi(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::DIKONFIRMASI,
        ]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::DITOLAK,
        ]);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::DIBATALKAN,
        ]);
    }

    public function invoiced(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::INVOICED,
        ]);
    }

    public function dibayar(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::DIBAYAR,
        ]);
    }
}
