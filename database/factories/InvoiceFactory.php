<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'nomor' => 'INV-'.fake()->unique()->numerify('2026-####'),
            'total' => 15000000.00,
            'jatuh_tempo' => now()->addDays(3)->toDateString(),
            'status' => 'TERBIT',
            'purchase_orders_id_purchase_order' => PurchaseOrder::factory(),
        ];
    }

    public function terbit(): static
    {
        return $this->state(fn () => ['status' => 'TERBIT']);
    }

    public function dibayar(): static
    {
        return $this->state(fn () => ['status' => 'DIBAYAR']);
    }

    public function void(): static
    {
        return $this->state(fn () => ['status' => 'VOID']);
    }
}
