<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    protected $model = OrderStatusHistory::class;

    public function definition(): array
    {
        return [
            'dari' => null,
            'ke' => OrderStatus::DRAFT->value,
            'alasan' => 'PO dibuat',
            'waktu' => now(),
            'purchase_orders_id_purchase_order' => PurchaseOrder::factory(),
            'users_id_user' => User::factory(),
        ];
    }
}
