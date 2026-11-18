<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\RfqContract;
use App\Models\RfqOffer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RfqOffer>
 */
class RfqOfferFactory extends Factory
{
    protected $model = RfqOffer::class;

    public function definition(): array
    {
        return [
            'volume_kg' => 1000.00,
            'harga_per_kg' => 3000.00,
            'catatan' => 'Penawaran harga dan volume material',
            'urutan' => 1,
            'rfq_contracts_id_rfq_contract' => RfqContract::factory(),
            'companies_id_company' => Company::factory()->buyer(),
        ];
    }

    public function round(int $urutan, ?Company $company = null): static
    {
        return $this->state(fn () => [
            'urutan' => $urutan,
            'companies_id_company' => $company?->id_company ?? Company::factory(),
        ]);
    }
}
