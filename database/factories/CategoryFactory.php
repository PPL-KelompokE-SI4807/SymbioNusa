<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(2, true).' Non-B3',
            'spec_schema' => [
                [
                    'nama' => 'Kadar Air',
                    'tipe' => 'number',
                    'satuan' => '%',
                    'wajib' => true,
                ],
                [
                    'nama' => 'Ukuran Partikel',
                    'tipe' => 'text',
                    'satuan' => 'cm',
                    'wajib' => false,
                ],
                [
                    'nama' => 'Tingkat Kebersihan',
                    'tipe' => 'select',
                    'pilihan' => ['Bersih', 'Cukup Bersih', 'Kotor'],
                    'wajib' => true,
                ],
            ],
            'is_b3_prohibited' => 0,
            'toleransi_susut_persen' => 2.50,
            'kadar_air_maks_persen' => 5.00,
            'toleransi_kadar_air_poin' => 1.00,
            'tingkat_risiko' => 'RENDAH',
            'wajib_sertifikat_lab' => 0,
            'categories_id_categories' => null,
            'status' => 'AKTIF',
        ];
    }

    /**
     * State untuk kategori B3 yang dilarang.
     */
    public function b3(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_b3_prohibited' => 1,
            'tingkat_risiko' => 'TINGGI',
            'wajib_sertifikat_lab' => 1,
        ]);
    }

    /**
     * State untuk kategori yang diarsipkan.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ARSIP',
        ]);
    }
}
