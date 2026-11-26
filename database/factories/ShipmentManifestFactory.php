<?php

namespace Database\Factories;

use App\Enums\ShipmentStatus;
use App\Models\PurchaseOrder;
use App\Models\ShipmentManifest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShipmentManifest>
 */
class ShipmentManifestFactory extends Factory
{
    protected $model = ShipmentManifest::class;

    public function definition(): array
    {
        return [
            'armada' => 'Hino Dutro',
            'plat' => 'D 1234 ABC',
            'sopir' => fake('id_ID')->name(),
            'manifes_url' => null,
            'status' => ShipmentStatus::DRAF,
            'purchase_orders_id_purchase_order' => PurchaseOrder::factory()->dibayar(),
            'berat_dikirim_kg' => '2000.00',
            'jadwal' => now()->addDay(),
            'rute' => 'Gudang Cimahi → Pabrik Cikarang',
            'no_manifes' => null,
        ];
    }

    public function dalamPerjalanan(): static
    {
        return $this->state(fn () => [
            'status' => ShipmentStatus::DALAM_PERJALANAN,
            'no_manifes' => 'MNF-'.now()->format('Ym').'-'.fake()->unique()->numerify('9###'),
        ]);
    }
}
