<?php

namespace Database\Factories;

use App\Models\Inspection;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inspection>
 */
class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition(): array
    {
        return [
            'mode_verifikasi' => Inspection::MODE_DESK,
            'alasan_mode' => null,
            'jadwal' => now()->addDays(2)->setHour(10)->setMinute(0),
            'status' => Inspection::STATUS_DITUGASKAN,
            'purchase_orders_id_purchase_order' => PurchaseOrder::factory(),
            'users_id_user' => User::factory()->inspector(),
        ];
    }

    public function desk(): static
    {
        return $this->state(fn () => [
            'mode_verifikasi' => Inspection::MODE_DESK,
            'alasan_mode' => null,
        ]);
    }

    public function lapangan(): static
    {
        return $this->state(fn () => [
            'mode_verifikasi' => Inspection::MODE_LAPANGAN,
            'alasan_mode' => 'Pemeriksaan fisik lapangan untuk komoditas risiko tinggi.',
        ]);
    }

    public function diproses(): static
    {
        return $this->state(fn () => [
            'status' => Inspection::STATUS_DIPROSES,
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => [
            'status' => Inspection::STATUS_SELESAI,
        ]);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn () => [
            'status' => Inspection::STATUS_DIBATALKAN,
        ]);
    }
}
