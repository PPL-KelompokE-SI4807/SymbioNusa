<?php

namespace App\Policies;

use App\Models\InspectorProfile;
use App\Models\User;

/**
 * Otorisasi Registri Inspector (PBI-01 / SYM-5).
 * Admin mengelola registri; Inspector hanya melihat profilnya sendiri dan mengunggah sertifikat baru.
 */
class InspectorProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    public function view(User $user, InspectorProfile $profile): bool
    {
        return $user->role === 'ADMIN' || $this->owns($user, $profile);
    }

    public function create(User $user): bool
    {
        return $user->role === 'ADMIN';
    }

    public function update(User $user, InspectorProfile $profile): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Perpanjang masa berlaku sertifikat (sekaligus mengaktifkan kembali).
     */
    public function renew(User $user, InspectorProfile $profile): bool
    {
        return $user->role === 'ADMIN';
    }

    /**
     * Keluarkan dari registri: status_aktif = 0, profil tetap tersimpan.
     */
    public function deactivate(User $user, InspectorProfile $profile): bool
    {
        return $user->role === 'ADMIN' && (bool) $profile->status_aktif;
    }

    public function downloadCertificate(User $user, InspectorProfile $profile): bool
    {
        return $this->view($user, $profile);
    }

    /**
     * Inspector mengunggah berkas sertifikat perpanjangan untuk profilnya sendiri.
     */
    public function uploadOwnCertificate(User $user, InspectorProfile $profile): bool
    {
        return $this->owns($user, $profile);
    }

    /**
     * Profil tidak pernah dihapus permanen; gunakan deactivate().
     */
    public function delete(User $user, InspectorProfile $profile): bool
    {
        return false;
    }

    private function owns(User $user, InspectorProfile $profile): bool
    {
        return $user->role === 'INSPECTOR' && (int) $profile->id_user === (int) $user->id_user;
    }
}
