<?php

namespace App\Policies;

use App\Enums\ShipmentStatus;
use App\Models\ShipmentManifest;
use App\Models\User;

/**
 * Hak akses PBI-11 (SYM-15): Seller mengelola pengiriman perusahaannya; Buyer melihat pengiriman yang sudah
 * terbit untuk perusahaannya; Admin dan Inspector hanya melihat.
 */
class ShipmentManifestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->activeRole(), ['ADMIN', 'SELLER', 'BUYER', 'INSPECTOR'], true);
    }

    public function view(User $user, ShipmentManifest $shipment): bool
    {
        $order = $shipment->purchaseOrder;

        return match ($user->activeRole()) {
            'ADMIN', 'INSPECTOR' => true,
            'SELLER' => (int) $user->id_company === (int) $order->companies_id_seller,
            'BUYER' => (int) $user->id_company === (int) $order->companies_id_buyer && ! $shipment->isDraft(),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->activeRole() === 'SELLER' && $user->id_company !== null;
    }

    public function update(User $user, ShipmentManifest $shipment): bool
    {
        return $this->ownsAsSeller($user, $shipment) && $shipment->isDraft();
    }

    public function issue(User $user, ShipmentManifest $shipment): bool
    {
        return $this->update($user, $shipment);
    }

    public function cancel(User $user, ShipmentManifest $shipment): bool
    {
        return $this->update($user, $shipment);
    }

    public function deleteDocument(User $user, ShipmentManifest $shipment): bool
    {
        return $this->update($user, $shipment);
    }

    public function arrive(User $user, ShipmentManifest $shipment): bool
    {
        return $this->ownsAsSeller($user, $shipment) && $shipment->status === ShipmentStatus::DALAM_PERJALANAN;
    }

    public function downloadManifest(User $user, ShipmentManifest $shipment): bool
    {
        return $shipment->no_manifes !== null && $this->view($user, $shipment);
    }

    private function ownsAsSeller(User $user, ShipmentManifest $shipment): bool
    {
        return $user->activeRole() === 'SELLER'
            && (int) $user->id_company === (int) $shipment->purchaseOrder->companies_id_seller;
    }
}
