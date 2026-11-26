<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        return [
            'judul' => fake()->words(3, true).' Non-B3',
            'spesifikasi' => [
                'Kadar Air' => 10,
                'Ukuran Partikel' => '2-5 cm',
                'Tingkat Kebersihan' => 'Bersih',
            ],
            'volume_kg' => 5000.00,
            'stok_kg' => 5000.00,
            'harga_per_kg' => 3500.00,
            'biaya_buang_per_kg' => 500.00,
            'metode_buang_sebelumnya' => 'TPA Sukamaju',
            'lokasi' => 'Bandung, Jawa Barat',
            'status' => 'PUBLISHED',
            'companies_id_company' => Company::factory()->seller(),
            'categories_id_categories' => Category::factory(),
        ];
    }

    public function draf(): static
    {
        return $this->state(fn () => ['status' => 'DRAF']);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'DIARSIPKAN']);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stok_kg' => 0.00]);
    }
}
