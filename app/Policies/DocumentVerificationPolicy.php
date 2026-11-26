<?php

namespace App\Policies;

use App\Models\DocumentVerification;
use App\Models\User;

class DocumentVerificationPolicy
{
    /**
     * Memeriksa apakah pengguna berhak melihat daftar verifikasi dokumen.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['INSPECTOR', 'ADMIN'], true);
    }

    /**
     * Memeriksa apakah pengguna berhak melihat detail verifikasi dokumen.
     */
    public function view(User $user, DocumentVerification $verification): bool
    {
        if ($user->role === 'ADMIN') {
            return true;
        }

        if ($user->role === 'INSPECTOR') {
            return $verification->users_id_user === $user->id_user;
        }

        return false;
    }

    /**
     * Memeriksa apakah Inspector berhak memutuskan (menyetujui atau menolak) verifikasi dokumen.
     * Sesuai aturan: hanya Inspector yang ditugaskan yang dapat memutuskan.
     */
    public function verify(User $user, DocumentVerification $verification): bool
    {
        if ($user->role !== 'INSPECTOR' || $user->status !== 'AKTIF') {
            return false;
        }

        if ($verification->users_id_user !== $user->id_user) {
            return false;
        }

        return $verification->status === DocumentVerification::STATUS_MENUNGGU;
    }
}
