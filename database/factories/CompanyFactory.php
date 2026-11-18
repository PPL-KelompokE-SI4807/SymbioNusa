<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Perusahaan sesuai tabel `companies` di DDL. NIB dibuat unik (kolom UNIQUE).
 *
 * Contoh: Company::factory()->seller()->create(); Company::factory()->buyer()->pending()->create();
 *
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'PT '.fake('id_ID')->company(),
            'tipe' => 'KEDUANYA',
            'nib' => (string) fake()->unique()->numerify('99#########'),
            'alamat' => fake('id_ID')->address(),
            'kontak' => fake('id_ID')->numerify('08##########'),
            'status_verifikasi' => 'TERVERIFIKASI',
        ];
    }

    public function seller(): static
    {
        return $this->state(fn () => ['tipe' => 'SELLER']);
    }

    public function buyer(): static
    {
        return $this->state(fn () => ['tipe' => 'BUYER']);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status_verifikasi' => 'PENDING']);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => ['status_verifikasi' => 'DITOLAK']);
    }
}
