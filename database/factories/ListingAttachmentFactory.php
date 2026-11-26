<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingAttachment>
 */
class ListingAttachmentFactory extends Factory
{
    protected $model = ListingAttachment::class;

    public function definition(): array
    {
        return [
            'jenis' => fake()->randomElement(['SURAT_NON_B3', 'FOTO_SAMPEL', 'SERTIFIKAT_LAB']),
            'file_url' => 'listings/dummy_'.fake()->uuid().'.pdf',
            'tanggal_pengambilan' => fake()->date(),
            'listings_id_listings' => Listing::factory(),
            'status' => 'AKTIF',
        ];
    }

    public function suratNonB3(): static
    {
        return $this->state(fn () => [
            'jenis' => 'SURAT_NON_B3',
            'file_url' => 'listings/surat_non_b3_'.fake()->uuid().'.pdf',
        ]);
    }

    public function fotoSampel(): static
    {
        return $this->state(fn () => [
            'jenis' => 'FOTO_SAMPEL',
            'file_url' => 'listings/foto_sampel_'.fake()->uuid().'.jpg',
            'tanggal_pengambilan' => now()->toDateString(),
        ]);
    }

    public function sertifikatLab(): static
    {
        return $this->state(fn () => [
            'jenis' => 'SERTIFIKAT_LAB',
            'file_url' => 'listings/sertifikat_lab_'.fake()->uuid().'.pdf',
        ]);
    }
}
