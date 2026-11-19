<?php

namespace App\Policies;

use App\Enums\DemandRequestStatus;
use App\Models\DemandRequest;
use App\Models\User;

class DemandPolicy
{
    /**
     * Memeriksa apakah pengguna berhak mengakses fitur kebutuhan.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['BUYER', 'SELLER', 'ADMIN'], true) && $user->id_company !== null;
    }

    /**
     * Memeriksa apakah pengguna berhak melihat detail kebutuhan tertentu.
     * Kebutuhan berstatus DRAF hanya dapat dilihat oleh Buyer pemiliknya.
     * Kebutuhan terbuka dapat dilihat oleh Buyer pemilik dan seluruh Seller aktif.
     */
    public function view(User $user, DemandRequest $demand): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        if (! $user->id_company) {
            return false;
        }

        // Pemilik kebutuhan selalu dapat melihat
        if ($demand->companies_id_company === $user->id_company) {
            return true;
        }

        // Bila status DRAF, non-pemilik tidak boleh melihat
        $statusVal = $demand->status instanceof DemandRequestStatus ? $demand->status->value : $demand->status;
        if ($statusVal === DemandRequestStatus::DRAF->value) {
            return false;
        }

        // Seller aktif dapat melihat kebutuhan publik
        return $user->role === 'SELLER' && $user->status === 'AKTIF';
    }

    /**
     * Hanya Buyer aktif dan memiliki perusahaan yang berhak memublikasikan kebutuhan.
     */
    public function create(User $user): bool
    {
        return $user->role === 'BUYER' && $user->status === 'AKTIF' && $user->id_company !== null;
    }

    /**
     * Hanya Buyer pemilik yang berhak menyunting kebutuhan,
     * dan hanya jika statusnya masih DRAF atau TERBUKA.
     */
    public function update(User $user, DemandRequest $demand): bool
    {
        if ($user->role !== 'BUYER' || $user->status !== 'AKTIF' || ! $user->id_company) {
            return false;
        }

        if ($demand->companies_id_company !== $user->id_company) {
            return false;
        }

        $statusVal = $demand->status instanceof DemandRequestStatus ? $demand->status->value : $demand->status;

        return in_array($statusVal, [DemandRequestStatus::DRAF->value, DemandRequestStatus::TERBUKA->value], true);
    }

    /**
     * Hanya Buyer pemilik yang berhak menutup kebutuhan (menandai KEDALUWARSA/TERPENUHI),
     * dan hanya jika kebutuhan berstatus TERBUKA.
     */
    public function close(User $user, DemandRequest $demand): bool
    {
        if ($user->role !== 'BUYER' || $user->status !== 'AKTIF' || ! $user->id_company) {
            return false;
        }

        if ($demand->companies_id_company !== $user->id_company) {
            return false;
        }

        $statusVal = $demand->status instanceof DemandRequestStatus ? $demand->status->value : $demand->status;

        return $statusVal === DemandRequestStatus::TERBUKA->value;
    }

    /**
     * Hanya Buyer pemilik yang berhak menghapus draf kebutuhan.
     */
    public function delete(User $user, DemandRequest $demand): bool
    {
        if ($user->role !== 'BUYER' || $user->status !== 'AKTIF' || ! $user->id_company) {
            return false;
        }

        if ($demand->companies_id_company !== $user->id_company) {
            return false;
        }

        $statusVal = $demand->status instanceof DemandRequestStatus ? $demand->status->value : $demand->status;

        return $statusVal === DemandRequestStatus::DRAF->value;
    }
}
