<?php

namespace Database\Factories;

use App\Enums\DemandRequestFrequency;
use App\Enums\DemandRequestStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\DemandRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemandRequest>
 */
class DemandRequestFactory extends Factory
{
    protected $model = DemandRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'spesifikasi_min' => [
                'Kadar Air' => '12%',
                'Ukuran Partikel' => '5 cm',
                'Tingkat Kebersihan' => 'Bersih',
            ],
            'volume_kg' => fake()->randomFloat(2, 500, 50000),
            'harga_maks' => fake()->randomFloat(2, 2000, 25000),
            'berlaku_sampai' => now()->addDays(30)->toDateString(),
            'status' => DemandRequestStatus::TERBUKA,
            'companies_id_company' => Company::factory(),
            'categories_id_categories' => Category::factory(),
            'frekuensi' => DemandRequestFrequency::SEKALI,
        ];
    }

    /**
     * Kebutuhan berstatus DRAF.
     */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => DemandRequestStatus::DRAF,
        ]);
    }

    /**
     * Kebutuhan berstatus TERPENUHI.
     */
    public function fulfilled(): static
    {
        return $this->state(fn () => [
            'status' => DemandRequestStatus::TERPENUHI,
        ]);
    }

    /**
     * Kebutuhan berstatus KEDALUWARSA.
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => DemandRequestStatus::KEDALUWARSA,
            'berlaku_sampai' => now()->subDay()->toDateString(),
        ]);
    }
}
