<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PoPolicy
{
    /**
     * Memeriksa apakah pengguna berhak melihat daftar Purchase Order.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['BUYER', 'SELLER', 'ADMIN'], true)
            && ($user->role === 'ADMIN' || $user->id_company !== null);
    }

    /**
     * Memeriksa apakah pengguna berhak melihat detail Purchase Order tertentu.
     */
    public function view(User $user, PurchaseOrder $po): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        if (! $user->id_company) {
            return false;
        }

        return (int) $user->id_company === (int) $po->companies_id_buyer
            || (int) $user->id_company === (int) $po->companies_id_seller;
    }

    /**
     * Memeriksa apakah pengguna berhak membuat Purchase Order baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'BUYER'
            && $user->id_company !== null
            && $user->status === 'AKTIF';
    }

    /**
     * Memeriksa apakah pengguna adalah Buyer pemilik PO.
     */
    public function send(User $user, PurchaseOrder $po): bool
    {
        return (int) $user->id_company === (int) $po->companies_id_buyer
            && $user->role === 'BUYER';
    }

    /**
     * Memeriksa apakah pengguna adalah Seller penerima PO.
     */
    public function confirm(User $user, PurchaseOrder $po): bool
    {
        return (int) $user->id_company === (int) $po->companies_id_seller
            && $user->role === 'SELLER';
    }

    /**
     * Memeriksa apakah pengguna adalah Seller penerima PO.
     */
    public function reject(User $user, PurchaseOrder $po): bool
    {
        return (int) $user->id_company === (int) $po->companies_id_seller
            && $user->role === 'SELLER';
    }

    /**
     * Memeriksa apakah pengguna adalah Buyer pembuat PO (atau Admin).
     */
    public function cancel(User $user, PurchaseOrder $po): bool
    {
        return (int) $user->id_company === (int) $po->companies_id_buyer
            || $user->role === 'ADMIN';
    }
}
