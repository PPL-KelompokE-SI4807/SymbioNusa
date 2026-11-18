<?php

namespace Database\Factories;

use App\Enums\RfqStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\RfqContract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfqContract>
 */
class RfqContractFactory extends Factory
{
    protected $model = RfqContract::class;

    public function definition(): array
    {
        return [
            'volume_kg_disepakati' => null,
            'harga_disepakati' => null,
            'tenggat' => now()->addDays(3),
            'status' => RfqStatus::TERBUKA,
            'listings_id_listings' => Listing::factory(),
            'companies_id_company' => Company::factory()->buyer(),
            'demand_requests_id_demand_request' => null,
        ];
    }

    public function terbuka(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::TERBUKA,
        ]);
    }

    public function negosiasi(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::NEGOSIASI,
        ]);
    }

    public function disepakati(?float $volume = 2000.0, ?float $harga = 3500.0): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::DISEPAKATI,
            'volume_kg_disepakati' => $volume,
            'harga_disepakati' => $harga,
        ]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::DITOLAK,
        ]);
    }

    public function kedaluwarsa(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::KEDALUWARSA,
            'tenggat' => now()->subDay(),
        ]);
    }

    public function ditarik(): static
    {
        return $this->state(fn () => [
            'status' => RfqStatus::DITARIK,
        ]);
    }
}
