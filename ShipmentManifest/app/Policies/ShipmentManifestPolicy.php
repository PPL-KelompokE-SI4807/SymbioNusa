<?php

namespace App\Policies;

use App\Enums\ShipmentStatus;
use App\Models\ShipmentManifest;
use App\Models\User;

/** Hak akses PBI-11: Seller CRUD; Buyer, Inspector, Admin hanya melihat (proposal 3.1.2). */
class ShipmentManifestPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SELLER, User::ROLE_BUYER, User::ROLE_INSPECTOR], true);
    }

    public function view(User $user, ShipmentManifest $shipment): bool
    {
        $order = $shipment->order;

        return match ($user->role) {
            User::ROLE_ADMIN, User::ROLE_INSPECTOR => true,
            User::ROLE_SELLER => (int) $user->company_id === (int) $order->seller_company_id,
            User::ROLE_BUYER => (int) $user->company_id === (int) $order->buyer_company_id && ! $shipment->isDraft(),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === User::ROLE_SELLER && $user->company_id !== null;
    }

    public function update(User $user, ShipmentManifest $shipment): bool
    {
        return $this->ownsAsSeller($user, $shipment) && $shipment->isDraft();
    }

    public function start(User $user, ShipmentManifest $shipment): bool
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
        return $this->ownsAsSeller($user, $shipment) && $shipment->status === ShipmentStatus::DalamPengiriman;
    }

    public function downloadManifest(User $user, ShipmentManifest $shipment): bool
    {
        return $this->view($user, $shipment) && $shipment->no_manifes !== null;
    }

    private function ownsAsSeller(User $user, ShipmentManifest $shipment): bool
    {
        return $user->role === User::ROLE_SELLER
            && (int) $user->company_id === (int) $shipment->order->seller_company_id;
    }
}
