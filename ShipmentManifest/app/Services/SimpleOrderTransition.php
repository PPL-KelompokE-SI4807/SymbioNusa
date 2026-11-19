<?php

namespace App\Services;

use App\Contracts\OrderTransition;
use App\Enums\OrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Implementasi sederhana untuk mode mandiri. Ganti dengan OrderStateMachine saat integrasi. */
class SimpleOrderTransition implements OrderTransition
{
    public function markInTransit(PurchaseOrder $order, ?User $actor, string $reason): void
    {
        $from = $order->status instanceof OrderStatus ? $order->status->value : (string) $order->status;

        $order->update(['status' => OrderStatus::DalamPengiriman]);

        DB::table('order_status_history')->insert([
            'purchase_order_id' => $order->id,
            'user_id' => $actor?->id,
            'dari' => $from,
            'ke' => OrderStatus::DalamPengiriman->value,
            'alasan' => $reason,
            'created_at' => now(),
        ]);
    }
}
