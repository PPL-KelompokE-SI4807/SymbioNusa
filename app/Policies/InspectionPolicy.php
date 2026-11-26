<?php

namespace App\Policies;

use App\Models\Inspection;
use App\Models\User;

class InspectionPolicy
{
    /**
     * Memeriksa apakah pengguna berhak melihat daftar penugasan inspeksi.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['ADMIN', 'INSPECTOR'], true);
    }

    /**
     * Memeriksa apakah pengguna berhak melihat detail penugasan inspeksi tertentu.
     */
    public function view(User $user, Inspection $inspection): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        if ($user->role === 'INSPECTOR') {
            return $inspection->users_id_user === $user->id_user;
        }

        return false;
    }

    /**
     * Memeriksa apakah pengguna berhak menugaskan Inspector ke Purchase Order.
     */
    public function assign(User $user): bool
    {
        return $user->role === 'ADMIN' && $user->status === 'AKTIF';
    }

    /**
     * Memeriksa apakah pengguna berhak membatalkan penugasan inspeksi.
     */
    public function cancel(User $user, Inspection $inspection): bool
    {
        if ($user->role !== 'ADMIN' || $user->status !== 'AKTIF') {
            return false;
        }

        return ! in_array($inspection->status, [Inspection::STATUS_SELESAI, Inspection::STATUS_DIBATALKAN], true);
    }

    /**
     * Memeriksa apakah pengguna berhak menugaskan ulang ke Inspector lain.
     */
    public function reassign(User $user, Inspection $inspection): bool
    {
        if ($user->role !== 'ADMIN' || $user->status !== 'AKTIF') {
            return false;
        }

        return ! in_array($inspection->status, [Inspection::STATUS_SELESAI, Inspection::STATUS_DIBATALKAN], true);
    }
}
