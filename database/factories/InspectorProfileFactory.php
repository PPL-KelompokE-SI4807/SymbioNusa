<?php

namespace Database\Factories;

use App\Models\InspectorProfile;
use App\Models\User;
use App\Support\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Profil Inspector sesuai tabel `inspector_profiles` di DDL (PBI-01 / SYM-5).
 *
 * Contoh:
 *   InspectorProfile::factory()->create();                         // aktif, berlaku ±1 tahun
 *   InspectorProfile::factory()->expiringSoon()->create();         // berakhir ≤ 30 hari
 *   InspectorProfile::factory()->expired()->create();              // sertifikat sudah lewat
 *   InspectorProfile::factory()->withExpertise([$cat->id_categories])->create();
 *
 * @extends Factory<InspectorProfile>
 */
class InspectorProfileFactory extends Factory
{
    protected $model = InspectorProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bidang_keahlian' => [],
            'wilayah_tugas' => fake()->randomElement(Wilayah::PROVINSI),
            'no_sertifikat' => 'INS-'.fake()->unique()->numerify('####-####'),
            'sertifikat_url' => null,
            'berlaku_sampai' => InspectorProfile::today()->addYear()->toDateString(),
            'status_aktif' => 1,
            'id_user' => User::factory()->inspector(),
        ];
    }

    /**
     * @param  list<int>  $categoryIds
     */
    public function withExpertise(array $categoryIds): static
    {
        return $this->state(fn () => ['bidang_keahlian' => array_values($categoryIds)]);
    }

    public function expiringSoon(int $days = 10): static
    {
        return $this->state(fn () => ['berlaku_sampai' => InspectorProfile::today()->addDays($days)->toDateString()]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['berlaku_sampai' => InspectorProfile::today()->subDay()->toDateString()]);
    }

    public function deactivated(): static
    {
        return $this->state(fn () => ['status_aktif' => 0]);
    }
}
