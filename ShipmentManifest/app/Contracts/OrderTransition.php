<?php

namespace App\Contracts;

use App\Models\PurchaseOrder;
use App\Models\User;

/**
 * TITIK INTEGRASI dengan PBI-05 (OrderStateMachine).
 * PBI-11 tidak mengubah status order sendiri; ia meminta transisi lewat kontrak ini.
 */
interface OrderTransition
{
    /** Order DIBAYAR -> DALAM_PENGIRIMAN saat manifes diterbitkan. */
    public function markInTransit(PurchaseOrder $order, ?User $actor, string $reason): void;
}
