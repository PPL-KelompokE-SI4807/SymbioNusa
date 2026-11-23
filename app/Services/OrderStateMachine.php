<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\PurchaseOrder;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class OrderStateMachine
{
    /**
     * Peta transisi status order yang diizinkan sesuai alur bisnis SymbioNusa.
     *
     * @var array<string, list<string>>
     */
    private const TRANSITIONS = [
        OrderStatus::DRAFT->value => [
            OrderStatus::MENUNGGU_KONFIRMASI->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::MENUNGGU_KONFIRMASI->value => [
            OrderStatus::DIKONFIRMASI->value,
            OrderStatus::DITOLAK->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::DIKONFIRMASI->value => [
            OrderStatus::INSPEKSI->value,
            OrderStatus::LOLOS_INSPEKSI->value,
            OrderStatus::INVOICED->value,
            OrderStatus::SENGKETA->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::INSPEKSI->value => [
            OrderStatus::LOLOS_INSPEKSI->value,
            OrderStatus::GAGAL_INSPEKSI->value,
            OrderStatus::SENGKETA->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::LOLOS_INSPEKSI->value => [
            OrderStatus::INVOICED->value,
            OrderStatus::SENGKETA->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::GAGAL_INSPEKSI->value => [
            OrderStatus::SENGKETA->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::INVOICED->value => [
            OrderStatus::DIBAYAR->value,
            OrderStatus::SENGKETA->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::DIBAYAR->value => [
            OrderStatus::DALAM_PENGIRIMAN->value,
            OrderStatus::SENGKETA->value,
        ],
        OrderStatus::DALAM_PENGIRIMAN->value => [
            OrderStatus::DITERIMA->value,
            OrderStatus::SENGKETA->value,
        ],
        OrderStatus::DITERIMA->value => [
            OrderStatus::SELESAI->value,
            OrderStatus::SENGKETA->value,
        ],
        OrderStatus::SENGKETA->value => [
            OrderStatus::SELESAI->value,
            OrderStatus::DIBATALKAN->value,
        ],
        OrderStatus::SELESAI->value => [],
        OrderStatus::DIBATALKAN->value => [],
        OrderStatus::DITOLAK->value => [],
    ];

    /**
     * Periksa apakah transisi dari status asal ke tujuan diizinkan.
     */
    public static function canTransition(OrderStatus|string $current, OrderStatus|string $target): bool
    {
        $currentValue = $current instanceof OrderStatus ? $current->value : $current;
        $targetValue = $target instanceof OrderStatus ? $target->value : $target;

        $allowed = self::TRANSITIONS[$currentValue] ?? [];

        return in_array($targetValue, $allowed, true);
    }

    /**
     * Daftar status yang diizinkan untuk transisi dari status tertentu.
     *
     * @return list<string>
     */
    public static function allowedTransitions(OrderStatus|string $current): array
    {
        $currentValue = $current instanceof OrderStatus ? $current->value : $current;

        return self::TRANSITIONS[$currentValue] ?? [];
    }

    /**
     * Eksekusi transisi status Purchase Order dan catat riwayatnya.
     *
     * @throws DomainException
     */
    public static function transition(
        PurchaseOrder $po,
        OrderStatus|string $target,
        ?User $actor = null,
        ?string $reason = null
    ): PurchaseOrder {
        $targetEnum = $target instanceof OrderStatus ? $target : OrderStatus::from($target);
        $currentEnum = $po->status;

        if (! self::canTransition($currentEnum, $targetEnum)) {
            throw new DomainException(
                "Transisi status Purchase Order dari {$currentEnum->label()} ({$currentEnum->value}) ke {$targetEnum->label()} ({$targetEnum->value}) tidak diizinkan."
            );
        }

        return DB::transaction(function () use ($po, $currentEnum, $targetEnum, $actor, $reason) {
            $po->status = $targetEnum;
            $po->save();

            OrderStatusHistory::create([
                'dari' => $currentEnum->value,
                'ke' => $targetEnum->value,
                'alasan' => $reason,
                'waktu' => now(),
                'purchase_orders_id_purchase_order' => $po->id_purchase_order,
                'users_id_user' => $actor?->id_user,
            ]);

            return $po;
        });
    }
}
